<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Customer messages inbox.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
        }
        return view('messages.index', compact('user'));
    }

    /**
     * Customer view conversation thread.
     */
    public function show(Conversation $conversation): View
    {
        if ($conversation->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        // Mark vendor messages as read
        $conversation->messages()
            ->where('sender_type', 'vendor')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->load(['vendor', 'product', 'messages']);

        return view('messages.show', compact('conversation'));
    }

    /**
     * Customer initiate a new conversation or send initial message.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'product_id' => 'nullable|exists:products,id',
            'subject' => 'nullable|string|max:200',
            'body' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        // Check if conversation already exists for user and vendor (and optionally product)
        $conversationQuery = Conversation::where('user_id', $user->id)
            ->where('vendor_id', $validated['vendor_id']);

        if (!empty($validated['product_id'])) {
            $conversationQuery->where('product_id', $validated['product_id']);
        }

        $conversation = $conversationQuery->first();

        if (!$conversation) {
            $subject = $validated['subject'] ?? null;
            if (!$subject && !empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                $subject = 'Inquiry about: ' . ($product ? $product->title : 'Product');
            }

            $conversation = Conversation::create([
                'vendor_id' => $validated['vendor_id'],
                'user_id' => $user->id,
                'product_id' => $validated['product_id'] ?? null,
                'subject' => $subject ?: 'Direct Inquiry',
                'last_message_at' => now(),
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('messages.show', $conversation->id)
            ->with('success', 'Message sent successfully.');
    }

    /**
     * Customer reply to conversation.
     */
    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        if ($conversation->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Vendor inbox.
     */
    public function vendorIndex(Request $request): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        $conversations = $vendor->conversations()
            ->with(['user', 'latestMessage', 'product'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('vendor.messages.index', compact('conversations', 'vendor'));
    }

    /**
     * Vendor view conversation thread.
     */
    public function vendorShow(Request $request, Conversation $conversation): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor || $conversation->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        // Mark user messages as read
        $conversation->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->load(['user', 'product', 'messages']);

        return view('vendor.messages.show', compact('conversation', 'vendor'));
    }

    /**
     * Vendor reply to conversation.
     */
    public function vendorReply(Request $request, Conversation $conversation): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor || $conversation->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'vendor',
            'sender_id' => $vendor->id,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return back()->with('success', 'Reply sent successfully.');
    }
}
