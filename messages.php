<?php
/**
 * Instagram Direct (DM) Clone — Complete Frontend
 * File: messages.php
 * K71 Creative Agency & Marketplace
 */

// 1. Detect and use existing database connection & session bridge
$dbFile = file_exists(__DIR__ . '/includes/db.php') 
    ? __DIR__ . '/includes/db.php' 
    : __DIR__ . '/db.php';
require_once $dbFile;

// 2. Authentication check: Redirect guests to login
$currentUserId = get_current_user_id();
if (!$currentUserId) {
    header('Location: /login?redirect=' . urlencode('/messages.php'));
    exit;
}

// 3. Retrieve current user profile details
$currentUser = get_chat_user_info($pdo, $currentUserId);
if (!$currentUser) {
    header('Location: /login?redirect=' . urlencode('/messages.php'));
    exit;
}

$pageTitle = 'Direct Messages • Chats — K71';

// 4. Wrap with existing layout header
$headerFile = file_exists(__DIR__ . '/includes/header.php') 
    ? __DIR__ . '/includes/header.php' 
    : null;
if ($headerFile) {
    require_once $headerFile;
}
?>

<!-- =======================================================================
     INSTAGRAM DIRECT (DM) SCOPED STYLES
     Self-contained inside #instagram-direct wrapper to prevent style conflicts
     ======================================================================= -->
<style>
#instagram-direct {
    --ig-bg: #FAFAFA;
    --ig-card-bg: #FFFFFF;
    --ig-border: #DBDBDB;
    --ig-primary: #0095F6;
    --ig-primary-hover: #1877F2;
    --ig-text-primary: #262626;
    --ig-text-secondary: #8E8E8E;
    --ig-bubble-received: #EFEFEF;
    --ig-bubble-received-text: #262626;
    --ig-bubble-sent: #3797F0;
    --ig-bubble-sent-grad: linear-gradient(135deg, #405DE6, #833AB4);
    --ig-unread: #0095F6;
    
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: var(--ig-text-primary);
    background-color: var(--ig-bg);
    width: 100%;
    max-width: 1100px;
    margin: 16px auto;
    padding: 0 16px;
    box-sizing: border-box;
    flex: 1;
    display: flex;
    flex-direction: column;
}

#instagram-direct .ig-container {
    background: var(--ig-card-bg);
    border: 1px solid var(--ig-border);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    display: flex;
    height: calc(100vh - 130px);
    min-height: 580px;
    overflow: hidden;
    position: relative;
}

/* ---------------- LEFT SIDEBAR (Inbox) ---------------- */
#instagram-direct .ig-sidebar {
    width: 360px;
    min-width: 320px;
    border-right: 1px solid var(--ig-border);
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
    transition: transform 0.25s ease;
}

#instagram-direct .ig-sidebar-header {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid var(--ig-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#instagram-direct .ig-sidebar-user {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 700;
    color: var(--ig-text-primary);
    text-decoration: none;
    cursor: pointer;
}

#instagram-direct .ig-sidebar-user svg {
    width: 14px;
    height: 14px;
    fill: currentColor;
}

#instagram-direct .ig-new-chat-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ig-text-primary);
    transition: background 0.15s ease;
}

#instagram-direct .ig-new-chat-btn:hover {
    background: #F0F0F0;
}

#instagram-direct .ig-sidebar-search {
    padding: 10px 16px;
    border-bottom: 1px solid #F0F0F0;
}

#instagram-direct .ig-search-box {
    position: relative;
    display: flex;
    align-items: center;
}

#instagram-direct .ig-search-box svg {
    position: absolute;
    left: 12px;
    width: 14px;
    height: 14px;
    color: var(--ig-text-secondary);
    pointer-events: none;
}

#instagram-direct .ig-search-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    border-radius: 8px;
    border: 1px solid var(--ig-border);
    background: #FAFAFA;
    font-size: 13px;
    color: var(--ig-text-primary);
    outline: none;
    transition: all 0.15s ease;
}

#instagram-direct .ig-search-input:focus {
    background: #FFFFFF;
    border-color: #A8A8A8;
}

#instagram-direct .ig-conversations-list {
    flex: 1;
    overflow-y: auto;
    list-style: none;
    margin: 0;
    padding: 0;
}

#instagram-direct .ig-conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    cursor: pointer;
    transition: background 0.15s ease;
    text-decoration: none;
    color: inherit;
    position: relative;
}

