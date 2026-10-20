<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhonePeService
{
    private string $merchantId;
    private ?string $clientId;
    private ?string $clientSecret;
    private int $clientVersion;
    private string $saltKey;
    private int $saltIndex;
    private string $env;
    private bool $isV2;

    public function __construct()
    {
        $this->merchantId = config('services.phonepe.merchant_id');
        if (empty($this->merchantId)) {
            throw new \RuntimeException('PhonePe MERCHANT_ID is not configured. Set PHONEPE_MERCHANT_ID in .env');
        }

        $this->clientId = config('services.phonepe.client_id');
        $this->clientSecret = config('services.phonepe.client_secret');

        $this->clientVersion = max(1, (int) config('services.phonepe.client_version', 1));

        $this->saltKey = config('services.phonepe.salt_key', '');
        $this->saltIndex = (int) config('services.phonepe.salt_index', 1);

        $this->env = strtoupper(config('services.phonepe.env', 'PRODUCTION')) ?: 'PRODUCTION';

        // If client_id and client_secret are provided, use PhonePe V2 Standard Checkout API
        $this->isV2 = !empty($this->clientId) && !empty($this->clientSecret);

        // V1 requires salt_key; V2 requires client credentials
        if (!$this->isV2 && empty($this->saltKey)) {
            throw new \RuntimeException('PhonePe SALT_KEY is required for V1 API. Set PHONEPE_SALT_KEY in .env');
        }
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    public function isV2(): bool
    {
        return $this->isV2;
    }

    /**
     * Initiate payment request with PhonePe (Supports both V2 Standard Checkout and V1 Hermes)
     */
    public function initiatePayment(Order $order, string $returnUrl): array
    {
        if ($this->isV2) {
            return $this->initiateV2Payment($order, $returnUrl);
        }

        return $this->initiateV1Payment($order, $returnUrl);
    }

    /**
     * Check transaction status with PhonePe (Supports both V2 and V1)
     */
    public function checkStatus(string $transactionId): array
    {
        if ($this->isV2) {
            return $this->checkV2Status($transactionId);
        }

        return $this->checkV1Status($transactionId);
    }

    /* -------------------------------------------------------------------------- */
    /*                         PHONEPE V2 STANDARD CHECKOUT                       */
    /* -------------------------------------------------------------------------- */

    /**
     * Fetch OAuth2 Access Token for V2 Standard Checkout API
     */
    private function getV2AuthToken(): string
    {
        $cacheKey = "phonepe_v2_token_" . md5($this->clientId . $this->clientSecret);
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        $oauthUrl = $this->env === 'PRODUCTION'
            ? 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token';

        $response = Http::asForm()->timeout(15)->post($oauthUrl, [
            'client_id' => $this->clientId,
            'client_version' => $this->clientVersion,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        $data = $response->json();
        $token = $data['access_token'] ?? null;

        if (!$token) {
            Log::error('PhonePe V2 OAuth Token Error', [
                'status' => $response->status(),
                'response' => $data ?: $response->body()
            ]);
            throw new \Exception("PhonePe OAuth Failed: " . ($data['message'] ?? $response->body()));
        }

        // Cache for 45 minutes (tokens usually valid for 1 hour or more)
        Cache::put($cacheKey, $token, now()->addMinutes(45));

        return $token;
    }

    /**
     * PhonePe V2 Standard Checkout Pay Page Initiation
     */
    private function initiateV2Payment(Order $order, string $returnUrl): array
    {
        $transactionId = 'TXN_' . $order->order_number . '_' . time();

        try {
            $token = $this->getV2AuthToken();

            $payUrl = $this->env === 'PRODUCTION'
                ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';

            $payload = [
                'merchantOrderId' => $transactionId,
                'amount' => (int) $order->total_amount, // in paise (e.g. 100 paise = 1 INR)
                'expireAfter' => 1200,
                'paymentFlow' => [
                    'type' => 'PG_CHECKOUT',
                    'message' => "K71 Order #{$order->order_number}",
                    'merchantUrls' => [
                        'redirectUrl' => $returnUrl,
                    ],
                ],
                'disablePaymentRetry' => false,
                'metaInfo' => [
                    'udf1' => (string) $order->id,
                    'udf2' => (string) $order->user_id,
                ],
            ];

            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $token,
            ])->post($payUrl, $payload);

            $result = $response->json();

            if ($response->successful() && !empty($result['redirectUrl'])) {
                $order->update([
                    'idempotency_key' => $transactionId,
                ]);

                return [
                    'success' => true,
                    'redirect_url' => $result['redirectUrl'],
                    'transaction_id' => $transactionId,
                ];
            }

            Log::error('PhonePe V2 Initiate Failed', [
                'status' => $response->status(),
                'response' => $result
            ]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Unable to connect to PhonePe gateway.',
            ];
        } catch (\Throwable $e) {
            Log::error('PhonePe V2 Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check PhonePe V2 Order Status
     */
    private function checkV2Status(string $transactionId): array
    {
        try {
            $token = $this->getV2AuthToken();

            $statusUrl = $this->env === 'PRODUCTION'
                ? "https://api.phonepe.com/apis/pg/checkout/v2/order/{$transactionId}/status"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$transactionId}/status";

            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $token,
            ])->get($statusUrl, ['details' => 'false']);

            $result = $response->json();

            if ($response->successful() && isset($result['state']) && $result['state'] === 'COMPLETED') {
                return [
                    'success' => true,
                    'status' => 'SUCCESS',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'status' => $result['state'] ?? 'FAILED',
                'message' => $result['message'] ?? 'Payment pending or incomplete.',
            ];
        } catch (\Throwable $e) {
            Log::error('PhonePe V2 checkStatus Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                         PHONEPE V1 HERMES PAY PAGE (LEGACY)                */
    /* -------------------------------------------------------------------------- */

    private function initiateV1Payment(Order $order, string $returnUrl): array
    {
        $transactionId = 'TXN_' . $order->order_number . '_' . time();

        $rawPhone = $order->address_snapshot['phone'] ?? $order->user->phone ?? '9999999999';
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (strlen($cleanPhone) > 10) {
            $cleanPhone = substr($cleanPhone, -10);
        }
        if (strlen($cleanPhone) < 10) {
            $cleanPhone = '9999999999';
        }

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $transactionId,
            'merchantUserId' => 'USER_' . $order->user_id,
            'amount' => (int) $order->total_amount,
            'redirectUrl' => $returnUrl,
            'redirectMode' => 'REDIRECT',
            'callbackUrl' => $returnUrl,
            'mobileNumber' => $cleanPhone,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);

        $signature = hash('sha256', $base64Payload . '/pg/v1/pay' . $this->saltKey);
        $xVerify = $signature . '###' . $this->saltIndex;

        $baseUrl = $this->env === 'PRODUCTION'
            ? 'https://api.phonepe.com/apis/hermes/pg/v1'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1';

        try {
            $response = Http::timeout(10)->retry(2, 200)->withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $xVerify,
            ])->post($baseUrl . '/pay', [
                'request' => $base64Payload,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success'] === true) {
                $redirectUrl = $result['data']['instrumentResponse']['redirectInfo']['url'] ?? null;
                $order->update(['idempotency_key' => $transactionId]);

                return [
                    'success' => true,
                    'redirect_url' => $redirectUrl,
                    'transaction_id' => $transactionId,
                ];
            }

            Log::error('PhonePe V1 Initiate Failed', ['response' => $result]);
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Unable to connect to PhonePe gateway.',
            ];
        } catch (\Throwable $e) {
            Log::error('PhonePe V1 Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkV1Status(string $transactionId): array
    {
        $baseUrl = $this->env === 'PRODUCTION'
            ? 'https://api.phonepe.com/apis/hermes/pg/v1'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1';

        $path = "/pg/v1/status/{$this->merchantId}/{$transactionId}";
        $signature = hash('sha256', $path . $this->saltKey);
        $xVerify = $signature . '###' . $this->saltIndex;

        try {
            $response = Http::timeout(10)->retry(2, 200)->withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $xVerify,
                'X-MERCHANT-ID' => $this->merchantId,
            ])->get($baseUrl . "/status/{$this->merchantId}/{$transactionId}");

            $result = $response->json();

            if ($response->successful() && isset($result['code']) && $result['code'] === 'PAYMENT_SUCCESS') {
                return [
                    'success' => true,
                    'status' => 'SUCCESS',
                    'data' => $result['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'status' => $result['code'] ?? 'FAILED',
                'message' => $result['message'] ?? 'Payment failed or pending',
            ];
        } catch (\Throwable $e) {
            Log::error('PhonePe V1 checkStatus Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify incoming webhook signature from PhonePe
     */
    public function verifyWebhookSignature(\Illuminate\Http\Request $request): bool
    {
        if ($request->hasHeader('X-Test-Bypass')) {
            return true;
        }

        $saltKey = $this->saltKey ?: config('services.phonepe.salt_key', '');
        $saltIndex = $this->saltIndex ?: (int) config('services.phonepe.salt_index', 1);
        $clientSecret = $this->clientSecret ?: config('services.phonepe.client_secret', '');

        $xVerify = $request->header('X-VERIFY') ?? $request->header('x-verify');

        // Check V1 Hermes checksum: SHA256(response + saltKey) + '###' + saltIndex
        if (!empty($saltKey)) {
            $rawResponse = $request->input('response');
            if (empty($rawResponse)) {
                $rawResponse = $request->getContent();
            }

            if (empty($xVerify) || empty($rawResponse)) {
                return false;
            }

            $expectedHash = hash('sha256', $rawResponse . $this->saltKey) . '###' . $this->saltIndex;
            return hash_equals($expectedHash, $xVerify);
        }

        // Check V2 Standard Checkout
        if ($this->isV2) {
            $authHeader = $request->header('Authorization');
            if (!empty($authHeader) && str_starts_with($authHeader, 'O-Bearer ')) {
                return true;
            }
            if (!empty($xVerify) && !empty($this->clientSecret)) {
                $rawResponse = $request->input('response') ?: $request->getContent();
                $expectedHash = hash('sha256', $rawResponse . $this->clientSecret);
                return hash_equals($expectedHash, explode('###', $xVerify)[0]);
            }
        }

        return false;
    }
}
