<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #FAF7F2; color: #1F2A26; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; text-align: center; }
        .otp { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #8FA396; margin: 20px 0; }
        .footer { font-size: 12px; color: #6B7570; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verification Code</h2>
        <p>You requested a code for {{ str_replace('_', ' ', $purpose) }}. Here is your One-Time Password:</p>
        <div class="otp">{{ $code }}</div>
        <p>This code will expire in {{ config('marketplace.otp.expiry_minutes') }} minutes. Do not share this with anyone.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('marketplace.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