#instagram-direct .ig-conv-item:hover {
    background: #FAFAFA;
}

#instagram-direct .ig-conv-item.active {
    background: #EFEFEF;
}

#instagram-direct .ig-avatar-wrap {
    position: relative;
    width: 52px;
    height: 52px;
    flex-shrink: 0;
}

#instagram-direct .ig-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

#instagram-direct .ig-unread-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 11px;
    height: 11px;
    background: var(--ig-unread);
    border: 2px solid #FFFFFF;
    border-radius: 50%;
}

#instagram-direct .ig-conv-details {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

#instagram-direct .ig-conv-username {
    font-size: 14px;
    font-weight: 600;
    color: var(--ig-text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#instagram-direct .ig-conv-last-msg {
    font-size: 12.5px;
    color: var(--ig-text-secondary);
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#instagram-direct .ig-conv-last-msg.unread-bold {
    font-weight: 700;
    color: var(--ig-text-primary);
}

#instagram-direct .ig-conv-last-msg .text-span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#instagram-direct .ig-conv-last-msg .bullet {
    font-size: 10px;
}

/* ---------------- RIGHT CHAT PANE ---------------- */
#instagram-direct .ig-chat-pane {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
    position: relative;
}

/* Empty State */
#instagram-direct .ig-empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
}

#instagram-direct .ig-empty-circle {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 2px solid var(--ig-text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

#instagram-direct .ig-empty-circle svg {
    width: 48px;
    height: 48px;
    stroke: var(--ig-text-primary);
}

#instagram-direct .ig-empty-title {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

#instagram-direct .ig-empty-subtitle {
    font-size: 13.5px;
    color: var(--ig-text-secondary);
    margin: 0 0 18px 0;
}

#instagram-direct .ig-btn-primary {
    background: var(--ig-primary);
    color: #FFFFFF;
    border: none;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

#instagram-direct .ig-btn-primary:hover {
    background: var(--ig-primary-hover);
}

/* Active Chat Header */
#instagram-direct .ig-chat-header {
    height: 60px;
    padding: 0 18px;
    border-bottom: 1px solid var(--ig-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FFFFFF;
    z-index: 10;
}

#instagram-direct .ig-chat-header-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

#instagram-direct .ig-back-btn {
    display: none;
    background: none;
    border: none;
    padding: 6px;
    cursor: pointer;
    color: var(--ig-text-primary);
    border-radius: 50%;
}

#instagram-direct .ig-chat-header-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

#instagram-direct .ig-chat-header-info {
    display: flex;
    flex-direction: column;
}

#instagram-direct .ig-chat-header-username {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--ig-text-primary);
    text-decoration: none;
}

#instagram-direct .ig-chat-header-status {
    font-size: 11.5px;
    color: var(--ig-text-secondary);
}

#instagram-direct .ig-chat-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

#instagram-direct .ig-header-icon-btn {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    border-radius: 50%;
    color: var(--ig-text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

#instagram-direct .ig-header-icon-btn:hover {
    background: #F0F0F0;
}

/* Message Feed */
#instagram-direct .ig-chat-feed {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: #FFFFFF;
}

#instagram-direct .ig-date-divider {
    text-align: center;
    margin: 12px 0;
    position: relative;
}

#instagram-direct .ig-date-divider span {
    font-size: 11px;
    font-weight: 600;
    color: var(--ig-text-secondary);
    background: #FFFFFF;
    padding: 0 12px;
}

#instagram-direct .ig-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 72%;
}

#instagram-direct .ig-msg-row.sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

#instagram-direct .ig-msg-row.received {
    align-self: flex-start;
}

#instagram-direct .ig-msg-sender-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    margin-bottom: 2px;
}

#instagram-direct .ig-bubble {
    padding: 9px 15px;
    border-radius: 20px;
    font-size: 13.5px;
    line-height: 1.45;
    word-break: break-word;
    position: relative;
    max-width: 100%;
}

#instagram-direct .ig-msg-row.sent .ig-bubble {
    background: var(--ig-bubble-sent-grad);
    color: #FFFFFF;
    border-bottom-right-radius: 4px;
}

#instagram-direct .ig-msg-row.received .ig-bubble {
    background: var(--ig-bubble-received);
    color: var(--ig-bubble-received-text);
    border-bottom-left-radius: 4px;
}

