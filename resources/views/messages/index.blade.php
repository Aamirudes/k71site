<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Direct Messages • Chats — K71</title>
    <meta name="description" content="Real-time direct messaging and private message requests on K71. Connect with any user, buyer, or seller instantly.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js (Defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* ======================================================================
           META / APPLE LIQUID GLASS DIRECT MESSAGING DESIGN SYSTEM
           Scoped styles to guarantee 100% responsiveness without element merging
           ====================================================================== */
        :root {
            --glass-bg: rgba(255, 255, 255, 0.76);
            --glass-surface: rgba(255, 255, 255, 0.65);
            --glass-card: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.6);
            --glass-specular: rgba(255, 255, 255, 0.9);
            --glass-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.09);
            --glass-glow: rgba(6, 0, 194, 0.15);
            --color-text-main: #0F172A;
            --color-text-muted: #64748B;
            --color-bubble-received: rgba(241, 245, 249, 0.85);
            --color-bubble-received-text: #0F172A;
            --color-bubble-sent-grad: linear-gradient(135deg, #0600c2 0%, #4338ca 60%, #6366f1 100%);
            --color-active-item: rgba(6, 0, 194, 0.06);
        }

        .dark {
            --glass-bg: rgba(15, 23, 42, 0.78);
            --glass-surface: rgba(30, 41, 59, 0.65);
            --glass-card: rgba(15, 23, 42, 0.88);
            --glass-border: rgba(255, 255, 255, 0.12);
            --glass-specular: rgba(255, 255, 255, 0.18);
            --glass-shadow: 0 24px 60px -12px rgba(0, 0, 0, 0.55);
            --glass-glow: rgba(99, 102, 241, 0.25);
            --color-text-main: #F8FAFC;
            --color-text-muted: #94A3B8;
            --color-bubble-received: rgba(30, 41, 59, 0.85);
            --color-bubble-received-text: #F8FAFC;
            --color-bubble-sent-grad: linear-gradient(135deg, #0600c2 0%, #4f46e5 60%, #7c3aed 100%);
            --color-active-item: rgba(99, 102, 241, 0.14);
        }

        #k71-chat-wrapper {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: calc(100vh - 110px);
            min-height: 580px;
            max-height: 940px;
            display: flex;
            align-items: stretch;
            justify-content: center;
            padding: 12px 18px;
            box-sizing: border-box;
            background-color: transparent;
            overflow: hidden;
        }

        /* Master Glass Card Container */
        .glass-chat-frame {
            width: 100%;
            max-width: 1200px;
            height: 100%;
            min-height: 0;
            max-height: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(36px) saturate(200%);
            -webkit-backdrop-filter: blur(36px) saturate(200%);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            box-shadow: var(--glass-shadow), inset 0 1px 2px var(--glass-specular);
            display: flex;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
        }

        /* ---------------- LEFT SIDEBAR ---------------- */
        .glass-sidebar {
            width: 360px;
            min-width: 310px;
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            background: var(--glass-surface);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 10;
        }

        .glass-sidebar-header {
            height: 68px;
            padding: 0 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .glass-user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            min-width: 0;
        }

        .glass-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--glass-border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
            transition: opacity 0.2s ease;
        }

        .glass-user-avatar-wrap {
            position: relative;
            cursor: pointer;
            flex-shrink: 0;
            display: inline-flex;
        }
        .glass-user-avatar-wrap:hover .glass-avatar-camera-badge {
            transform: scale(1.15);
            background: #4f46e5;
        }
        .glass-avatar-camera-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: #0600c2;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--glass-card);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .glass-user-name {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--color-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.01em;
        }

        .glass-icon-btn {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--color-text-main);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
        }

        .dark .glass-icon-btn {
            background: rgba(30, 41, 59, 0.5);
        }

        .glass-icon-btn:hover {
            background: rgba(255, 255, 255, 0.85);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px var(--glass-glow);
        }

        .dark .glass-icon-btn:hover {
            background: rgba(51, 65, 85, 0.85);
        }

        /* Instagram Dual Inbox Segmented Tabs */
        .glass-tabs-bar {
            display: flex;
            padding: 8px 16px;
            gap: 6px;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.25);
        }
        .dark .glass-tabs-bar {
            background: rgba(15, 23, 42, 0.35);
        }

        .glass-tab-btn {
            flex: 1;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--color-text-muted);
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .glass-tab-btn.active {
            background: var(--glass-card);
            color: var(--color-text-main);
            border-color: var(--glass-border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05), inset 0 1px 1px var(--glass-specular);
        }

        .glass-tab-badge {
            background: #EF4444;
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 9999px;
            letter-spacing: 0;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
            animation: pulse-badge 2s infinite ease-in-out;
        }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* In-Chat Filter Search */
        .glass-search-wrap {
            padding: 10px 16px;
            border-bottom: 1px solid var(--glass-border);
        }

        .glass-search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .glass-search-box svg {
            position: absolute;
            left: 14px;
            width: 15px;
            height: 15px;
            color: var(--color-text-muted);
            pointer-events: none;
        }

        .glass-search-input {
            width: 100%;
            padding: 9px 14px 9px 38px;
            border-radius: 14px;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-main);
            outline: none;
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .dark .glass-search-input {
            background: rgba(30, 41, 59, 0.4);
        }

        .glass-search-input:focus {
            background: var(--glass-card);
            border-color: #0600c2;
            box-shadow: 0 0 0 3px var(--glass-glow);
        }

        /* Conversations Stream */
        .glass-conv-list {
            flex: 1;
            overflow-y: auto;
            list-style: none;
            margin: 0;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .glass-conv-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .glass-conv-item:hover {
            background: rgba(255, 255, 255, 0.4);
            border-color: var(--glass-border);
        }

        .dark .glass-conv-item:hover {
            background: rgba(30, 41, 59, 0.4);
        }

        .glass-conv-item.active {
            background: var(--color-active-item);
            border-color: var(--glass-border);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03), inset 0 1px 1px var(--glass-specular);
        }

        .glass-avatar-wrap {
            position: relative;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
        }

        .glass-conv-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--glass-border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .glass-unread-dot {
            position: absolute;
            bottom: 0px;
            right: 0px;
            width: 11px;
            height: 11px;
            background: #EF4444;
            border: 2px solid #FFFFFF;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.75);
            animation: instagramRedDotPing 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        .dark .glass-unread-dot {
            background: #EF4444;
            border-color: #0E172A;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.9);
        }

        @keyframes instagramRedDotPing {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            50% {
                transform: scale(1.15);
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
            }
        }

        .glass-conv-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .glass-conv-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .glass-conv-username {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--color-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .glass-conv-time {
            font-size: 11px;
            color: var(--color-text-muted);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .glass-conv-last-msg {
            font-size: 12px;
            color: var(--color-text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.35;
        }

        .glass-conv-last-msg.unread-bold {
            font-weight: 700;
            color: var(--color-text-main);
        }

        /* ---------------- RIGHT CHAT PANE ---------------- */
        .glass-chat-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            position: relative;
            background: var(--glass-card);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
        }

        /* Empty State */
        .glass-empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px;
            text-align: center;
        }

        .glass-empty-circle {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(6, 0, 194, 0.12), rgba(99, 102, 241, 0.08));
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 12px 28px rgba(6, 0, 194, 0.08), inset 0 1px 2px var(--glass-specular);
        }

        .glass-empty-circle svg {
            width: 40px;
            height: 40px;
            color: #0600c2;
            transform: translate(2px, -1px);
        }

        .dark .glass-empty-circle svg {
            color: #38BDF8;
        }

        .glass-empty-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--color-text-main);
            margin: 0 0 6px;
            letter-spacing: -0.02em;
        }

        .glass-empty-desc {
            font-size: 13.5px;
            color: var(--color-text-muted);
            max-width: 320px;
            margin: 0 0 20px;
            line-height: 1.45;
        }

        .glass-btn-primary {
            background: linear-gradient(135deg, #0600c2 0%, #4338ca 100%);
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            box-shadow: 0 8px 24px var(--glass-glow), inset 0 1px 1px rgba(255, 255, 255, 0.35);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .glass-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px var(--glass-glow);
        }

        .glass-btn-primary:active {
            transform: translateY(0);
        }

        /* Active Chat Header */
        .glass-chat-header {
            height: 68px;
            padding: 0 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--glass-surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            gap: 12px;
            flex-shrink: 0;
        }

        .glass-chat-header-user {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .glass-back-btn {
            display: none;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--glass-border);
            color: var(--color-text-main);
            width: 38px;
            height: 38px;
            cursor: pointer;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-right: 2px;
        }
        .dark .glass-back-btn {
            background: rgba(30, 41, 59, 0.65);
        }
        .glass-back-btn:hover {
            background: rgba(255, 255, 255, 0.95);
            transform: scale(1.08);
            box-shadow: 0 4px 14px var(--glass-glow);
        }
        .dark .glass-back-btn:hover {
            background: rgba(51, 65, 85, 0.95);
        }
        .glass-back-btn:active {
            transform: scale(0.95);
        }

        .glass-chat-header-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid var(--glass-border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            flex-shrink: 0;
        }

        .glass-chat-header-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .glass-chat-header-username {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--color-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .glass-chat-header-status {
            font-size: 11.5px;
            color: var(--color-text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .glass-online-indicator {
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            background: #10B981;
            display: inline-block;
            box-shadow: 0 0 6px #10B981;
        }

        /* ⚡ INSTAGRAM DIRECT REQUEST ACTION BANNER */
        .glass-request-banner {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(239, 68, 68, 0.08));
            border-bottom: 1px solid rgba(245, 158, 11, 0.25);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 12px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            animation: slide-down 0.25s ease;
            flex-shrink: 0;
        }

        @keyframes slide-down {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glass-request-banner-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .glass-request-badge-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.2);
            color: #D97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .glass-request-banner-text {
            flex: 1;
            min-width: 0;
        }

        .glass-request-banner-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--color-text-main);
            letter-spacing: -0.01em;
        }

        .glass-request-banner-sub {
            font-size: 11.5px;
            color: var(--color-text-muted);
            margin-top: 1px;
            line-height: 1.35;
        }

        .glass-request-banner-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .glass-btn-decline {
            padding: 6px 14px;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.4);
            color: #EF4444;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .dark .glass-btn-decline {
            background: rgba(30, 41, 59, 0.4);
        }
        .glass-btn-decline:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .glass-btn-accept {
            padding: 6px 18px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            transition: all 0.15s ease;
        }
        .glass-btn-accept:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }

        /* Message Feed */
        .glass-chat-feed {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .glass-date-divider {
            text-align: center;
            margin: 14px 0;
            position: relative;
        }

        .glass-date-divider span {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-text-muted);
            background: var(--glass-surface);
            padding: 3px 12px;
            border-radius: 9999px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .glass-msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            max-width: 72%;
        }

        .glass-msg-row.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .glass-msg-row.received {
            align-self: flex-start;
        }

        .glass-msg-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--glass-border);
            flex-shrink: 0;
            margin-bottom: 2px;
        }

        .glass-msg-bubble {
            padding: 10px 16px;
            border-radius: 20px;
            font-size: 13.5px;
            line-height: 1.45;
            word-break: break-word;
            position: relative;
            max-width: 100%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .glass-msg-row.sent .glass-msg-bubble {
            background: var(--color-bubble-sent-grad);
            color: #FFFFFF;
            border-bottom-right-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 6px 18px var(--glass-glow), inset 0 1px 1px rgba(255, 255, 255, 0.35);
        }

        .glass-msg-row.received .glass-msg-bubble {
            background: var(--color-bubble-received);
            color: var(--color-bubble-received-text);
            border-bottom-left-radius: 5px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03), inset 0 1px 1px var(--glass-specular);
        }

        .glass-msg-bubble.optimistic {
            opacity: 0.7;
        }

        .glass-msg-meta {
            font-size: 10px;
            color: var(--color-text-muted);
            margin-top: 3px;
            padding: 0 4px;
            display: none;
        }

        .glass-msg-row:hover .glass-msg-meta {
            display: block;
        }

        /* Quick Emojis Drawer */
        .glass-emoji-drawer {
            display: none;
            padding: 8px 16px;
            gap: 8px;
            background: var(--glass-surface);
            border-top: 1px solid var(--glass-border);
            overflow-x: auto;
        }

        .glass-emoji-drawer.open {
            display: flex;
        }

        .glass-emoji-chip {
            background: rgba(255, 255, 255, 0.45);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 4px 10px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.12s ease;
        }
        .dark .glass-emoji-chip {
            background: rgba(30, 41, 59, 0.45);
        }
        .glass-emoji-chip:hover {
            transform: scale(1.2);
        }

        /* Sticky Bottom Composer (Instagram Style Elevation & Responsiveness) */
        .glass-composer-bar {
            padding: 14px 20px max(18px, env(safe-area-inset-bottom, 16px));
            background: var(--glass-surface);
            border-top: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            flex-shrink: 0;
            position: relative;
            z-index: 40;
        }

        .glass-composer-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 6px 12px;
            min-height: 52px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04), inset 0 1px 1px var(--glass-specular);
            transition: all 0.2s ease;
            position: relative;
        }

        .glass-composer-pill:focus-within {
            border-color: #0095f6;
            box-shadow: 0 0 0 3px rgba(0, 149, 246, 0.15), inset 0 1px 1px var(--glass-specular);
        }

        .glass-composer-emoji-btn {
            background: none;
            border: none;
            padding: 0;
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
            border-radius: 50%;
            transition: all 0.15s ease;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            flex-shrink: 0;
        }
        .glass-composer-emoji-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }
        .glass-composer-emoji-btn:active {
            transform: scale(0.92);
        }

        .glass-composer-textarea {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            font-family: inherit;
            color: var(--color-text-main);
            background: transparent;
            max-height: 100px;
            resize: none;
            padding: 8px 6px;
            line-height: 1.4;
            min-height: 24px;
        }

        .glass-composer-textarea::placeholder {
            color: var(--color-text-muted);
        }

        .glass-composer-send-btn {
            background: #0095f6;
            color: #ffffff;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            padding: 8px 18px;
            min-height: 38px;
            border-radius: 20px;
            transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 149, 246, 0.3);
            white-space: nowrap;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            flex-shrink: 0;
        }

        .glass-composer-send-btn:hover {
            background: #0081d6;
            transform: scale(1.04);
        }

        .glass-composer-send-btn:active {
            transform: scale(0.94);
        }

        .glass-composer-send-btn.disabled {
            opacity: 0.45;
            background: transparent;
            color: #0095f6;
            box-shadow: none;
        }

        /* ---------------- SEARCH USER MODAL ---------------- */
        .glass-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .glass-modal-backdrop.open {
            display: flex;
        }

        .glass-modal-card {
            width: 100%;
            max-width: 440px;
            max-height: 540px;
            background: var(--glass-card);
            backdrop-filter: blur(36px) saturate(200%);
            -webkit-backdrop-filter: blur(36px) saturate(200%);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.2), inset 0 1px 2px var(--glass-specular);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: modal-pop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modal-pop {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .glass-modal-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .glass-modal-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--color-text-main);
            letter-spacing: -0.01em;
        }

        .glass-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--color-text-muted);
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }

        .glass-modal-search {
            padding: 12px 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .glass-modal-search-label {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--color-text-main);
        }

        .glass-modal-search-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--color-text-main);
            background: transparent;
        }

        .glass-modal-user-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
            list-style: none;
            margin: 0;
        }

        .glass-modal-user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .glass-modal-user-item:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        .dark .glass-modal-user-item:hover {
            background: rgba(30, 41, 59, 0.4);
        }

        .glass-modal-user-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .glass-modal-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--glass-border);
            flex-shrink: 0;
        }

        .glass-modal-user-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .glass-modal-username {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--color-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .glass-modal-fullname {
            font-size: 12px;
            color: var(--color-text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .glass-modal-msg-btn {
            background: #0600c2;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 4px 12px var(--glass-glow);
        }

        .dark .glass-modal-msg-btn {
            background: #38BDF8;
            color: #060A17;
        }

        .glass-modal-msg-btn:hover {
            transform: translateY(-1px);
        }

        /* ---------------- INSTAGRAM DIRECT UPGRADE STYLES ---------------- */
        .glass-online-badge {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #10B981;
            border: 2px solid #FFFFFF;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.7);
            z-index: 2;
        }
        .dark .glass-online-badge {
            border-color: #0F172A;
        }
        .glass-online-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #059669;
            font-weight: 700;
        }
        .dark .glass-online-pill {
            color: #34D399;
        }
        .glass-online-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10B981;
            display: inline-block;
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.8);
            animation: pulseDot 2s infinite ease-in-out;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.8; }
        }

        /* Instagram 3-Dot Bouncing Typing Bubble */
        .glass-typing-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 14px;
            padding-left: 2px;
            animation: fadeIn 0.2s ease;
        }
        .glass-typing-bubble {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 10px 16px;
            background: var(--color-bubble-received);
            border: 1px solid var(--glass-border);
            border-radius: 20px 20px 20px 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        .typing-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #8e8e93;
            display: inline-block;
            animation: igTypingBounce 1.2s infinite ease-in-out both;
        }
        .dark .typing-dot {
            background: #94a3b8;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        .typing-dot:nth-child(3) { animation-delay: 0s; }
        @keyframes igTypingBounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.35; }
            40% { transform: translateY(-6px); opacity: 1; background: #0095f6; }
        }

        /* Instagram Voice Message Elements (Exact Parity) */
        .glass-composer-voice-btn {
            background: transparent;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            width: 38px;
            height: 38px;
            min-width: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .glass-composer-voice-btn:hover {
            color: #0095f6;
            background: rgba(0, 149, 246, 0.08);
            transform: scale(1.08);
        }
        .glass-composer-voice-btn:active {
            transform: scale(0.92);
        }
        .glass-composer-voice-btn.recording {
            color: #ffffff !important;
            background: #ef4444 !important;
            animation: pulseRecord 1s infinite alternate;
            transform: scale(1.1);
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.5);
        }
        @keyframes pulseRecord {
            from { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
            to { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        }
        .glass-voice-recording-overlay {
            position: absolute;
            inset: 3px;
            background: var(--color-panel-bg);
            border-radius: 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 0 8px 0 12px;
            z-index: 25;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1.5px solid #ef4444;
        }
        .glass-voice-recording-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
            animation: pulseDot 1s infinite;
            flex-shrink: 0;
        }
        .glass-voice-timer {
            font-size: 13.5px;
            font-weight: 800;
            color: #ef4444;
            font-variant-numeric: tabular-nums;
            min-width: 32px;
        }
        .glass-voice-waveform {
            display: flex;
            align-items: center;
            gap: 2.5px;
            flex: 1;
            height: 22px;
            padding: 0 4px;
        }
        .glass-voice-waveform span {
            width: 2.5px;
            background: #ef4444;
            border-radius: 2px;
            animation: waveBounce 0.8s ease-in-out infinite alternate;
        }
        .glass-voice-waveform span:nth-child(1) { height: 6px; animation-delay: 0.1s; }
        .glass-voice-waveform span:nth-child(2) { height: 16px; animation-delay: 0.3s; }
        .glass-voice-waveform span:nth-child(3) { height: 22px; animation-delay: 0.2s; }
        .glass-voice-waveform span:nth-child(4) { height: 10px; animation-delay: 0.4s; }
        .glass-voice-waveform span:nth-child(5) { height: 18px; animation-delay: 0.15s; }
        .glass-voice-waveform span:nth-child(6) { height: 14px; animation-delay: 0.35s; }
        .glass-voice-waveform span:nth-child(7) { height: 20px; animation-delay: 0.25s; }
        .glass-voice-waveform span:nth-child(8) { height: 8px; animation-delay: 0.45s; }
        .glass-voice-waveform span:nth-child(9) { height: 16px; animation-delay: 0.2s; }
        .glass-voice-waveform span:nth-child(10) { height: 10px; animation-delay: 0.35s; }
        .glass-voice-cancel-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            flex-shrink: 0;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .glass-voice-cancel-btn:hover {
            background: #ef4444;
            color: #ffffff;
            transform: scale(1.08);
        }
        .glass-voice-cancel-btn:active {
            transform: scale(0.92);
        }
        .glass-voice-confirm-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: #0095f6;
            color: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 149, 246, 0.4);
            transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .glass-voice-confirm-btn:hover {
            background: #0081d6;
            transform: scale(1.08);
        }
        .glass-voice-confirm-btn:active {
            transform: scale(0.92);
        }

        /* Voice Player Message Bubble */
        .glass-voice-player {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 22px;
            min-width: 220px;
            max-width: 320px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            user-select: none;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            transition: transform 0.12s ease, box-shadow 0.15s ease;
        }
        .glass-voice-player:active {
            transform: scale(0.985);
        }
        .glass-voice-player.sent {
            background: linear-gradient(135deg, #0095f6 0%, #0077e6 100%);
            color: #ffffff;
            border-bottom-right-radius: 6px;
        }
        .glass-voice-player.received {
            background: var(--color-bubble-received);
            color: var(--color-text-main);
            border: 1px solid var(--glass-border);
            border-bottom-left-radius: 6px;
        }
        .glass-voice-play-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .glass-voice-player.sent .glass-voice-play-btn {
            background: #ffffff;
            color: #0095f6;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }
        .glass-voice-player.received .glass-voice-play-btn {
            background: #0095f6;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 149, 246, 0.25);
        }
        .glass-voice-play-btn:hover {
            transform: scale(1.08);
        }
        .glass-voice-play-btn:active {
            transform: scale(0.92);
        }
        .glass-voice-wave-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }
        .glass-voice-bars {
            display: flex;
            align-items: center;
            gap: 2.5px;
            height: 20px;
        }
        .glass-voice-bars span {
            width: 2.5px;
            border-radius: 2px;
            transform-origin: bottom;
            transition: height 0.15s ease, opacity 0.2s ease;
        }
        .glass-voice-player.sent .glass-voice-bars span {
            background: rgba(255, 255, 255, 0.75);
        }
        .glass-voice-player.received .glass-voice-bars span {
            background: var(--color-text-muted);
        }
        @keyframes waveBounce {
            0% { transform: scaleY(0.35); opacity: 0.55; }
            50% { transform: scaleY(1.35); opacity: 1; }
            100% { transform: scaleY(0.35); opacity: 0.55; }
        }
        .glass-voice-player.is-playing .glass-voice-bars span {
            animation: waveBounce 0.9s ease-in-out infinite;
        }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(1) { animation-delay: 0.05s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(2) { animation-delay: 0.25s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(3) { animation-delay: 0.45s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(4) { animation-delay: 0.15s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(5) { animation-delay: 0.35s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(6) { animation-delay: 0.55s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(7) { animation-delay: 0.1s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(8) { animation-delay: 0.3s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(9) { animation-delay: 0.5s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(10) { animation-delay: 0.2s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(11) { animation-delay: 0.4s; }
        .glass-voice-player.is-playing .glass-voice-bars span:nth-child(12) { animation-delay: 0.6s; }
        .glass-voice-progress-track {
            width: 100%;
            height: 4px;
            border-radius: 2px;
            background: rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }
        .glass-voice-player.sent .glass-voice-progress-track {
            background: rgba(255, 255, 255, 0.3);
        }
        .glass-voice-progress-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.08s linear;
        }
        .glass-voice-player.sent .glass-voice-progress-fill {
            background: #ffffff;
        }
        .glass-voice-player.received .glass-voice-progress-fill {
            background: #0095f6;
        }
        .glass-voice-duration {
            font-size: 11.5px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .glass-voice-player.sent .glass-voice-duration {
            color: rgba(255, 255, 255, 0.9);
        }
        .glass-voice-player.received .glass-voice-duration {
            color: var(--color-text-muted);
        }

        /* Chat Photo / Image Bubble */
        .glass-msg-image-wrap {
            max-width: 280px;
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }
        .glass-msg-image-wrap:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }
        .glass-msg-image-wrap img {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            display: block;
        }
        .glass-msg-image-caption {
            padding: 8px 12px;
            font-size: 13.5px;
            line-height: 1.4;
        }

        /* Unsend Message Trigger */
        .glass-msg-row {
            position: relative;
        }
        .glass-msg-unsend-btn {
            opacity: 0;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #EF4444;
            transition: all 0.18s ease;
            font-size: 11px;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            z-index: 5;
        }
        .dark .glass-msg-unsend-btn {
            background: rgba(30, 41, 59, 0.9);
        }
        .glass-msg-row.sent .glass-msg-unsend-btn {
            left: -32px;
        }
        .glass-msg-row:hover .glass-msg-unsend-btn {
            opacity: 1;
        }
        .glass-msg-unsend-btn:hover {
            background: #EF4444;
            color: #FFFFFF;
            transform: translateY(-50%) scale(1.1);
        }

        /* Long-press Instagram Action Sheet Modal */
        .glass-action-sheet-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            animation: fadeIn 0.15s ease-out;
        }
        .glass-action-sheet {
            width: 100%;
            max-width: 440px;
            margin: 0 16px 24px 16px;
            background: var(--glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            animation: slideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .glass-action-sheet-handle {
            width: 36px;
            height: 4px;
            border-radius: 2px;
            background: rgba(148, 163, 184, 0.4);
            margin: 0 auto 8px auto;
        }
        .glass-action-sheet-header {
            font-size: 13px;
            font-weight: 700;
            color: var(--color-text-muted);
            text-align: center;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--glass-border);
        }
        .glass-action-sheet-actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .glass-action-sheet-btn {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: none;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
        }
        .glass-action-sheet-btn.danger {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
        }
        .glass-action-sheet-btn.danger:hover {
            background: #EF4444;
            color: #FFFFFF;
        }
        .glass-action-sheet-btn.cancel {
            background: rgba(148, 163, 184, 0.1);
            color: var(--color-text-main);
        }
        .glass-action-sheet-btn.cancel:hover {
            background: rgba(148, 163, 184, 0.2);
        }

        /* Image Lightbox Modal */
        .glass-lightbox-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(12px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            cursor: zoom-out;
            animation: fadeIn 0.2s ease;
        }
        .glass-lightbox-overlay img {
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            object-fit: contain;
        }
        .glass-lightbox-close {
            position: absolute;
            top: 20px;
            right: 24px;
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .glass-lightbox-close:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        /* ---------------- RESPONSIVE BREAKPOINT ---------------- */
        @media (max-width: 768px) {
            #k71-chat-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                height: calc(100dvh - 56px) !important;
                max-height: calc(100dvh - 56px) !important;
                min-height: 480px !important;
                overflow: hidden !important;
                display: flex !important;
                align-items: stretch !important;
                margin-bottom: 0 !important;
            }
            body.chat-active #k71-chat-wrapper {
                height: calc(100dvh - 56px) !important;
                max-height: calc(100dvh - 56px) !important;
                position: relative !important;
                inset: auto !important;
                z-index: 1 !important;
            }
            .glass-chat-frame {
                border-radius: 0 !important;
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                min-height: 100% !important;
                max-height: 100% !important;
                flex: 1 1 0% !important;
                overflow: hidden !important;
                position: relative;
            }
            .glass-sidebar {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                border-right: none !important;
                display: flex !important;
                flex-direction: column !important;
                flex: 1 1 0% !important;
                min-height: 0 !important;
                overflow: hidden !important;
            }
            .glass-conv-list {
                flex: 1 1 0% !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            .glass-chat-pane {
                display: none;
                position: absolute;
                inset: 0;
                width: 100% !important;
                height: 100% !important;
                z-index: 20;
                overflow: hidden;
                flex-direction: column;
            }
            body.chat-active .glass-sidebar,
            .glass-chat-frame.chat-active .glass-sidebar {
                display: none !important;
            }
            body.chat-active .glass-chat-pane,
            .glass-chat-frame.chat-active .glass-chat-pane {
                display: flex !important;
            }
            #glassActiveChat {
                height: 100% !important;
                max-height: 100% !important;
                min-height: 0 !important;
                flex: 1 1 0% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            }
            .glass-chat-header {
                height: 56px !important;
                padding: 0 12px !important;
                gap: 8px !important;
                flex-shrink: 0 !important;
                background: var(--glass-surface) !important;
                border-bottom: 1px solid var(--glass-border) !important;
            }
            .glass-chat-header-user {
                gap: 8px !important;
                min-width: 0 !important;
                flex: 1 1 auto !important;
            }
            .glass-back-btn {
                display: flex !important;
                width: 36px !important;
                height: 36px !important;
                margin-right: 2px !important;
            }
            .glass-chat-header-avatar {
                width: 38px !important;
                height: 38px !important;
            }
            .glass-chat-header-username {
                font-size: 14px !important;
                max-width: 180px !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }
            .glass-chat-header-status {
                font-size: 11px !important;
            }
            .glass-chat-feed {
                flex: 1 1 0% !important;
                min-height: 0 !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
                padding: 12px 14px !important;
                gap: 8px !important;
            }
            .glass-msg-row {
                max-width: 86% !important;
            }
            .glass-msg-bubble {
                font-size: 14px !important;
                padding: 9px 14px !important;
            }
            .glass-composer-bar {
                flex-shrink: 0 !important;
                padding: 10px 14px max(20px, env(safe-area-inset-bottom, 20px)) !important;
                background: var(--glass-surface) !important;
                z-index: 50 !important;
                border-top: 1px solid var(--glass-border) !important;
            }
            .glass-composer-pill {
                padding: 5px 12px !important;
                gap: 6px !important;
                border-radius: 26px !important;
                min-height: 50px !important;
            }
            .glass-composer-textarea {
                font-size: 15px !important;
                padding: 6px 4px !important;
                max-height: 80px !important;
                line-height: 1.4 !important;
            }
            .glass-composer-send-btn {
                padding: 6px 16px !important;
                font-size: 14px !important;
                min-height: 38px !important;
            }
        }

        @media (min-width: 769px) {
            .glass-chat-frame.chat-active .glass-back-btn {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#060a17] text-slate-900 dark:text-slate-100 min-h-screen antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation -->
    <x-k71-nav />

    <div id="k71-chat-wrapper">
        <div class="glass-chat-frame" id="glassMainContainer">
            
            <!-- ==============================================================
                 LEFT SIDEBAR: INBOX CONVERSATIONS & DUAL TABS
                 ============================================================== -->
            <aside class="glass-sidebar" id="glassSidebar">
                <!-- Header: Current User + New Chat Button -->
                <div class="glass-sidebar-header">
                    <div class="glass-user-profile">
                        @php
                            $uName = $user?->name ?? ($user?->username ?? 'Guest');
                            $uHandle = $user?->username ?? 'guest';
                            $uAvatar = $user?->avatar ?? null;
                        @endphp
                        <div class="glass-user-avatar-wrap" id="sidebarAvatarBtn" title="Click to upload profile photo">
                            <img src="{{ format_user_avatar($uAvatar, $uName) }}" 
                                 alt="{{ $uHandle }}" 
                                 class="glass-user-avatar my-user-avatar"
                                 id="mySidebarAvatar">
                            <span class="glass-avatar-camera-badge" title="Change photo">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </span>
                            <input type="file" id="sidebarAvatarInput" accept="image/jpeg,image/png,image/webp,image/jpg" style="display: none;">
                        </div>
                        <div style="display: flex; flex-direction: column; min-width: 0; cursor: pointer;" onclick="document.getElementById('sidebarAvatarInput').click()">
                            <span class="glass-user-name">&#64;{{ $uHandle }}</span>
                            <span style="font-size: 10px; font-weight: 700; color: #4f46e5; letter-spacing: 0.02em;">Edit Photo</span>
                        </div>
                    </div>
                    <button type="button" class="glass-icon-btn" id="glassNewChatBtn" title="Search user or start message">
                        <!-- Instagram / Pencil Edit Icon -->
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Instagram Dual Inbox Segmented Tabs (Primary vs Requests) -->
                <div class="glass-tabs-bar">
                    <button type="button" class="glass-tab-btn active" id="tabPrimaryBtn" data-tab="primary">
                        <span>Primary</span>
                    </button>
                    <button type="button" class="glass-tab-btn" id="tabRequestsBtn" data-tab="requests">
                        <span>Requests</span>
                        <span class="glass-tab-badge" id="requestsCountBadge" style="display: none;">0</span>
                    </button>
                </div>

                <!-- In-Chat Search / Filter Input -->
                <div class="glass-search-wrap">
                    <div class="glass-search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="glassFilterInput" class="glass-search-input" placeholder="Search conversations...">
                    </div>
                </div>

                <!-- Scrollable Conversations Stream -->
                <ul class="glass-conv-list" id="glassConversationsList">
                    <li style="padding: 36px 20px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                        Loading your direct messages...
                    </li>
                </ul>
            </aside>

            <!-- ==============================================================
                 RIGHT CHAT PANE: EMPTY STATE & ACTIVE CHAT
                 ============================================================== -->
            <main class="glass-chat-pane" id="glassChatPane">
                
                <!-- 1. EMPTY STATE (When no thread selected) -->
                <div class="glass-empty-state" id="glassEmptyState">
                    <div class="glass-empty-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </div>
                    <h2 class="glass-empty-title">Your Messages</h2>
                    <p class="glass-empty-desc">Send private messages and requests to any user, buyer, or creator across K71.</p>
                    <button type="button" class="glass-btn-primary" id="glassEmptySendBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Send Message</span>
                    </button>
                </div>

                <!-- 2. ACTIVE CHAT STATE -->
                <div id="glassActiveChat" style="display: none; height: 100%; flex-direction: column;">
                    
                    <!-- Chat Header -->
                    <div class="glass-chat-header">
                        <div class="glass-chat-header-user">
                            <!-- Instagram-Style Back Button -->
                            <button type="button" class="glass-back-btn" id="glassBackBtn" title="Back to Inbox" aria-label="Back to Inbox">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="19" y1="12" x2="5" y2="12"></line>
                                    <polyline points="12 19 5 12 12 5"></polyline>
                                </svg>
                            </button>
                            
                            <div style="position: relative; flex-shrink: 0;">
                                <img src="" alt="" class="glass-chat-header-avatar" id="headerAvatar">
                                <span class="glass-online-badge" id="headerOnlineDot" style="display: none;" title="Active now"></span>
                            </div>
                            <div class="glass-chat-header-info">
                                <span class="glass-chat-header-username" id="headerUsername">&#64;username</span>
                                <span class="glass-chat-header-status" id="headerStatus">
                                    <span class="glass-online-indicator"></span> Active now
                                </span>
                            </div>
                        </div>

                        <div>
                            <button type="button" class="glass-icon-btn" id="headerInfoBtn" title="Conversation Details">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- ⚡ INSTAGRAM DIRECT REQUEST ACTION BANNER (Shown for incoming unaccepted requests) -->
                    <div class="glass-request-banner" id="glassRequestBanner" style="display: none;">
                        <div class="glass-request-banner-header">
                            <div class="glass-request-badge-icon">⚡</div>
                            <div class="glass-request-banner-text">
                                <div class="glass-request-banner-title" id="requestBannerTitle">Message Request</div>
                                <div class="glass-request-banner-sub">They want to send you a message. Choose whether to accept or decline.</div>
                            </div>
                        </div>
                        <div class="glass-request-banner-actions">
                            <button type="button" class="glass-btn-decline" id="btnDeclineRequest">Decline</button>
                            <button type="button" class="glass-btn-accept" id="btnAcceptRequest">Accept Request</button>
                        </div>
                    </div>

                    <!-- Scrollable Messages Feed -->
                    <div class="glass-chat-feed" id="glassChatFeed">
                        <!-- Messages dynamically appended here -->
                    </div>

                    <!-- Quick Emojis Drawer -->
                    <div class="glass-emoji-drawer" id="glassEmojiDrawer">
                        <button type="button" class="glass-emoji-chip" data-emoji="❤️">❤️</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="😂">😂</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="🔥">🔥</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="👍">👍</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="😍">😍</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="🙌">🙌</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="🎉">🎉</button>
                        <button type="button" class="glass-emoji-chip" data-emoji="✨">✨</button>
                    </div>

                    <!-- Sticky Bottom Input Composer -->
                    <div class="glass-composer-bar">
                        <div class="glass-composer-pill">
                            <button type="button" class="glass-composer-emoji-btn" id="glassEmojiToggleBtn" title="Insert Emoji">
                                😊
                            </button>
                            <button type="button" class="glass-composer-emoji-btn" id="glassAttachBtn" title="Send Photo" style="font-size: 16px;">
                                📷
                            </button>
                            <input type="file" id="glassPhotoInput" accept="image/*" style="display: none;">
                            
                            <textarea id="glassMessageInput" 
                                      class="glass-composer-textarea" 
                                      rows="1" 
                                      placeholder="Message..." 
                                      autocomplete="off"></textarea>

                            <!-- Voice Recording Active Overlay (Instagram Direct Exact Layout) -->
                            <div class="glass-voice-recording-overlay" id="glassVoiceOverlay" style="display: none;">
                                <button type="button" class="glass-voice-cancel-btn" id="glassVoiceCancelBtn" title="Discard voice note">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                                <div class="glass-voice-recording-dot"></div>
                                <span class="glass-voice-timer" id="glassVoiceTimer">0:00</span>
                                <div class="glass-voice-waveform">
                                    <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                                </div>
                                <button type="button" class="glass-voice-confirm-btn" id="glassVoiceConfirmBtn" title="Send voice note">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                </button>
                            </div>

                            <!-- Voice Note Mic Button (Tap to record) -->
                            <button type="button" class="glass-composer-voice-btn" id="glassVoiceBtn" title="Tap to record voice message (Instagram style)">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"></path>
                                    <path d="M19 10v2a7 7 0 01-14 0v-2"></path>
                                    <line x1="12" y1="19" x2="12" y2="23"></line>
                                    <line x1="8" y1="23" x2="16" y2="23"></line>
                                </svg>
                            </button>

                            <button type="button" class="glass-composer-send-btn" id="glassSendBtn" title="Send" style="display: none;">
                                Send
                            </button>
                        </div>
                    </div>
                </div>

            </main>
        </div>

        <!-- ==============================================================
             SEARCH ANY USER MODAL (Instagram Style)
             ============================================================== -->
        <div class="glass-modal-backdrop" id="glassSearchModal">
            <div class="glass-modal-card">
                <div class="glass-modal-header">
                    <div style="width: 20px;"></div>
                    <div class="glass-modal-title">New Message</div>
                    <button type="button" class="glass-modal-close" id="glassCloseModalBtn">&times;</button>
                </div>
                
                <div class="glass-modal-search">
                    <span class="glass-modal-search-label">To:</span>
                    <input type="text" id="glassUserSearchInput" class="glass-modal-search-input" placeholder="Search any username or name..." autocomplete="off">
                </div>

                <ul class="glass-modal-user-list" id="glassModalUserList">
                    <li style="padding: 32px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                        Type a username to find any buyer, customer, or seller across K71.
                    </li>
                </ul>
            </div>
        </div>

        <!-- ==============================================================
             INSTAGRAM ACTION SHEET MODAL (Long-Press / Touch / Context Menu)
             ============================================================== -->
        <div class="glass-action-sheet-overlay" id="glassActionSheetModal" style="display: none;">
            <div class="glass-action-sheet">
                <div class="glass-action-sheet-handle"></div>
                <div class="glass-action-sheet-header" id="actionSheetHeader">Options</div>
                <div class="glass-action-sheet-actions">
                    <button type="button" class="glass-action-sheet-btn danger" id="actionSheetDangerBtn">Delete</button>
                    <button type="button" class="glass-action-sheet-btn cancel" id="actionSheetCancelBtn">Cancel</button>
                </div>
            </div>
        </div>

        <!-- ==============================================================
             IMAGE LIGHTBOX VIEWER
             ============================================================== -->
        <div class="glass-lightbox-overlay" id="glassImageLightbox" style="display: none;">
            <button type="button" class="glass-lightbox-close" id="glassLightboxClose">&times;</button>
            <img id="lightboxImg" src="" alt="Enlarged photo">
        </div>

    </div>

    <!-- Real-time Direct Messaging Engine -->
    <script>
    (function() {
        'use strict';

        const CURRENT_USER_ID = {{ auth()->id() }};
        const INITIAL_TARGET_USER_ID = {{ !empty($targetUserId) ? (int)$targetUserId : 'null' }};
        let myCurrentAvatarUrl = '{{ format_user_avatar($uAvatar, $uName) }}';
        let currentTab = 'primary';
        let currentConversationId = null;
        let activeRecipientId = null;
        let activeConversationStatus = 'accepted';
        let conversationsCache = [];
        let messagesCache = [];
        let threadMemoryCache = {};
        let isPollingActive = true;
        let searchDebounceTimer = null;
        let messagePollTimer = null;
        let inboxPollTimer = null;
        let lastTypingPing = 0;
        let pendingAction = null; // { type: 'conversation'|'message', id: 123, label: '...' }

        // DOM References
        const mainContainer = document.getElementById('glassMainContainer');
        const conversationsList = document.getElementById('glassConversationsList');
        const filterInput = document.getElementById('glassFilterInput');
        const emptyState = document.getElementById('glassEmptyState');
        const activeChat = document.getElementById('glassActiveChat');
        const chatFeed = document.getElementById('glassChatFeed');
        const headerAvatar = document.getElementById('headerAvatar');
        const headerUsername = document.getElementById('headerUsername');
        const headerStatus = document.getElementById('headerStatus');
        const messageInput = document.getElementById('glassMessageInput');
        const sendBtn = document.getElementById('glassSendBtn');
        const backBtn = document.getElementById('glassBackBtn');
        const newChatBtn = document.getElementById('glassNewChatBtn');
        const emptySendBtn = document.getElementById('glassEmptySendBtn');
        const searchModal = document.getElementById('glassSearchModal');
        const closeModalBtn = document.getElementById('glassCloseModalBtn');
        const userSearchInput = document.getElementById('glassUserSearchInput');
        const modalUserList = document.getElementById('glassModalUserList');
        const emojiToggleBtn = document.getElementById('glassEmojiToggleBtn');
        const emojiDrawer = document.getElementById('glassEmojiDrawer');
        const tabPrimaryBtn = document.getElementById('tabPrimaryBtn');
        const tabRequestsBtn = document.getElementById('tabRequestsBtn');
        const requestsCountBadge = document.getElementById('requestsCountBadge');
        const requestBanner = document.getElementById('glassRequestBanner');
        const requestBannerTitle = document.getElementById('requestBannerTitle');
        const btnAcceptRequest = document.getElementById('btnAcceptRequest');
        const btnDeclineRequest = document.getElementById('btnDeclineRequest');

        // Upgraded Instagram Feature Elements
        const attachBtn = document.getElementById('glassAttachBtn');
        const photoInput = document.getElementById('glassPhotoInput');
        const actionSheetModal = document.getElementById('glassActionSheetModal');
        const actionSheetHeader = document.getElementById('actionSheetHeader');
        const actionSheetDangerBtn = document.getElementById('actionSheetDangerBtn');
        const actionSheetCancelBtn = document.getElementById('actionSheetCancelBtn');
        const lightboxModal = document.getElementById('glassImageLightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        const lightboxClose = document.getElementById('glassLightboxClose');

        // ---------------------------------------------------------------------
        // 1. INBOX TABS & CONVERSATIONS ENGINE
        // ---------------------------------------------------------------------
        function switchTab(tab) {
            currentTab = tab;
            if (tab === 'requests') {
                tabRequestsBtn.classList.add('active');
                tabPrimaryBtn.classList.remove('active');
            } else {
                tabPrimaryBtn.classList.add('active');
                tabRequestsBtn.classList.remove('active');
            }
            fetchConversations(false);
        }

        tabPrimaryBtn.addEventListener('click', () => switchTab('primary'));
        tabRequestsBtn.addEventListener('click', () => switchTab('requests'));

        function fetchConversations(silent = false) {
            fetch(`/api/chat/get_conversations.php?tab=${currentTab}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(result => {
                if (result.success && Array.isArray(result.data)) {
                    conversationsCache = result.data;

                    // Update Requests badge count dynamically
                    const reqCount = parseInt(result.requests_count, 10) || 0;
                    if (reqCount > 0) {
                        requestsCountBadge.textContent = reqCount;
                        requestsCountBadge.style.display = 'inline-block';
                    } else {
                        requestsCountBadge.style.display = 'none';
                    }

                    renderConversations(filterInput.value.trim());
                } else if (!silent) {
                    conversationsList.innerHTML = `
                        <li style="padding: 30px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                            ${result.error || 'No conversations found.'}
                        </li>`;
                }
            })
            .catch(err => {
                if (!silent) console.error('Error fetching conversations:', err);
            });
        }

        function renderConversations(filterKeyword = '') {
            const query = filterKeyword.toLowerCase();
            const filtered = conversationsCache.filter(c => {
                const username = (c.recipient && c.recipient.username) ? c.recipient.username.toLowerCase() : '';
                const name = (c.recipient && c.recipient.name) ? c.recipient.name.toLowerCase() : '';
                const lastBody = (c.last_message && c.last_message.body) ? c.last_message.body.toLowerCase() : '';
                return username.includes(query) || name.includes(query) || lastBody.includes(query);
            });

            if (filtered.length === 0) {
                const emptyMsg = currentTab === 'requests' 
                    ? 'No incoming message requests.' 
                    : (query ? 'No matching conversations.' : 'No messages yet.<br>Click the pencil icon above to start a chat!');
                conversationsList.innerHTML = `
                    <li style="padding: 36px 20px; text-align: center; color: var(--color-text-muted); font-size: 13px; line-height: 1.5;">
                        ${emptyMsg}
                    </li>`;
                return;
            }

            conversationsList.innerHTML = filtered.map(c => {
                const isActive = (c.id === currentConversationId);
                const isUnread = (c.unread_count > 0);
                const isOnline = Boolean(c.recipient && c.recipient.is_online);
                const prefix = (c.last_message && c.last_message.is_sent_by_me) ? 'You: ' : '';
                const lastMsgText = (c.last_message && c.last_message.body) ? c.last_message.body : 'Tap to start chatting';
                const lastMsgTime = (c.last_message && c.last_message.time_formatted) ? c.last_message.time_formatted : '';
                const recUsername = (c.recipient && c.recipient.username) ? c.recipient.username : 'user';
                const recName = (c.recipient && c.recipient.name) ? c.recipient.name : 'User';
                const recAvatar = (c.recipient && c.recipient.profile_pic) ? c.recipient.profile_pic : 'https://ui-avatars.com/api/?name=User&background=0095F6&color=fff';
                const recId = (c.recipient && c.recipient.id) ? c.recipient.id : 0;

                return `
                    <li class="glass-conv-item ${isActive ? 'active' : ''}" data-conv-id="${c.id}" data-recipient-id="${recId}" data-username="${escapeHtml(recUsername)}">
                        <div class="glass-avatar-wrap" style="position: relative;">
                            <img src="${escapeHtml(recAvatar)}" alt="${escapeHtml(recName)}" class="glass-conv-avatar">
                            ${isOnline ? '<span class="glass-online-badge" title="Active now"></span>' : ''}
                            ${isUnread ? '<div class="glass-unread-dot"></div>' : ''}
                        </div>
                        <div class="glass-conv-details">
                            <div class="glass-conv-top">
                                <div class="glass-conv-username">&#64;${escapeHtml(recUsername)}</div>
                                <div class="glass-conv-time">${escapeHtml(lastMsgTime)}</div>
                            </div>
                            <div class="glass-conv-last-msg ${isUnread ? 'unread-bold' : ''}">
                                <span>${escapeHtml(prefix + lastMsgText)}</span>
                            </div>
                        </div>
                    </li>
                `;
            }).join('');

            // Click & Long-Press Handlers on Conversation Items
            conversationsList.querySelectorAll('.glass-conv-item').forEach(item => {
                const convId = parseInt(item.getAttribute('data-conv-id'), 10);
                const recId = parseInt(item.getAttribute('data-recipient-id'), 10);
                const uName = item.getAttribute('data-username') || 'user';

                // Standard Click to open
                item.addEventListener('click', (e) => {
                    if (e.defaultPrevented) return;
                    loadConversation(convId, recId);
                });

                // Long-Press for Mobile Touch Devices (500ms)
                let pressTimer = null;
                item.addEventListener('touchstart', (e) => {
                    pressTimer = setTimeout(() => {
                        e.preventDefault();
                        if (navigator.vibrate) navigator.vibrate(50);
                        openActionSheet('conversation', convId, uName);
                    }, 500);
                }, { passive: false });

                item.addEventListener('touchend', () => clearTimeout(pressTimer));
                item.addEventListener('touchmove', () => clearTimeout(pressTimer));

                // Desktop Right-Click Context Menu
                item.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    openActionSheet('conversation', convId, uName);
                });
            });
        }

        filterInput.addEventListener('input', (e) => {
            renderConversations(e.target.value.trim());
        });

        // ---------------------------------------------------------------------
        // 2. ACTIVE THREAD & MESSAGES ENGINE
        // ---------------------------------------------------------------------
        function loadConversation(convId, recipientId) {
            currentConversationId = convId;
            activeRecipientId = recipientId;

            mainContainer.classList.add('chat-active');
            document.body.classList.add('chat-active');
            emptyState.style.display = 'none';
            activeChat.style.display = 'flex';

            adjustMobileLayout();
            renderConversations(filterInput.value.trim());
            updateSendButtonState();

            // Instant thread restore from in-memory cache
            if (threadMemoryCache[convId] && threadMemoryCache[convId].messages && threadMemoryCache[convId].recipient) {
                const cached = threadMemoryCache[convId];
                messagesCache = cached.messages;
                headerAvatar.src = cached.recipient.profile_pic || 'https://ui-avatars.com/api/?name=User&background=0095F6&color=fff';
                headerAvatar.alt = cached.recipient.name || 'User';
                headerUsername.textContent = '@' + (cached.recipient.username || 'user');
                renderMessages(messagesCache, cached.recipient);
                scrollFeedToBottom(false);
            } else {
                chatFeed.innerHTML = `
                    <div style="padding: 40px; text-align: center; color: var(--color-text-muted); font-size: 13px;" class="flex flex-col items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span>Loading messages...</span>
                    </div>`;
            }

            fetchMessages(true);
        }

        function fetchMessages(scrollToBottom = false) {
            if (!currentConversationId) return;

            fetch(`/api/chat/get_messages.php?conversation_id=${currentConversationId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(result => {
                const recipient = result.recipient || (result.data ? result.data.recipient : null);
                if (result.success && recipient) {
                    const convInfo = result.conversation || (result.data ? result.data.conversation : {});
                    activeConversationStatus = convInfo.status || 'accepted';

                    // Update Active Chat Header with Online Indicator
                    headerAvatar.src = recipient.profile_pic || 'https://ui-avatars.com/api/?name=User&background=0095F6&color=fff';
                    headerAvatar.alt = recipient.name || 'User';
                    headerUsername.textContent = '@' + (recipient.username || 'user');

                    const isOnline = Boolean(recipient.is_online);
                    const headerOnlineDot = document.getElementById('headerOnlineDot');
                    if (headerOnlineDot) {
                        headerOnlineDot.style.display = isOnline ? 'block' : 'none';
                    }
                    if (isOnline) {
                        headerStatus.innerHTML = `<span class="glass-online-pill"><span class="glass-online-dot"></span> Active now</span> • ${escapeHtml(recipient.name || 'User')}`;
                    } else {
                        const statusTxt = recipient.status_label ? escapeHtml(recipient.status_label) : 'Offline';
                        headerStatus.innerHTML = `<span style="color:var(--color-text-muted);font-size:12px;">${statusTxt}</span> • ${escapeHtml(recipient.name || 'User')}`;
                    }

                    // Check Instagram Request Action Banner
                    if (convInfo.is_incoming_request) {
                        requestBannerTitle.textContent = `Message Request from @${recipient.username || 'user'}`;
                        requestBanner.style.display = 'flex';
                    } else {
                        requestBanner.style.display = 'none';
                    }

                    // Render Messages
                    const newMessages = result.messages || (result.data ? result.data.messages : []);
                    const wasNearBottom = isUserNearBottom();

                    // Update in-memory thread cache
                    threadMemoryCache[currentConversationId] = {
                        messages: newMessages,
                        recipient: recipient
                    };

                    if (JSON.stringify(newMessages) !== JSON.stringify(messagesCache)) {
                        messagesCache = newMessages;
                        renderMessages(newMessages, recipient);
                        if (scrollToBottom || wasNearBottom) {
                            scrollFeedToBottom(scrollToBottom);
                        }
                    }

                    // Dispatch unread update event for instant red-dot clearance
                    window.dispatchEvent(new CustomEvent('k71-messages-read'));

                    // Instagram-Style 3-Dot Bouncing Typing Indicator
                    const isTyping = Boolean(result.is_typing);
                    const existingTyping = document.getElementById('glassTypingRow');
                    if (isTyping) {
                        if (!existingTyping) {
                            const recipientAvatar = recipient.profile_pic || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(recipient.name || 'User') + '&background=0095F6&color=fff');
                            const typingEl = document.createElement('div');
                            typingEl.id = 'glassTypingRow';
                            typingEl.className = 'glass-typing-row';
                            typingEl.innerHTML = `
                                <img src="${escapeHtml(recipientAvatar)}" class="glass-msg-avatar" alt="">
                                <div class="glass-typing-bubble">
                                    <span class="typing-dot"></span>
                                    <span class="typing-dot"></span>
                                    <span class="typing-dot"></span>
                                </div>
                            `;
                            chatFeed.appendChild(typingEl);
                            if (wasNearBottom) scrollFeedToBottom(true);
                        }
                    } else if (existingTyping) {
                        existingTyping.remove();
                    }

                } else if (result.error) {
                    chatFeed.innerHTML = `
                        <div style="padding: 40px; text-align: center; color: #EF4444; font-size: 13.5px; font-weight: 700;">
                            ${escapeHtml(result.error)}
                        </div>`;
                }
            })
            .catch(err => {
                console.error('Error fetching messages:', err);
            });
        }

        function renderMessages(messages, recipient) {
            const recipientAvatar = (recipient && recipient.profile_pic) 
                ? recipient.profile_pic 
                : ('https://ui-avatars.com/api/?name=' + encodeURIComponent((recipient && recipient.name) ? recipient.name : 'User') + '&background=0095F6&color=fff');

            if (messages.length === 0) {
                chatFeed.innerHTML = `
                    <div style="padding: 60px 20px; text-align: center; color: var(--color-text-muted);">
                        <div style="width: 68px; height: 68px; margin: 0 auto 12px; border-radius: 50%; overflow: hidden; border: 1.5px solid var(--glass-border);">
                            <img src="${escapeHtml(recipientAvatar)}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h3 style="margin: 0; font-size: 16px; color: var(--color-text-main); font-weight: 800;">&#64;${escapeHtml((recipient && recipient.username) ? recipient.username : 'user')}</h3>
                        <p style="margin: 4px 0 16px; font-size: 12.5px;">${escapeHtml((recipient && recipient.name) ? recipient.name : 'User')} • Direct Messages</p>
                        <p style="font-size: 13px; color: var(--color-text-muted);">Say hello to start the conversation!</p>
                    </div>`;
                return;
            }

            let lastDate = '';
            let html = '';

            messages.forEach(msg => {
                if (msg.date_header && msg.date_header !== lastDate) {
                    lastDate = msg.date_header;
                    html += `<div class="glass-date-divider"><span>${escapeHtml(lastDate)}</span></div>`;
                }

                const isSent = msg.is_mine;
                const isVoice = (msg.attachment_type === 'voice') || (msg.attachment_url && (msg.attachment_url.includes('/chat_voice/') || /\.(webm|ogg|wav|mp3|m4a|aac)$/i.test(msg.attachment_url)));
                let attachmentHtml = '';

                if (isVoice) {
                    let durationLabel = '0:05';
                    if (msg.body && msg.body.includes(':')) {
                        const match = msg.body.match(/\((\d+:\d+)\)/);
                        if (match) durationLabel = match[1];
                    }
                    attachmentHtml = `
                        <div class="glass-voice-player ${isSent ? 'sent' : 'received'}" data-audio-url="${escapeHtml(msg.attachment_url)}">
                            <button type="button" class="glass-voice-play-btn" title="Play Voice Message" aria-label="Play Voice Message">
                                <svg class="play-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                <svg class="pause-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                            </button>
                            <div class="glass-voice-wave-container">
                                <div class="glass-voice-bars">
                                    <span style="height: 6px;"></span>
                                    <span style="height: 14px;"></span>
                                    <span style="height: 18px;"></span>
                                    <span style="height: 10px;"></span>
                                    <span style="height: 16px;"></span>
                                    <span style="height: 12px;"></span>
                                    <span style="height: 20px;"></span>
                                    <span style="height: 8px;"></span>
                                    <span style="height: 15px;"></span>
                                    <span style="height: 11px;"></span>
                                    <span style="height: 17px;"></span>
                                    <span style="height: 7px;"></span>
                                </div>
                                <div class="glass-voice-progress-track">
                                    <div class="glass-voice-progress-fill" style="width: 0%;"></div>
                                </div>
                            </div>
                            <span class="glass-voice-duration">${escapeHtml(durationLabel)}</span>
                            <audio preload="auto" playsinline src="${escapeHtml(msg.attachment_url)}"></audio>
                        </div>
                    `;
                } else if (msg.attachment_url) {
                    attachmentHtml = `
                        <div class="glass-msg-image-wrap" data-img-url="${escapeHtml(msg.attachment_url)}">
                            <img src="${escapeHtml(msg.attachment_url)}" alt="Photo" loading="lazy">
                        </div>
                    `;
                }

                let bodyHtml = '';
                if (isVoice) {
                    // Do not show duplicated text bubble if body is voice note indicator
                    if (msg.body && !msg.body.startsWith('🎤 Voice message') && !msg.body.startsWith('🎤 Voice note')) {
                        bodyHtml = `<div class="glass-msg-bubble ${msg.is_optimistic ? 'optimistic' : ''}">${escapeHtml(msg.body).replace(/\n/g, '<br>')}</div>`;
                    }
                } else if (msg.body && msg.body !== '📷 Photo') {
                    bodyHtml = `<div class="glass-msg-bubble ${msg.is_optimistic ? 'optimistic' : ''}">${escapeHtml(msg.body).replace(/\n/g, '<br>')}</div>`;
                } else if (!attachmentHtml) {
                    bodyHtml = `<div class="glass-msg-bubble">📷 Photo</div>`;
                }

                const unsendBtnHtml = (isSent && !msg.is_optimistic)
                    ? `<button type="button" class="glass-msg-unsend-btn" data-msg-id="${msg.id}" title="Unsend message">&times;</button>`
                    : '';

                html += `
                    <div class="glass-msg-row ${isSent ? 'sent' : 'received'}" data-msg-id="${msg.id}" data-is-mine="${isSent ? '1' : '0'}">
                        ${!isSent ? `<img src="${escapeHtml(recipientAvatar)}" alt="" class="glass-msg-avatar">` : `<img src="${escapeHtml(myCurrentAvatarUrl)}" alt="" class="glass-msg-avatar my-user-avatar">`}
                        <div>
                            ${attachmentHtml}
                            ${bodyHtml}
                            <div class="glass-msg-meta">${escapeHtml(msg.time_formatted || '')}</div>
                        </div>
                        ${unsendBtnHtml}
                    </div>
                `;
            });

            chatFeed.innerHTML = html;

            // Bind click to open lightbox on photo thumbnails
            chatFeed.querySelectorAll('.glass-msg-image-wrap').forEach(wrap => {
                wrap.addEventListener('click', () => {
                    const url = wrap.getAttribute('data-img-url');
                    if (url) window.openLightbox(url);
                });
            });

            // Bind Voice Player audio playback controls (Fast Responsive Tap & Clean Replay)
            chatFeed.querySelectorAll('.glass-voice-player').forEach(player => {
                const audio = player.querySelector('audio');
                const playBtn = player.querySelector('.glass-voice-play-btn');
                const playIcon = playBtn ? playBtn.querySelector('.play-icon') : null;
                const pauseIcon = playBtn ? playBtn.querySelector('.pause-icon') : null;
                const progressFill = player.querySelector('.glass-voice-progress-fill');
                const durationSpan = player.querySelector('.glass-voice-duration');
                const originalDurationText = durationSpan ? durationSpan.textContent : '0:05';

                if (!audio) return;

                function formatTime(secs) {
                    if (isNaN(secs) || !isFinite(secs) || secs < 0) return '0:00';
                    const m = Math.floor(secs / 60);
                    const s = Math.floor(secs % 60);
                    return `${m}:${s < 10 ? '0' : ''}${s}`;
                }

                audio.addEventListener('loadedmetadata', () => {
                    if (audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
                        if (durationSpan && (originalDurationText === '0:00' || originalDurationText === '0:05')) {
                            durationSpan.textContent = formatTime(audio.duration);
                        }
                    }
                });

                audio.addEventListener('timeupdate', () => {
                    if (!audio.duration || isNaN(audio.duration)) return;
                    const pct = Math.min(100, Math.max(0, (audio.currentTime / audio.duration) * 100));
                    if (progressFill) progressFill.style.width = `${pct}%`;
                    const rem = Math.max(0, audio.duration - audio.currentTime);
                    if (durationSpan) {
                        durationSpan.textContent = formatTime(rem);
                    }
                });

                function resetToStart() {
                    player.classList.remove('is-playing');
                    if (playIcon) playIcon.style.display = 'block';
                    if (pauseIcon) pauseIcon.style.display = 'none';
                    if (progressFill) progressFill.style.width = '0%';
                    if (durationSpan) durationSpan.textContent = originalDurationText;
                }

                audio.addEventListener('ended', () => {
                    audio.currentTime = 0;
                    resetToStart();
                });

                audio.addEventListener('pause', () => {
                    player.classList.remove('is-playing');
                    if (playIcon) playIcon.style.display = 'block';
                    if (pauseIcon) pauseIcon.style.display = 'none';
                });

                audio.addEventListener('play', () => {
                    // Pause any other playing voice notes across the page
                    document.querySelectorAll('.glass-voice-player audio').forEach(otherAudio => {
                        if (otherAudio !== audio && !otherAudio.paused) {
                            otherAudio.pause();
                            otherAudio.currentTime = 0;
                        }
                    });
                    player.classList.add('is-playing');
                    if (playIcon) playIcon.style.display = 'none';
                    if (pauseIcon) pauseIcon.style.display = 'block';
                });

                // Toggle Playback: Play once -> Tap again pause -> Tap again replay
                function togglePlay(e) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    // If audio finished or near the end, reset to 0 so it cleanly plays again from start
                    if (audio.ended || (audio.duration && audio.currentTime >= audio.duration - 0.2)) {
                        audio.currentTime = 0;
                        if (progressFill) progressFill.style.width = '0%';
                    }

                    if (audio.paused) {
                        // Pause all others first
                        document.querySelectorAll('.glass-voice-player audio').forEach(otherAudio => {
                            if (otherAudio !== audio && !otherAudio.paused) {
                                otherAudio.pause();
                                otherAudio.currentTime = 0;
                            }
                        });

                        const playPromise = audio.play();
                        if (playPromise !== undefined) {
                            playPromise.then(() => {
                                player.classList.add('is-playing');
                                if (playIcon) playIcon.style.display = 'none';
                                if (pauseIcon) pauseIcon.style.display = 'block';
                            }).catch(err => {
                                console.warn('Audio playback error, retrying with load():', err);
                                audio.load();
                                audio.play().catch(e2 => console.error('Audio play retry error:', e2));
                            });
                        }
                    } else {
                        audio.pause();
                        player.classList.remove('is-playing');
                        if (playIcon) playIcon.style.display = 'block';
                        if (pauseIcon) pauseIcon.style.display = 'none';
                    }
                }

                // Attach to Play Button
                if (playBtn) {
                    playBtn.addEventListener('click', togglePlay);
                }

                // Attach to entire voice bubble for ultra-responsive mobile tap
                player.addEventListener('click', (e) => {
                    if (e.target.closest('.glass-voice-progress-track') || e.target.closest('.glass-msg-unsend-btn')) {
                        return; // Let progress scrubbing or unsend action handle its own click
                    }
                    togglePlay(e);
                });

                const track = player.querySelector('.glass-voice-progress-track');
                if (track) {
                    track.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (!audio.duration || isNaN(audio.duration)) return;
                        const rect = track.getBoundingClientRect();
                        const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                        audio.currentTime = pos * audio.duration;
                        if (progressFill) progressFill.style.width = `${pos * 100}%`;
                        if (audio.paused) {
                            audio.play().catch(() => {});
                        }
                    });
                }
            });

            // Bind Unsend Button clicks
            chatFeed.querySelectorAll('.glass-msg-unsend-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const msgId = parseInt(btn.getAttribute('data-msg-id'), 10);
                    if (msgId) {
                        openActionSheet('message', msgId, '');
                    }
                });
            });

            // Bind Long-press on Sent Messages
            chatFeed.querySelectorAll('.glass-msg-row.sent').forEach(row => {
                const msgId = parseInt(row.getAttribute('data-msg-id'), 10);
                if (!msgId) return;

                let pressTimer = null;
                row.addEventListener('touchstart', (e) => {
                    pressTimer = setTimeout(() => {
                        e.preventDefault();
                        if (navigator.vibrate) navigator.vibrate(50);
                        openActionSheet('message', msgId, '');
                    }, 500);
                }, { passive: false });

                row.addEventListener('touchend', () => clearTimeout(pressTimer));
                row.addEventListener('touchmove', () => clearTimeout(pressTimer));
            });
        }

        function isUserNearBottom() {
            return (chatFeed.scrollHeight - chatFeed.scrollTop - chatFeed.clientHeight) <= 120;
        }

        function scrollFeedToBottom(smooth = false) {
            if (smooth) {
                chatFeed.scrollTo({ top: chatFeed.scrollHeight, behavior: 'smooth' });
            } else {
                chatFeed.scrollTop = chatFeed.scrollHeight;
            }
        }

        // ---------------------------------------------------------------------
        // 3. SEND MESSAGE & INSTAGRAM AUTO-ACCEPT ON REPLY
        // ---------------------------------------------------------------------
        function sendMessage() {
            const text = messageInput.value.trim();
            if (!text || !currentConversationId) return;

            messageInput.value = '';
            updateSendButtonState();
            messageInput.style.height = 'auto';

            // Optimistic UI Append
            const optimisticMsg = {
                id: 'opt_' + Date.now(),
                body: text,
                sender_id: CURRENT_USER_ID,
                is_mine: true,
                is_optimistic: true,
                created_at: new Date().toISOString(),
                time_formatted: 'Sending...'
            };

            messagesCache.push(optimisticMsg);
            renderMessages(messagesCache, {
                username: headerUsername.textContent.replace('@', ''),
                name: headerStatus.textContent,
                profile_pic: headerAvatar.src
            });
            scrollFeedToBottom(true);

            // API POST
            fetch('/api/chat/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    conversation_id: currentConversationId,
                    message: text
                })
            })
            .then(res => res.json())
            .then(result => {
                const sentMsg = result.data || result.message;
                if (result.success && sentMsg) {
                    const index = messagesCache.findIndex(m => m.id === optimisticMsg.id);
                    if (index !== -1) {
                        messagesCache[index] = sentMsg;
                    }
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                    
                    if (result.conversation_status === 'accepted') {
                        requestBanner.style.display = 'none';
                        activeConversationStatus = 'accepted';
                    }

                    fetchConversations(true);
                } else {
                    alert(result.error || 'Failed to send message.');
                    messagesCache = messagesCache.filter(m => m.id !== optimisticMsg.id);
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                }
            })
            .catch(err => {
                console.error('Error sending message:', err);
                alert('Network error while sending message.');
            });
        }

        function updateSendButtonState() {
            const hasText = messageInput && messageInput.value.trim().length > 0;
            if (hasText) {
                if (sendBtn) {
                    sendBtn.style.display = 'inline-flex';
                    sendBtn.classList.add('active');
                    sendBtn.classList.remove('disabled');
                }
                if (typeof voiceBtn !== 'undefined' && voiceBtn) {
                    voiceBtn.style.display = 'none';
                }
            } else {
                if (sendBtn) {
                    sendBtn.style.display = 'none';
                    sendBtn.classList.remove('active');
                }
                if (typeof voiceBtn !== 'undefined' && voiceBtn) {
                    voiceBtn.style.display = 'inline-flex';
                }
            }
        }

        // Emit typing indicator with debounce
        messageInput.addEventListener('input', () => {
            updateSendButtonState();
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 100) + 'px';

            const now = Date.now();
            if (now - lastTypingPing > 2200 && currentConversationId) {
                lastTypingPing = now;
                fetch('/api/chat/typing.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ conversation_id: currentConversationId })
                }).catch(() => {});
            }
        });

        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        sendBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text) {
                messageInput.focus();
                return;
            }
            sendMessage();
        });

        backBtn.addEventListener('click', () => {
            mainContainer.classList.remove('chat-active');
            document.body.classList.remove('chat-active');
            currentConversationId = null;
            activeRecipientId = null;
            if (window.innerWidth <= 768) {
                adjustMobileLayout();
            } else {
                activeChat.style.display = 'none';
                emptyState.style.display = 'flex';
            }
            conversationsList.querySelectorAll('.glass-conv-item').forEach(i => i.classList.remove('active'));
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        // Photo Upload Handling
        attachBtn.addEventListener('click', () => {
            if (!currentConversationId) {
                alert('Please select or start a chat first to send a photo.');
                return;
            }
            photoInput.click();
        });

        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];
            if (!file || !currentConversationId) return;

            const optId = 'opt_img_' + Date.now();
            const tempUrl = URL.createObjectURL(file);
            const optimisticMsg = {
                id: optId,
                body: '📷 Uploading...',
                attachment_url: tempUrl,
                attachment_type: 'image',
                sender_id: CURRENT_USER_ID,
                is_mine: true,
                is_optimistic: true,
                created_at: new Date().toISOString(),
                time_formatted: 'Sending photo...'
            };

            messagesCache.push(optimisticMsg);
            renderMessages(messagesCache, {
                username: headerUsername.textContent.replace('@', ''),
                name: headerStatus.textContent,
                profile_pic: headerAvatar.src
            });
            scrollFeedToBottom(true);

            const formData = new FormData();
            formData.append('conversation_id', currentConversationId);
            formData.append('photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('/api/chat/upload_photo.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                photoInput.value = '';
                const sentMsg = result.data || result.message;
                if (result.success && sentMsg) {
                    const idx = messagesCache.findIndex(m => m.id === optId);
                    if (idx !== -1) {
                        messagesCache[idx] = sentMsg;
                    }
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                    scrollFeedToBottom(true);
                    fetchConversations(true);
                } else {
                    alert(result.error || 'Failed to upload photo.');
                    messagesCache = messagesCache.filter(m => m.id !== optId);
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                }
            })
            .catch(err => {
                photoInput.value = '';
                console.error('Photo upload error:', err);
                alert('Network error while uploading photo.');
            });
        });

        // Lightbox Functions
        window.openLightbox = function(url) {
            lightboxImg.src = url;
            lightboxModal.style.display = 'flex';
        };
        lightboxClose.addEventListener('click', () => {
            lightboxModal.style.display = 'none';
        });
        lightboxModal.addEventListener('click', (e) => {
            if (e.target === lightboxModal || e.target === lightboxClose) {
                lightboxModal.style.display = 'none';
            }
        });

        // Instagram Long-Press Action Sheet Functions
        function openActionSheet(type, id, label) {
            pendingAction = { type, id, label };
            if (type === 'conversation') {
                actionSheetHeader.textContent = `Chat with @${label}`;
                actionSheetDangerBtn.textContent = 'Delete Chat';
            } else {
                actionSheetHeader.textContent = 'Message Options';
                actionSheetDangerBtn.textContent = 'Unsend Message';
            }
            actionSheetModal.style.display = 'flex';
        }

        function closeActionSheet() {
            actionSheetModal.style.display = 'none';
            pendingAction = null;
        }

        actionSheetCancelBtn.addEventListener('click', closeActionSheet);
        actionSheetModal.addEventListener('click', (e) => {
            if (e.target === actionSheetModal) closeActionSheet();
        });

        actionSheetDangerBtn.addEventListener('click', () => {
            if (!pendingAction) return;
            const { type, id } = pendingAction;
            closeActionSheet();

            if (type === 'conversation') {
                if (!confirm('Delete this conversation? All messages will be permanently deleted.')) return;
                fetch('/api/chat/delete_conversation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ conversation_id: id })
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        if (currentConversationId === id) {
                            currentConversationId = null;
                            mainContainer.classList.remove('chat-active');
                            activeChat.style.display = 'none';
                            emptyState.style.display = 'flex';
                        }
                        fetchConversations(false);
                    } else {
                        alert(result.error || 'Failed to delete chat.');
                    }
                })
                .catch(err => console.error('Delete chat error:', err));
            } else if (type === 'message') {
                unsendMessage(id);
            }
        });

        function unsendMessage(msgId) {
            fetch('/api/chat/unsend_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message_id: msgId })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    messagesCache = messagesCache.filter(m => m.id !== msgId);
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                    fetchConversations(true);
                } else {
                    alert(result.error || 'Failed to unsend message.');
                }
            })
            .catch(err => console.error('Unsend error:', err));
        }

        // Emoji Drawer
        emojiToggleBtn.addEventListener('click', () => {
            emojiDrawer.classList.toggle('open');
        });

        emojiDrawer.querySelectorAll('.glass-emoji-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                messageInput.value += chip.getAttribute('data-emoji');
                updateSendButtonState();
                messageInput.focus();
            });
        });

        // ---------------------------------------------------------------------
        // 4. INSTAGRAM REQUEST ACTION BUTTONS (Accept / Decline)
        // ---------------------------------------------------------------------
        btnAcceptRequest.addEventListener('click', () => {
            if (!currentConversationId) return;
            btnAcceptRequest.disabled = true;
            btnAcceptRequest.textContent = 'Accepting...';

            fetch('/api/chat/request_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    conversation_id: currentConversationId,
                    action: 'accept'
                })
            })
            .then(res => res.json())
            .then(result => {
                btnAcceptRequest.disabled = false;
                btnAcceptRequest.textContent = 'Accept Request';
                if (result.success) {
                    requestBanner.style.display = 'none';
                    activeConversationStatus = 'accepted';
                    switchTab('primary');
                    fetchConversations(true);
                } else {
                    alert(result.error || 'Failed to accept request.');
                }
            })
            .catch(err => {
                btnAcceptRequest.disabled = false;
                btnAcceptRequest.textContent = 'Accept Request';
                console.error('Accept request error:', err);
            });
        });

        btnDeclineRequest.addEventListener('click', () => {
            if (!currentConversationId) return;
            if (!confirm('Are you sure you want to decline this message request?')) return;

            btnDeclineRequest.disabled = true;
            btnDeclineRequest.textContent = 'Declining...';

            fetch('/api/chat/request_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    conversation_id: currentConversationId,
                    action: 'decline'
                })
            })
            .then(res => res.json())
            .then(result => {
                btnDeclineRequest.disabled = false;
                btnDeclineRequest.textContent = 'Decline';
                if (result.success) {
                    currentConversationId = null;
                    mainContainer.classList.remove('chat-active');
                    activeChat.style.display = 'none';
                    emptyState.style.display = 'flex';
                    fetchConversations(false);
                } else {
                    alert(result.error || 'Failed to decline request.');
                }
            })
            .catch(err => {
                btnDeclineRequest.disabled = false;
                btnDeclineRequest.textContent = 'Decline';
                console.error('Decline request error:', err);
            });
        });

        // ---------------------------------------------------------------------
        // 5. UNIVERSAL USER SEARCH MODAL & INSTANT CHAT INITIALIZATION
        // ---------------------------------------------------------------------
        function openModal() {
            searchModal.classList.add('open');
            userSearchInput.value = '';
            modalUserList.innerHTML = `
                <li style="padding: 32px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                    Type a username to find any buyer, customer, or seller across K71.
                </li>`;
            setTimeout(() => userSearchInput.focus(), 100);
        }

        function closeModal() {
            searchModal.classList.remove('open');
        }

        newChatBtn.addEventListener('click', openModal);
        emptySendBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);

        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) closeModal();
        });

        userSearchInput.addEventListener('input', (e) => {
            clearTimeout(searchDebounceTimer);
            const query = e.target.value.trim();

            if (query.length < 1) {
                modalUserList.innerHTML = `
                    <li style="padding: 32px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                        Type a username to find any buyer, customer, or seller across K71.
                    </li>`;
                return;
            }

            searchDebounceTimer = setTimeout(() => {
                fetch(`/api/chat/search_users.php?q=${encodeURIComponent(query)}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success && Array.isArray(result.data)) {
                        renderSearchResults(result.data);
                    }
                })
                .catch(err => console.error('Search error:', err));
            }, 250);
        });

        function renderSearchResults(users) {
            if (users.length === 0) {
                modalUserList.innerHTML = `
                    <li style="padding: 32px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                        No accounts found matching your query.
                    </li>`;
                return;
            }

            modalUserList.innerHTML = users.map(u => `
                <li class="glass-modal-user-item" data-user-id="${u.id}">
                    <div class="glass-modal-user-meta">
                        <img src="${escapeHtml(u.profile_pic)}" alt="${escapeHtml(u.name)}" class="glass-modal-user-avatar">
                        <div class="glass-modal-user-info">
                            <span class="glass-modal-username">&#64;${escapeHtml(u.username)}</span>
                            <span class="glass-modal-fullname">${escapeHtml(u.name)}</span>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="glass-modal-msg-btn" data-user-id="${u.id}">
                            Message
                        </button>
                    </div>
                </li>
            `).join('');

            modalUserList.querySelectorAll('.glass-modal-user-item, .glass-modal-msg-btn').forEach(el => {
                el.addEventListener('click', (e) => {
                    const targetId = parseInt(el.getAttribute('data-user-id'), 10);
                    if (!targetId) return;

                    fetch('/api/chat/search_users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            action: 'start_or_find',
                            target_user_id: targetId
                        })
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success && result.conversation_id) {
                            closeModal();
                            loadConversation(result.conversation_id, targetId);
                            fetchConversations(true);
                            setTimeout(() => messageInput.focus(), 200);
                        } else {
                            alert(result.error || 'Failed to start conversation.');
                        }
                    })
                    .catch(err => console.error('Start chat error:', err));
                });
            });
        }

        // ---------------------------------------------------------------------
        // 6. REAL-TIME POLLING SYNC (Fast 2.5s message, 5s inbox)
        // ---------------------------------------------------------------------
        function startPolling() {
            messagePollTimer = setInterval(() => {
                if (isPollingActive && currentConversationId) {
                    fetchMessages(false);
                }
            }, 2500);

            inboxPollTimer = setInterval(() => {
                if (isPollingActive) {
                    fetchConversations(true);
                }
            }, 5000);
        }

        document.addEventListener('visibilitychange', () => {
            isPollingActive = !document.hidden;
            if (isPollingActive) {
                fetchConversations(true);
                if (currentConversationId) fetchMessages(false);
            }
        });

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // ---------------------------------------------------------------------
        // 7. INITIAL BOOTSTRAP
        // ---------------------------------------------------------------------
        fetchConversations();
        startPolling();

        // 8. Auto-load conversation if target user or conversation passed in URL
        const urlParams = new URLSearchParams(window.location.search);
        const qConv = urlParams.get('conversation');
        const qUser = INITIAL_TARGET_USER_ID || urlParams.get('user') || urlParams.get('chat_with') || urlParams.get('uid') || urlParams.get('vendor');

        if (qConv) {
            loadConversation(parseInt(qConv, 10), null);
        } else if (qUser) {
            fetch('/api/chat/search_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: 'start_or_find',
                    target_user_id: parseInt(qUser, 10)
                })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success && result.conversation_id) {
                    loadConversation(result.conversation_id, parseInt(qUser, 10));
                }
            })
            .catch(err => console.error('Direct chat auto-start error:', err));
        }

        // ---------------------------------------------------------------------
        // 9. DYNAMIC MOBILE VIEWPORT & HEIGHT ADJUSTER
        // ---------------------------------------------------------------------
        function adjustMobileLayout() {
            const isMobile = window.innerWidth <= 768;
            const isChatActive = mainContainer && (mainContainer.classList.contains('chat-active') || document.body.classList.contains('chat-active'));
            const wrapper = document.getElementById('k71-chat-wrapper');
            if (!wrapper) return;

            if (isMobile) {
                const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;
                if (isChatActive) {
                    document.body.classList.add('chat-active');
                    wrapper.style.height = `${vh}px`;
                    wrapper.style.maxHeight = `${vh}px`;
                } else {
                    document.body.classList.remove('chat-active');
                    const nav = document.querySelector('header');
                    const navHeight = (nav && nav.offsetHeight > 0) ? nav.offsetHeight : 104;
                    const availableHeight = Math.max(280, Math.floor(vh - navHeight));
                    wrapper.style.height = `${availableHeight}px`;
                    wrapper.style.maxHeight = `${availableHeight}px`;
                }
            } else {
                document.body.classList.remove('chat-active');
                wrapper.style.height = '';
                wrapper.style.maxHeight = '';
            }
        }
        window.addEventListener('resize', adjustMobileLayout);
        window.addEventListener('orientationchange', () => setTimeout(adjustMobileLayout, 100));
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', () => {
                adjustMobileLayout();
                if (chatFeed) chatFeed.scrollTop = chatFeed.scrollHeight;
            });
        }
        adjustMobileLayout();

        // ---------------------------------------------------------------------
        // 10. GLASS TOAST NOTIFICATION SYSTEM
        // ---------------------------------------------------------------------
        function showGlassToast(message, type = 'success') {
            let toast = document.getElementById('glassNotificationToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'glassNotificationToast';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 24px;
                    left: 50%;
                    transform: translateX(-50%) translateY(100px);
                    background: rgba(15, 23, 42, 0.94);
                    backdrop-filter: blur(16px);
                    -webkit-backdrop-filter: blur(16px);
                    color: #ffffff;
                    padding: 10px 20px;
                    border-radius: 9999px;
                    font-size: 13px;
                    font-weight: 700;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.35);
                    border: 1px solid rgba(255,255,255,0.18);
                    z-index: 9999;
                    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                    pointer-events: none;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                `;
                document.body.appendChild(toast);
            }
            toast.innerHTML = (type === 'success' ? '<span style="color:#10B981;">✓</span> ' : '<span style="color:#EF4444;">⚠️</span> ') + escapeHtml(message);
            toast.style.transform = 'translateX(-50%) translateY(0)';
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.style.transform = 'translateX(-50%) translateY(100px)';
            }, 3200);
        }

        // ---------------------------------------------------------------------
        // 11. PROFILE PICTURE INSTANT UPLOAD
        // ---------------------------------------------------------------------
        const sidebarAvatarBtn = document.getElementById('sidebarAvatarBtn');
        const sidebarAvatarInput = document.getElementById('sidebarAvatarInput');
        const mySidebarAvatar = document.getElementById('mySidebarAvatar');

        if (sidebarAvatarBtn && sidebarAvatarInput) {
            sidebarAvatarBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebarAvatarInput.click();
            });

            sidebarAvatarInput.addEventListener('change', () => {
                const file = sidebarAvatarInput.files[0];
                if (!file) return;

                // Preview immediately
                const reader = new FileReader();
                reader.onload = (e) => {
                    myCurrentAvatarUrl = e.target.result;
                    if (mySidebarAvatar) mySidebarAvatar.src = e.target.result;
                    document.querySelectorAll('.my-user-avatar').forEach(img => img.src = e.target.result);
                };
                reader.readAsDataURL(file);

                const formData = new FormData();
                formData.append('avatar', file);

                showGlassToast('Uploading profile picture...');

                fetch('/api/user/avatar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.avatar_url) {
                        myCurrentAvatarUrl = data.avatar_url;
                        if (mySidebarAvatar) mySidebarAvatar.src = data.avatar_url;
                        document.querySelectorAll('.my-user-avatar').forEach(img => img.src = data.avatar_url);
                        showGlassToast(data.message || 'Profile picture updated successfully!');
                        // Refresh conversations so our avatar is updated if needed
                        fetchConversations();
                    } else {
                        showGlassToast(data.error || 'Failed to upload photo.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Avatar upload error:', err);
                    showGlassToast('Error uploading photo. Please try again.', 'error');
                });
            });
        }

        // ---------------------------------------------------------------------
        // 12. INSTAGRAM-STYLE HOLD/TAP VOICE MESSAGES & PERMISSION ONBOARDING
        // ---------------------------------------------------------------------
        const voiceBtn = document.getElementById('glassVoiceBtn');
        const voiceOverlay = document.getElementById('glassVoiceOverlay');
        const voiceTimer = document.getElementById('glassVoiceTimer');
        const voiceConfirmBtn = document.getElementById('glassVoiceConfirmBtn');
        const voiceCancelBtn = document.getElementById('glassVoiceCancelBtn');

        let mediaRecorder = null;
        let audioChunks = [];
        let recordingStream = null;
        let recordStartTime = 0;
        let recordTimerInterval = null;
        let isRecordingActive = false;
        let isRecordCancelled = false;

        function formatRecordDuration(seconds) {
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${s < 10 ? '0' : ''}${s}`;
        }

        async function startVoiceRecording(e) {
            if (e && e.cancelable) e.preventDefault();
            if (!currentConversationId) {
                showGlassToast('Please open or start a chat first before recording a voice message.', 'error');
                return;
            }
            if (isRecordingActive) return;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showGlassToast('Microphone access is not supported on this browser or requires HTTPS.', 'error');
                return;
            }

            try {
                recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });

                let mimeType = 'audio/webm;codecs=opus';
                if (!window.MediaRecorder || !MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = (window.MediaRecorder && MediaRecorder.isTypeSupported('audio/mp4')) 
                        ? 'audio/mp4' 
                        : ((window.MediaRecorder && MediaRecorder.isTypeSupported('audio/ogg')) ? 'audio/ogg' : '');
                }

                const options = mimeType ? { mimeType } : {};
                mediaRecorder = new MediaRecorder(recordingStream, options);
                audioChunks = [];
                isRecordingActive = true;
                isRecordCancelled = false;
                recordStartTime = Date.now();

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    clearInterval(recordTimerInterval);
                    if (voiceOverlay) voiceOverlay.style.display = 'none';
                    if (voiceBtn) voiceBtn.classList.remove('recording');

                    if (recordingStream) {
                        recordingStream.getTracks().forEach(t => t.stop());
                        recordingStream = null;
                    }

                    if (isRecordCancelled) {
                        audioChunks = [];
                        isRecordingActive = false;
                        return;
                    }

                    const elapsedSec = (Date.now() - recordStartTime) / 1000;
                    if (elapsedSec < 0.3) {
                        audioChunks = [];
                        isRecordingActive = false;
                        showGlassToast('Recording too short', 'error');
                        return;
                    }

                    const audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                    audioChunks = [];
                    isRecordingActive = false;
                    sendVoiceMessage(audioBlob, elapsedSec);
                };

                mediaRecorder.start(100);
                if (voiceOverlay) voiceOverlay.style.display = 'flex';
                if (voiceBtn) voiceBtn.classList.add('recording');
                if (voiceTimer) voiceTimer.textContent = '0:00';

                recordTimerInterval = setInterval(() => {
                    const elapsed = (Date.now() - recordStartTime) / 1000;
                    if (voiceTimer) voiceTimer.textContent = formatRecordDuration(elapsed);
                }, 250);

                if (navigator.vibrate) navigator.vibrate(40);

            } catch (err) {
                console.error('Microphone access error:', err);
                isRecordingActive = false;
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    showGlassToast('Microphone access blocked. Please tap the lock/settings icon in your browser URL bar and allow Microphone.', 'error');
                } else {
                    showGlassToast('Microphone error: ' + (err.message || 'Please check mic permissions.'), 'error');
                }
            }
        }

        function stopVoiceRecording(e) {
            if (e && e.cancelable && e.type !== 'touchend') e.preventDefault();
            if (!isRecordingActive || !mediaRecorder) return;
            if (mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
        }

        function cancelVoiceRecording(e) {
            if (e) {
                if (e.cancelable) e.preventDefault();
                e.stopPropagation();
            }
            if (!isRecordingActive) return;
            isRecordCancelled = true;
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            showGlassToast('Voice recording cancelled');
        }

        function sendVoiceMessage(blob, durationSec) {
            if (!currentConversationId) return;

            const formattedDuration = formatRecordDuration(durationSec);
            const optId = 'opt_voice_' + Date.now();
            const tempUrl = URL.createObjectURL(blob);

            const optimisticMsg = {
                id: optId,
                body: `🎤 Voice message (${formattedDuration})`,
                attachment_url: tempUrl,
                attachment_type: 'voice',
                sender_id: CURRENT_USER_ID,
                is_mine: true,
                is_optimistic: true,
                created_at: new Date().toISOString(),
                time_formatted: 'Sending voice...'
            };

            messagesCache.push(optimisticMsg);
            renderMessages(messagesCache, {
                username: headerUsername.textContent.replace('@', ''),
                name: headerStatus.textContent,
                profile_pic: headerAvatar.src
            });
            scrollFeedToBottom(true);

            const formData = new FormData();
            formData.append('conversation_id', currentConversationId);
            formData.append('duration', Math.round(durationSec));
            formData.append('voice', blob, `voice_${Date.now()}.webm`);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('/api/chat/upload_voice.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                const sentMsg = result.data || result.message;
                if (result.success && sentMsg) {
                    const idx = messagesCache.findIndex(m => m.id === optId);
                    if (idx !== -1) {
                        messagesCache[idx] = sentMsg;
                    }
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                    scrollFeedToBottom(true);
                    fetchConversations(true);
                } else {
                    showGlassToast(result.error || 'Failed to send voice message.', 'error');
                    messagesCache = messagesCache.filter(m => m.id !== optId);
                    renderMessages(messagesCache, {
                        username: headerUsername.textContent.replace('@', ''),
                        name: headerStatus.textContent,
                        profile_pic: headerAvatar.src
                    });
                }
            })
            .catch(err => {
                console.error('Voice send error:', err);
                showGlassToast('Network error while uploading voice note.', 'error');
            });
        }

        if (voiceBtn) {
            // Tap / Click to record (Exact Instagram Direct parity)
            voiceBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!isRecordingActive) {
                    startVoiceRecording(e);
                } else {
                    stopVoiceRecording(e);
                }
            });
        }

        if (voiceConfirmBtn) {
            voiceConfirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                stopVoiceRecording(e);
            });
        }

        if (voiceCancelBtn) {
            voiceCancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                cancelVoiceRecording(e);
            });
        }

        updateSendButtonState();

    })();
    </script>

    <!-- Mobile Bottom Navigation Panel -->
    <x-k71-bottom-nav />
</body>
</html>
