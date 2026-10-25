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
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
            background-color: transparent;
        }

        /* Master Glass Card Container */
        .glass-chat-frame {
            width: 100%;
            max-width: 1140px;
            height: calc(100vh - 105px);
            min-height: 600px;
            max-height: 860px;
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
            bottom: 1px;
            right: 1px;
            width: 12px;
            height: 12px;
            background: #0600c2;
            border: 2px solid #FFFFFF;
            border-radius: 50%;
            box-shadow: 0 0 8px #0600c2;
        }

        .dark .glass-unread-dot {
            background: #38BDF8;
            border-color: #0E172A;
            box-shadow: 0 0 8px #38BDF8;
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

        /* Sticky Bottom Composer */
        .glass-composer-bar {
            padding: 14px 20px;
            background: var(--glass-surface);
            border-top: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            flex-shrink: 0;
        }

        .glass-composer-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--glass-card);
            border: 1px solid var(--glass-border);
            border-radius: 26px;
            padding: 6px 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03), inset 0 1px 1px var(--glass-specular);
            transition: all 0.2s ease;
        }

        .glass-composer-pill:focus-within {
            border-color: #0600c2;
            box-shadow: 0 0 0 3px var(--glass-glow), inset 0 1px 1px var(--glass-specular);
        }

        .glass-composer-emoji-btn {
            background: none;
            border: none;
            padding: 2px;
            font-size: 19px;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.75;
            transition: opacity 0.15s ease;
        }
        .glass-composer-emoji-btn:hover {
            opacity: 1;
        }

        .glass-composer-textarea {
            flex: 1;
            border: none;
            outline: none;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--color-text-main);
            background: transparent;
            max-height: 100px;
            resize: none;
            padding: 6px 0;
            line-height: 1.4;
        }

        .glass-composer-textarea::placeholder {
            color: var(--color-text-muted);
        }

        .glass-composer-send-btn {
            background: none;
            border: none;
            color: #0600c2;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            padding: 4px 8px;
            opacity: 0.35;
            pointer-events: none;
            transition: all 0.15s ease;
        }

        .dark .glass-composer-send-btn {
            color: #38BDF8;
        }

        .glass-composer-send-btn.active {
            opacity: 1;
            pointer-events: auto;
        }

        .glass-composer-send-btn.active:hover {
            transform: scale(1.05);
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
            gap: 4px;
            padding: 10px 16px;
            background: var(--color-bubble-received);
            border: 1px solid var(--glass-border);
            border-radius: 20px 20px 20px 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        .typing-dot {
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            background: #8e8e93;
            display: inline-block;
            animation: igTypingBounce 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        .typing-dot:nth-child(3) { animation-delay: 0s; }
        @keyframes igTypingBounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
            40% { transform: translateY(-5px); opacity: 1; background: #0600c2; }
        }
        .dark @keyframes igTypingBounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
            40% { transform: translateY(-5px); opacity: 1; background: #38BDF8; }
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
                padding: 0;
            }
            .glass-chat-frame {
                border-radius: 0;
                border: none;
                height: calc(100vh - 64px);
                max-height: 100vh;
            }
            .glass-sidebar {
                width: 100%;
                min-width: 100%;
                border-right: none;
            }
            .glass-chat-pane {
                display: none;
                position: absolute;
                inset: 0;
                z-index: 20;
            }
            .glass-chat-frame.chat-active .glass-sidebar {
                display: none;
            }
            .glass-chat-frame.chat-active .glass-chat-pane {
                display: flex;
            }
            .glass-back-btn {
                display: flex !important;
            }
        }

        @media (min-width: 769px) {
            .glass-chat-frame.chat-active .glass-back-btn {
                display: flex;
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
                        <img src="{{ format_user_avatar($uAvatar, $uName) }}" 
                             alt="{{ $uHandle }}" 
                             class="glass-user-avatar">
                        <span class="glass-user-name">&#64;{{ $uHandle }}</span>
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
                            <button type="button" class="glass-composer-send-btn" id="glassSendBtn">
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
        let currentTab = 'primary';
        let currentConversationId = null;
        let activeRecipientId = null;
        let activeConversationStatus = 'accepted';
        let conversationsCache = [];
        let messagesCache = [];
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
            emptyState.style.display = 'none';
            activeChat.style.display = 'flex';

            renderConversations(filterInput.value.trim());

            chatFeed.innerHTML = `
                <div style="padding: 40px; text-align: center; color: var(--color-text-muted); font-size: 13px;">
                    Loading messages...
                </div>`;

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

                    if (JSON.stringify(newMessages) !== JSON.stringify(messagesCache)) {
                        messagesCache = newMessages;
                        renderMessages(newMessages, recipient);
                        if (scrollToBottom || wasNearBottom) {
                            scrollFeedToBottom();
                        }
                    }

                    // Instagram-Style 3-Dot Bouncing Typing Indicator
                    const isTyping = Boolean(result.is_typing);
                    const existingTyping = document.getElementById('glassTypingRow');
                    if (isTyping) {
                        if (!existingTyping) {
                            const typingEl = document.createElement('div');
                            typingEl.id = 'glassTypingRow';
                            typingEl.className = 'glass-typing-row';
                            typingEl.innerHTML = `
                                <img src="${escapeHtml(recipient.profile_pic)}" class="glass-msg-avatar" alt="">
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
            if (messages.length === 0) {
                chatFeed.innerHTML = `
                    <div style="padding: 60px 20px; text-align: center; color: var(--color-text-muted);">
                        <div style="width: 68px; height: 68px; margin: 0 auto 12px; border-radius: 50%; overflow: hidden; border: 1.5px solid var(--glass-border);">
                            <img src="${escapeHtml(recipient.profile_pic)}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h3 style="margin: 0; font-size: 16px; color: var(--color-text-main); font-weight: 800;">&#64;${escapeHtml(recipient.username)}</h3>
                        <p style="margin: 4px 0 16px; font-size: 12.5px;">${escapeHtml(recipient.name)} • Direct Messages</p>
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
                let attachmentHtml = '';
                if (msg.attachment_url) {
                    attachmentHtml = `
                        <div class="glass-msg-image-wrap" data-img-url="${escapeHtml(msg.attachment_url)}">
                            <img src="${escapeHtml(msg.attachment_url)}" alt="Photo" loading="lazy">
                        </div>
                    `;
                }

                const bodyHtml = (msg.body && msg.body !== '📷 Photo')
                    ? `<div class="glass-msg-bubble ${msg.is_optimistic ? 'optimistic' : ''}">${escapeHtml(msg.body).replace(/\n/g, '<br>')}</div>`
                    : (attachmentHtml ? '' : `<div class="glass-msg-bubble">📷 Photo</div>`);

                const unsendBtnHtml = (isSent && !msg.is_optimistic)
                    ? `<button type="button" class="glass-msg-unsend-btn" data-msg-id="${msg.id}" title="Unsend message">&times;</button>`
                    : '';

                html += `
                    <div class="glass-msg-row ${isSent ? 'sent' : 'received'}" data-msg-id="${msg.id}" data-is-mine="${isSent ? '1' : '0'}">
                        ${!isSent ? `<img src="${escapeHtml(recipient.profile_pic)}" alt="" class="glass-msg-avatar">` : ''}
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
            if (messageInput.value.trim().length > 0) {
                sendBtn.classList.add('active');
            } else {
                sendBtn.classList.remove('active');
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
            sendMessage();
        });

        backBtn.addEventListener('click', () => {
            mainContainer.classList.remove('chat-active');
            currentConversationId = null;
            activeRecipientId = null;
            if (window.innerWidth <= 768) {
                // On mobile, removing chat-active shows glass-sidebar and hides glass-chat-pane
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

    })();
    </script>
</body>
</html>