#instagram-direct .ig-bubble.optimistic {
    opacity: 0.65;
}

#instagram-direct .ig-msg-meta {
    font-size: 10px;
    color: var(--ig-text-secondary);
    margin-top: 2px;
    padding: 0 4px;
    display: none;
}

#instagram-direct .ig-msg-row:hover .ig-msg-meta {
    display: block;
}

/* Chat Input Bar */
#instagram-direct .ig-chat-input-bar {
    padding: 12px 18px;
    background: #FFFFFF;
    border-top: 1px solid var(--ig-border);
}

#instagram-direct .ig-input-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--ig-border);
    border-radius: 24px;
    padding: 6px 14px;
    background: #FFFFFF;
    transition: border-color 0.15s ease;
}

#instagram-direct .ig-input-pill:focus-within {
    border-color: #A8A8A8;
}

#instagram-direct .ig-emoji-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ig-text-primary);
}

#instagram-direct .ig-message-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 13.5px;
    font-family: inherit;
    color: var(--ig-text-primary);
    background: transparent;
    max-height: 100px;
    resize: none;
    padding: 4px 0;
}

#instagram-direct .ig-send-btn {
    background: none;
    border: none;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--ig-primary);
    cursor: pointer;
    padding: 4px 8px;
    opacity: 0.35;
    pointer-events: none;
    transition: opacity 0.15s ease;
}

#instagram-direct .ig-send-btn.active {
    opacity: 1;
    pointer-events: auto;
}

#instagram-direct .ig-send-btn.active:hover {
    color: var(--ig-primary-hover);
}

/* Quick Emoji Bar */
#instagram-direct .ig-quick-emojis {
    display: none;
    gap: 8px;
    padding: 6px 14px;
    border-bottom: 1px solid #F0F0F0;
    background: #FAFAFA;
}

#instagram-direct .ig-quick-emojis.open {
    display: flex;
}

#instagram-direct .ig-emoji-chip {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    padding: 2px 6px;
    border-radius: 6px;
    transition: background 0.1s ease;
}

#instagram-direct .ig-emoji-chip:hover {
    background: #EFEFEF;
}

/* ---------------- SEARCH & FOLLOW MODAL ---------------- */
#instagram-direct .ig-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

#instagram-direct .ig-modal-overlay.open {
    display: flex;
}

#instagram-direct .ig-modal-card {
    background: #FFFFFF;
    border-radius: 16px;
    width: 100%;
    max-width: 440px;
    height: 500px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
    animation: igModalFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes igModalFade {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}

#instagram-direct .ig-modal-header {
    height: 48px;
    padding: 0 16px;
    border-bottom: 1px solid var(--ig-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#instagram-direct .ig-modal-title {
    font-size: 15px;
    font-weight: 700;
    text-align: center;
    flex: 1;
}

#instagram-direct .ig-modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    font-size: 18px;
    color: var(--ig-text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

#instagram-direct .ig-modal-search {
    padding: 10px 16px;
    border-bottom: 1px solid #F0F0F0;
    display: flex;
    align-items: center;
    gap: 8px;
}

#instagram-direct .ig-modal-search-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--ig-text-primary);
}

#instagram-direct .ig-modal-search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    font-family: inherit;
    color: var(--ig-text-primary);
}

#instagram-direct .ig-modal-user-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
    list-style: none;
    margin: 0;
}

#instagram-direct .ig-modal-user-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    transition: background 0.15s ease;
}

#instagram-direct .ig-modal-user-item:hover {
    background: #FAFAFA;
}

#instagram-direct .ig-modal-user-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}

#instagram-direct .ig-modal-user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

#instagram-direct .ig-modal-user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

#instagram-direct .ig-modal-username {
    font-size: 13.5px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#instagram-direct .ig-modal-fullname {
    font-size: 12.5px;
    color: var(--ig-text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Modal Action Buttons */
#instagram-direct .ig-action-btn-follow {
    background: var(--ig-primary);
    color: #FFFFFF;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
}

#instagram-direct .ig-action-btn-follow:hover {
    background: var(--ig-primary-hover);
}

#instagram-direct .ig-action-btn-requested {
    background: #EFEFEF;
    color: var(--ig-text-primary);
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
}

#instagram-direct .ig-action-btn-requested:hover {
    background: #DBDBDB;
}

#instagram-direct .ig-action-btn-message {
    background: transparent;
    color: var(--ig-text-primary);
    border: 1px solid var(--ig-border);
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
}

#instagram-direct .ig-action-btn-message:hover {
    background: #FAFAFA;
    border-color: #A8A8A8;
}

#instagram-direct .ig-action-group {
    display: flex;
    gap: 6px;
}

/* ---------------- RESPONSIVE BREAKPOINT ---------------- */
@media (max-width: 768px) {
    #instagram-direct {
        margin: 0;
        padding: 0;
        height: calc(100vh - 60px);
    }
    #instagram-direct .ig-container {
        border: none;
        border-radius: 0;
        height: 100%;
        box-shadow: none;
    }
    #instagram-direct .ig-sidebar {
        width: 100%;
        min-width: 100%;
        border-right: none;
    }
    #instagram-direct .ig-chat-pane {
        display: none;
        position: absolute;
        inset: 0;
        z-index: 20;
    }
    #instagram-direct .ig-container.chat-active .ig-sidebar {
        display: none;
    }
    #instagram-direct .ig-container.chat-active .ig-chat-pane {
        display: flex;
    }
    #instagram-direct .ig-back-btn {
        display: flex;
    }
}
</style>

<div id="instagram-direct">
    <div class="ig-container" id="igMainContainer">
        
        <!-- ==============================================================
             LEFT SIDEBAR: CONVERSATION LIST
             ============================================================== -->
        <aside class="ig-sidebar" id="igSidebar">
            <!-- Header: Username + New Chat Pencil Icon -->
            <div class="ig-sidebar-header">
                <div class="ig-sidebar-user">
                    <span>@<?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?></span>
                    <svg viewBox="0 0 24 24"><path d="M12 15l-5-5h10z"/></svg>
                </div>
                <button type="button" class="ig-new-chat-btn" id="igNewChatBtn" title="New Message">
                    <!-- Instagram Pencil / Edit Icon -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
            </div>

            <!-- In-Chat Search Bar -->
            <div class="ig-sidebar-search">
                <div class="ig-search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="igFilterConvInput" class="ig-search-input" placeholder="Search conversations...">
                </div>
            </div>

            <!-- Scrollable Conversations Stream -->
            <ul class="ig-conversations-list" id="igConversationsList">
                <li style="padding: 32px 20px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                    Loading your direct messages...
                </li>
            </ul>
        </aside>

        <!-- ==============================================================
             RIGHT CHAT PANE: EMPTY STATE & ACTIVE CONVERSATION
             ============================================================== -->
        <main class="ig-chat-pane" id="igChatPane">
            
            <!-- 1. EMPTY STATE (When no conversation is selected) -->
            <div class="ig-empty-state" id="igEmptyState">
                <div class="ig-empty-circle">
                    <!-- Instagram Paper Plane Icon -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </div>
                <h2 class="ig-empty-title">Your Messages</h2>
                <p class="ig-empty-subtitle">Send private photos and messages to a friend or creator.</p>
                <button type="button" class="ig-btn-primary" id="igEmptySendBtn">
                    <span>Send Message</span>
                </button>
            </div>

            <!-- 2. ACTIVE CHAT STATE -->
            <div id="igActiveChat" style="display: none; height: 100%; flex-direction: column;">
                <!-- Active Chat Header -->
                <div class="ig-chat-header">
                    <div class="ig-chat-header-user">
                        <!-- Mobile Back Arrow Button -->
                        <button type="button" class="ig-back-btn" id="igBackToInboxBtn" title="Back to Inbox">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        
                        <img src="" alt="" class="ig-chat-header-avatar" id="igHeaderAvatar">
                        <div class="ig-chat-header-info">
                            <span class="ig-chat-header-username" id="igHeaderUsername"></span>
                            <span class="ig-chat-header-status" id="igHeaderStatus">Active now</span>
                        </div>
                    </div>

                    <div class="ig-chat-header-actions">
                        <button type="button" class="ig-header-icon-btn" title="Conversation Info">
                            <!-- Info Icon -->
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Messages Feed -->
                <div class="ig-chat-feed" id="igChatFeed">
                    <!-- Dynamic Messages Appended Here -->
                </div>

                <!-- Quick Emojis Drawer -->
                <div class="ig-quick-emojis" id="igQuickEmojis">
                    <button type="button" class="ig-emoji-chip" data-emoji="❤️">❤️</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="😂">😂</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="🔥">🔥</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="👍">👍</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="😍">😍</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="🙌">🙌</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="🎉">🎉</button>
                    <button type="button" class="ig-emoji-chip" data-emoji="✨">✨</button>
                </div>

                <!-- Sticky Bottom Input Bar -->
                <div class="ig-chat-input-bar">
                    <div class="ig-input-pill">
                        <button type="button" class="ig-emoji-btn" id="igToggleEmojiBtn" title="Insert Emoji">
                            😊
                        </button>
                        <textarea id="igMessageInput" 
                                  class="ig-message-input" 
                                  rows="1" 
                                  placeholder="Message..." 
                                  autocomplete="off"></textarea>
                        <button type="button" class="ig-send-btn" id="igSendBtn">
                            Send
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ==============================================================
         SEARCH & FOLLOW MODAL (Exact Instagram Behavior)
         ============================================================== -->
    <div class="ig-modal-overlay" id="igSearchModal">
        <div class="ig-modal-card">
            <div class="ig-modal-header">
                <div style="width: 24px;"></div>
                <div class="ig-modal-title">New Message</div>
                <button type="button" class="ig-modal-close" id="igCloseModalBtn">&times;</button>
            </div>
            
            <div class="ig-modal-search">
                <span class="ig-modal-search-label">To:</span>
                <input type="text" id="igUserSearchInput" class="ig-modal-search-input" placeholder="Search people..." autocomplete="off">
            </div>

            <ul class="ig-modal-user-list" id="igModalUserList">
                <li style="padding: 30px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                    Search for users on K71 to follow and start direct messaging.
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- =======================================================================
     REAL-TIME CLIENT ENGINE (Embedded Vanilla JavaScript)
     ======================================================================= -->
<script>
(function() {
    'use strict';

    // State Variables
    let currentConversationId = null;
    let activeRecipientId = null;
    let conversationsCache = [];
    let messagesCache = [];
    let isPollingActive = true;
    let searchDebounceTimer = null;
    let messagePollTimer = null;
    let inboxPollTimer = null;

    // DOM Elements
    const mainContainer = document.getElementById('igMainContainer');
    const conversationsList = document.getElementById('igConversationsList');
    const filterConvInput = document.getElementById('igFilterConvInput');
    const emptyState = document.getElementById('igEmptyState');
    const activeChat = document.getElementById('igActiveChat');
    const chatFeed = document.getElementById('igChatFeed');
    const headerAvatar = document.getElementById('igHeaderAvatar');
    const headerUsername = document.getElementById('igHeaderUsername');
    const headerStatus = document.getElementById('igHeaderStatus');
    const messageInput = document.getElementById('igMessageInput');
    const sendBtn = document.getElementById('igSendBtn');
    const backToInboxBtn = document.getElementById('igBackToInboxBtn');
    const newChatBtn = document.getElementById('igNewChatBtn');
    const emptySendBtn = document.getElementById('igEmptySendBtn');
    const searchModal = document.getElementById('igSearchModal');
    const closeModalBtn = document.getElementById('igCloseModalBtn');
    const userSearchInput = document.getElementById('igUserSearchInput');
    const modalUserList = document.getElementById('igModalUserList');
    const toggleEmojiBtn = document.getElementById('igToggleEmojiBtn');
    const quickEmojis = document.getElementById('igQuickEmojis');

    // -------------------------------------------------------------------------
    // 1. INBOX CONVERSATIONS ENGINE
    // -------------------------------------------------------------------------
    function fetchConversations(silent = false) {
        fetch('/api/chat/get_conversations.php', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(result => {
            if (result.success && Array.isArray(result.data)) {
                conversationsCache = result.data;
                renderConversations(filterConvInput.value.trim());
            } else if (!silent) {
                conversationsList.innerHTML = `
                    <li style="padding: 30px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                        ${result.error || 'No conversations yet.'}
                    </li>`;
            }
        })
        .catch(err => {
            if (!silent) {
                console.error('Error fetching conversations:', err);
            }
        });
    }

    function renderConversations(filterKeyword = '') {
        const query = filterKeyword.toLowerCase();
        const filtered = conversationsCache.filter(c => {
            return c.recipient.username.toLowerCase().includes(query) ||
                   c.recipient.name.toLowerCase().includes(query) ||
                   c.last_message.body.toLowerCase().includes(query);
        });

        if (filtered.length === 0) {
            conversationsList.innerHTML = `
                <li style="padding: 30px 20px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                    ${query ? 'No matching conversations.' : 'No messages yet.<br>Click the pencil icon above to start a chat!'}
                </li>`;
            return;
        }

        conversationsList.innerHTML = filtered.map(c => {
            const isActive = (c.id === currentConversationId);
            const isUnread = (c.unread_count > 0);
            const prefix = c.last_message.is_sent_by_me ? 'You: ' : '';
            return `
                <li class="ig-conv-item ${isActive ? 'active' : ''}" data-conv-id="${c.id}" data-recipient-id="${c.recipient.id}">
                    <div class="ig-avatar-wrap">
                        <img src="${escapeHtml(c.recipient.profile_pic)}" alt="${escapeHtml(c.recipient.name)}" class="ig-avatar-img">
                        ${isUnread ? '<div class="ig-unread-badge"></div>' : ''}
                    </div>
                    <div class="ig-conv-details">
                        <div class="ig-conv-username">@${escapeHtml(c.recipient.username)}</div>
                        <div class="ig-conv-last-msg ${isUnread ? 'unread-bold' : ''}">
                            <span class="text-span">${escapeHtml(prefix + c.last_message.body)}</span>
                            <span class="bullet">•</span>
                            <span>${escapeHtml(c.last_message.time_formatted)}</span>
                        </div>
                    </div>
                </li>
            `;
        }).join('');

        // Attach Click Handlers
        conversationsList.querySelectorAll('.ig-conv-item').forEach(item => {
            item.addEventListener('click', () => {
                const convId = parseInt(item.getAttribute('data-conv-id'), 10);
                const recId = parseInt(item.getAttribute('data-recipient-id'), 10);
                loadConversation(convId, recId);
            });
        });
    }

    filterConvInput.addEventListener('input', (e) => {
        renderConversations(e.target.value.trim());
    });

    // -------------------------------------------------------------------------
    // 2. ACTIVE CONVERSATION & MESSAGES ENGINE
    // -------------------------------------------------------------------------
    function loadConversation(convId, recipientId) {
        currentConversationId = convId;
        activeRecipientId = recipientId;

        // Toggle UI Panes
        mainContainer.classList.add('chat-active');
        emptyState.style.display = 'none';
        activeChat.style.display = 'flex';

        // Highlight selected thread in sidebar
        renderConversations(filterConvInput.value.trim());

        chatFeed.innerHTML = `
            <div style="padding: 40px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
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
            if (result.success && result.data) {
                const data = result.data;
                const recipient = data.recipient;

                // Update Header
                headerAvatar.src = recipient.profile_pic;
                headerAvatar.alt = recipient.name;
                headerUsername.textContent = '@' + recipient.username;
                headerStatus.textContent = recipient.name;

                // Check if new messages arrived
                const newMessages = data.messages || [];
                const wasNearBottom = isUserNearBottom();

                if (JSON.stringify(newMessages) !== JSON.stringify(messagesCache)) {
                    messagesCache = newMessages;
                    renderMessages(newMessages, recipient);
                    if (scrollToBottom || wasNearBottom) {
                        scrollFeedToBottom();
                    }
                }
            } else if (result.error) {
                chatFeed.innerHTML = `
                    <div style="padding: 40px; text-align: center; color: #ED4956; font-size: 13.5px; font-weight: 600;">
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
                <div style="padding: 60px 20px; text-align: center; color: var(--ig-text-secondary);">
                    <div style="width: 64px; height: 64px; margin: 0 auto 12px; border-radius: 50%; overflow: hidden; border: 1px solid var(--ig-border);">
                        <img src="${escapeHtml(recipient.profile_pic)}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h3 style="margin: 0; font-size: 16px; color: var(--ig-text-primary); font-weight: 700;">@${escapeHtml(recipient.username)}</h3>
                    <p style="margin: 4px 0 16px; font-size: 12.5px;">${escapeHtml(recipient.name)} • Instagram Direct</p>
                    <p style="font-size: 13px; color: var(--ig-text-secondary);">Say hello to start the conversation!</p>
                </div>`;
            return;
        }

        let lastDate = '';
        let html = '';

        messages.forEach(msg => {
            // Render Date Divider if date changed
            if (msg.date_header && msg.date_header !== lastDate) {
                lastDate = msg.date_header;
                html += `<div class="ig-date-divider"><span>${escapeHtml(lastDate)}</span></div>`;
            }

            const isSent = msg.is_mine;
            html += `
                <div class="ig-msg-row ${isSent ? 'sent' : 'received'}" data-msg-id="${msg.id}">
                    ${!isSent ? `<img src="${escapeHtml(recipient.profile_pic)}" alt="" class="ig-msg-sender-avatar">` : ''}
                    <div>
                        <div class="ig-bubble ${msg.is_optimistic ? 'optimistic' : ''}">
                            ${escapeHtml(msg.body).replace(/\n/g, '<br>')}
                        </div>
                        <div class="ig-msg-meta">${escapeHtml(msg.time_formatted || '')}</div>
                    </div>
                </div>
            `;
        });

        chatFeed.innerHTML = html;
    }

    function isUserNearBottom() {
        const threshold = 120;
        return (chatFeed.scrollHeight - chatFeed.scrollTop - chatFeed.clientHeight) <= threshold;
    }

    function scrollFeedToBottom(smooth = false) {
        if (smooth) {
            chatFeed.scrollTo({ top: chatFeed.scrollHeight, behavior: 'smooth' });
        } else {
            chatFeed.scrollTop = chatFeed.scrollHeight;
        }
    }

    // -------------------------------------------------------------------------
    // 3. SEND MESSAGE & OPTIMISTIC UI
    // -------------------------------------------------------------------------
    function sendMessage() {
        const text = messageInput.value.trim();
        if (!text || !currentConversationId) return;

        // Clear input and reset button state
        messageInput.value = '';
        updateSendButtonState();
        messageInput.style.height = 'auto';

        // Optimistic UI Append
        const optimisticMsg = {
            id: 'opt_' + Date.now(),
            body: text,
            sender_id: window.CURRENT_USER_ID || 0,
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

        // API Request
        fetch('/api/chat/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                message: text
            })
        })
        .then(res => res.json())
        .then(result => {
            if (result.success && result.message) {
                // Replace optimistic message with verified message
                const index = messagesCache.findIndex(m => m.id === optimisticMsg.id);
                if (index !== -1) {
                    messagesCache[index] = result.message;
                }
                renderMessages(messagesCache, {
                    username: headerUsername.textContent.replace('@', ''),
                    name: headerStatus.textContent,
                    profile_pic: headerAvatar.src
                });
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

    // Input events
    messageInput.addEventListener('input', () => {
        updateSendButtonState();
        // Auto-expand textarea
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 100) + 'px';
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

    // Mobile Back to Inbox Button
    backToInboxBtn.addEventListener('click', () => {
        mainContainer.classList.remove('chat-active');
    });

    // Emoji Bar Toggle
    toggleEmojiBtn.addEventListener('click', () => {
        quickEmojis.classList.toggle('open');
    });

    quickEmojis.querySelectorAll('.ig-emoji-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const emoji = chip.getAttribute('data-emoji');
            messageInput.value += emoji;
            updateSendButtonState();
            messageInput.focus();
        });
    });

    // -------------------------------------------------------------------------
    // 4. USER SEARCH & FOLLOW MODAL
    // -------------------------------------------------------------------------
    function openModal() {
        searchModal.classList.add('open');
        userSearchInput.value = '';
        modalUserList.innerHTML = `
            <li style="padding: 30px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                Type a username to find people and start messaging.
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
                <li style="padding: 30px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                    Type a username to find people and start messaging.
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
            .catch(err => {
                console.error('Search error:', err);
            });
        }, 300);
    });

    function renderSearchResults(users) {
        if (users.length === 0) {
            modalUserList.innerHTML = `
                <li style="padding: 30px; text-align: center; color: var(--ig-text-secondary); font-size: 13px;">
                    No accounts found matching your query.
                </li>`;
            return;
        }

        modalUserList.innerHTML = users.map(u => {
            let actionBtnHtml = '';

            if (u.follow_status === 'none') {
                actionBtnHtml = `<button type="button" class="ig-action-btn-follow" data-user-id="${u.id}" data-action="follow">Follow</button>`;
            } else if (u.follow_status === 'pending_outgoing') {
                actionBtnHtml = `<button type="button" class="ig-action-btn-requested" data-user-id="${u.id}" data-action="cancel">Requested</button>`;
            } else if (u.follow_status === 'pending_incoming') {
                actionBtnHtml = `
                    <div class="ig-action-group">
                        <button type="button" class="ig-action-btn-follow" data-user-id="${u.id}" data-action="accept">Confirm</button>
                        <button type="button" class="ig-action-btn-requested" data-user-id="${u.id}" data-action="decline">Delete</button>
                    </div>`;
            } else if (u.follow_status === 'accepted') {
                actionBtnHtml = `<button type="button" class="ig-action-btn-message" data-user-id="${u.id}" data-action="open_chat">Message</button>`;
            }

            return `
                <li class="ig-modal-user-item" data-user-id="${u.id}">
                    <div class="ig-modal-user-meta">
                        <img src="${escapeHtml(u.profile_pic)}" alt="${escapeHtml(u.name)}" class="ig-modal-user-avatar">
                        <div class="ig-modal-user-info">
                            <span class="ig-modal-username">@${escapeHtml(u.username)}</span>
                            <span class="ig-modal-fullname">${escapeHtml(u.name)}</span>
                        </div>
                    </div>
                    <div class="ig-modal-user-action">
                        ${actionBtnHtml}
                    </div>
                </li>
            `;
        }).join('');

        // Attach action handlers to buttons inside modal
        modalUserList.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const targetId = parseInt(btn.getAttribute('data-user-id'), 10);
                const action = btn.getAttribute('data-action');

                if (action === 'open_chat') {
                    // Open existing conversation
                    closeModal();
                    // Find if conversation exists in cache
                    const existing = conversationsCache.find(c => c.recipient.id === targetId);
                    if (existing) {
                        loadConversation(existing.id, targetId);
                    } else {
                        // Trigger accept endpoint to guarantee conversation row creation
                        triggerFollowAction(targetId, 'accept', btn);
                    }
                } else {
                    triggerFollowAction(targetId, action, btn);
                }
            });
        });
    }

    function triggerFollowAction(targetId, action, buttonEl) {
        const originalText = buttonEl.textContent;
        buttonEl.disabled = true;
        buttonEl.style.opacity = '0.6';

        fetch('/api/chat/follow_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                target_id: targetId,
                action: action
            })
        })
        .then(res => res.json())
        .then(result => {
            buttonEl.disabled = false;
            buttonEl.style.opacity = '1';

            if (result.success) {
                if (action === 'accept' && result.conversation_id) {
                    closeModal();
                    fetchConversations();
                    loadConversation(result.conversation_id, targetId);
                } else {
                    // Refresh search list to update button state
                    const query = userSearchInput.value.trim();
                    if (query) {
                        userSearchInput.dispatchEvent(new Event('input'));
                    }
                    fetchConversations(true);
                }
            } else {
                alert(result.error || 'Failed to perform follow action.');
                buttonEl.textContent = originalText;
            }
        })
        .catch(err => {
            console.error('Follow action error:', err);
            buttonEl.disabled = false;
            buttonEl.style.opacity = '1';
            buttonEl.textContent = originalText;
        });
    }

    // -------------------------------------------------------------------------
    // 5. REAL-TIME POLLING ENGINES
    // -------------------------------------------------------------------------
    function startPolling() {
        // Message polling every 2.5 seconds
        messagePollTimer = setInterval(() => {
            if (isPollingActive && currentConversationId) {
                fetchMessages(false);
            }
        }, 2500);

        // Inbox conversations polling every 6 seconds
        inboxPollTimer = setInterval(() => {
            if (isPollingActive) {
                fetchConversations(true);
            }
        }, 6000);
    }

    // Handle tab focus / blur to save resources
    document.addEventListener('visibilitychange', () => {
        isPollingActive = !document.hidden;
        if (isPollingActive) {
            fetchConversations(true);
            if (currentConversationId) fetchMessages(false);
        }
    });

    // Helper: HTML escaping for XSS prevention
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

    // -------------------------------------------------------------------------
    // 6. INITIALIZATION
    // -------------------------------------------------------------------------
    fetchConversations();
    startPolling();

})();
</script>

<?php
// Wrap with existing layout footer
$footerFile = file_exists(__DIR__ . '/includes/footer.php') 
    ? __DIR__ . '/includes/footer.php' 
    : null;
if ($footerFile) {
    require_once $footerFile;
}
?>
