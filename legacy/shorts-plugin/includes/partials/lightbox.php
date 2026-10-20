<?php defined( "ABSPATH" ) || exit; ?>
<div class="zsu-lightbox" id="zsuLightbox" role="dialog" aria-modal="true" aria-label="Video player" style="display:none;">

    <!-- Close button -->
    <button class="zsu-lb-close" id="zsuLbClose" aria-label="Close">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>

    <!-- Navigation arrows -->
    <button class="zsu-lb-nav zsu-lb-prev" id="zsuLbPrev" aria-label="Previous short">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
    </button>
    <button class="zsu-lb-nav zsu-lb-next" id="zsuLbNext" aria-label="Next short">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
    </button>

    <!-- Video stack (vertical snap scroll) -->
    <div class="zsu-lb-stack" id="zsuLbStack" role="feed" aria-label="Video feed">
        <!-- Cards injected by JS -->
    </div>

    <!-- Side panel -->
    <aside class="zsu-lb-sidebar" id="zsuLbSidebar" aria-label="Video details">

        <!-- Vendor info -->
        <div class="zsu-lb-vendor" id="zsuLbVendor">
            <img class="zsu-lb-avatar" id="zsuLbAvatar" src="" alt="" loading="lazy">
            <div class="zsu-lb-vendor-info">
                <span class="zsu-lb-vendor-name" id="zsuLbVendorName"></span>
                <span class="zsu-lb-vendor-store" id="zsuLbVendorStore"></span>
            </div>
            <?php if ( zsu_opt('zsu_show_follow_btn', 1) ) : ?>
            <button class="zsu-follow-btn" id="zsuLbFollowBtn" data-vendor-id="" aria-pressed="false">
                <span class="zsu-follow-label">Follow</span>
            </button>
            <?php endif; ?>
        </div>

        <!-- Title + description -->
        <div class="zsu-lb-text">
            <h3 class="zsu-lb-title" id="zsuLbTitle"></h3>
            <p  class="zsu-lb-desc"  id="zsuLbDesc"></p>
        </div>

        <!-- Category chips -->
        <div class="zsu-lb-cats" id="zsuLbCats"></div>

        <!-- Stats row -->
        <div class="zsu-lb-stats" id="zsuLbStats">
            <div class="zsu-lb-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span id="zsuLbViews">0</span>
            </div>
            <div class="zsu-lb-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span id="zsuLbLikes">0</span>
            </div>
            <div class="zsu-lb-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span id="zsuLbShares">0</span>
            </div>
            <?php if ( zsu_opt('zsu_enable_ratings', 1) ) : ?>
            <div class="zsu-lb-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span id="zsuLbRating">—</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action buttons -->
        <div class="zsu-lb-actions" id="zsuLbActions">
            <?php if ( zsu_opt('zsu_show_like_btn', 1) ) : ?>
            <button class="zsu-lb-action-btn zsu-lb-like-btn" id="zsuLbLikeBtn" data-short-id="" aria-pressed="false" title="Like">
                <span class="zsu-action-icon">❤️</span>
                <span class="zsu-action-label" id="zsuLbLikeBtnCount">Like</span>
            </button>
            <?php endif; ?>

            <?php if ( zsu_opt('zsu_show_share_btn', 1) ) : ?>
            <button class="zsu-lb-action-btn zsu-lb-share-btn" id="zsuLbShareBtn" title="Share">
                <span class="zsu-action-icon">🔗</span>
                <span class="zsu-action-label">Share</span>
            </button>
            <?php endif; ?>

            <?php if ( zsu_opt('zsu_show_comment_btn', 0) ) : ?>
            <button class="zsu-lb-action-btn zsu-lb-comment-btn" id="zsuLbCommentBtn" title="Comments">
                <span class="zsu-action-icon">💬</span>
                <span class="zsu-action-label" id="zsuLbCommentCount">0</span>
            </button>
            <?php endif; ?>

            <button class="zsu-lb-action-btn zsu-lb-mute-btn" id="zsuLbMuteBtn" title="Mute/Unmute">
                <span class="zsu-action-icon" id="zsuMuteIcon">🔇</span>
                <span class="zsu-action-label">Mute</span>
            </button>

            <button class="zsu-lb-action-btn zsu-lb-fullscreen-btn" id="zsuLbFullscreenBtn" title="Fullscreen">
                <span class="zsu-action-icon">⛶</span>
                <span class="zsu-action-label">Expand</span>
            </button>
        </div>

        <!-- Product tray is rendered outside sidebar (below) for proper full-width bottom positioning -->

        <!-- Comments panel (hidden by default) -->
        <?php if ( zsu_opt('zsu_enable_comments', 0) ) : ?>
        <div class="zsu-comments-panel" id="zsuCommentsPanel" style="display:none;">
            <div class="zsu-comments-header">
                <span>💬 Comments</span>
                <button class="zsu-comments-close" id="zsuCommentsClose">✕</button>
            </div>
            <div class="zsu-comments-list" id="zsuCommentsList"></div>
            <?php if ( is_user_logged_in() ) : ?>
            <div class="zsu-comment-input-row">
                <img src="<?php echo esc_url(get_avatar_url(get_current_user_id(),['size'=>32])); ?>"
                     class="zsu-comment-avatar" alt="" loading="lazy">
                <input type="text" class="zsu-comment-input" id="zsuCommentInput" placeholder="Add a comment…" maxlength="500">
                <button class="zsu-comment-submit" id="zsuCommentSubmit" aria-label="Post">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </aside><!-- .zsu-lb-sidebar -->

    <!-- Video progress bar overlay (inside lightbox, top) -->
    <div class="zsu-lb-progress" id="zsuLbProgress" aria-hidden="true">
        <div class="zsu-lb-progress-bar" id="zsuLbProgressBar"></div>
    </div>

    <!-- YouTube-style floating product chips (injected per slide by JS) -->
    <div id="zsuShopChips" style="display:none;"></div>

    <!-- Shop bottom sheet — slides up when chip is tapped -->
    <div id="zsuShopSheet" style="display:none;">
        <div id="zsuShopSheetInner">
            <div id="zsuShopSheetHandle"></div>
            <div id="zsuShopSheetHeader">
                <span>&#128717; Shop This Look</span>
                <button id="zsuShopSheetClose" aria-label="Close">&#10005;</button>
            </div>
            <div id="zsuShopSheetList"></div>
        </div>
    </div>
    <div id="zsuShopSheetOverlay" style="display:none;"></div>

</div><!-- .zsu-lightbox -->

<!-- ════════════════════════════════════════════════════════════ -->
<!--  SHARE SHEET                                                 -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="zsu-share-sheet" id="zsuShareSheet" role="dialog" aria-modal="true" aria-label="Share" style="display:none;">
    <div class="zsu-share-sheet-inner">
        <div class="zsu-share-sheet-header">
            <span class="zsu-share-sheet-title">Share this Short</span>
            <button class="zsu-share-sheet-close" id="zsuShareSheetClose" aria-label="Close">✕</button>
        </div>
        <div class="zsu-share-preview" id="zsuSharePreview">
            <img class="zsu-share-thumb" id="zsuShareThumb" src="" alt="" loading="lazy">
            <span class="zsu-share-video-title" id="zsuShareVideoTitle"></span>
        </div>
        <div class="zsu-share-platforms" id="zsuSharePlatforms">
            <?php
            $platforms = [
                [ 'whatsapp',  '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.121 1.533 5.849L.063 23.617a.5.5 0 0 0 .619.62l5.849-1.471A11.944 11.944 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.71 9.71 0 0 1-4.964-1.356l-.356-.211-3.688.927.944-3.622-.231-.372A9.713 9.713 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>', 'WhatsApp' ],
                [ 'facebook',  '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.514c-1.491 0-1.956.93-1.956 1.883v2.271h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>', 'Facebook' ],
                [ 'twitter',   '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>', 'Twitter / X' ],
                [ 'telegram',  '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>', 'Telegram' ],
                [ 'linkedin',  '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>', 'LinkedIn' ],
                [ 'pinterest', '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>', 'Pinterest' ],
                [ 'email',     '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>', 'Email' ],
                [ 'copy',      '<svg viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>', 'Copy Link' ],
            ];
            foreach ( $platforms as [ $key, $svg, $label ] ) :
            ?>
            <button class="zsu-share-btn" data-platform="<?php echo esc_attr($key); ?>" aria-label="Share on <?php echo esc_attr($label); ?>">
                <span class="zsu-share-platform-icon"><?php echo $svg; ?></span>
                <span class="zsu-share-platform-label"><?php echo esc_html($label); ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <!-- Copy link row -->
        <div class="zsu-copy-link-row">
            <input type="text" class="zsu-copy-link-input" id="zsuCopyLinkInput" readonly aria-label="Share URL">
            <button class="zsu-copy-link-btn" id="zsuCopyLinkBtn">Copy</button>
        </div>
    </div>
</div>
<div class="zsu-overlay" id="zsuShareOverlay" style="display:none;"></div>

<!-- ════════════════════════════════════════════════════════════ -->
<!--  PRODUCT QUICK-VIEW MODAL                                   -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="zsu-product-modal" id="zsuProductModal" role="dialog" aria-modal="true" aria-label="Product details" style="display:none;">
    <div class="zsu-product-modal-inner">
        <button class="zsu-product-modal-close" id="zsuProductModalClose" aria-label="Close">✕</button>
        <div class="zsu-product-modal-body" id="zsuProductModalBody">
            <!-- Injected by JS -->
        </div>
    </div>
</div>
<div class="zsu-overlay" id="zsuProductOverlay" style="display:none;"></div>

<!-- ════════════════════════════════════════════════════════════ -->
<!--  TOAST NOTIFICATION                                          -->
<!-- ════════════════════════════════════════════════════════════ -->
<div class="zsu-toast-container" id="zsuToastContainer" aria-live="polite" aria-atomic="true"></div>

<!-- ════════════════════════════════════════════════════════════ -->
<!--  CONFETTI CANVAS                                             -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if ( zsu_opt('zsu_confetti_like', 1) ) : ?>
<canvas id="zsuConfettiCanvas" aria-hidden="true"></canvas>
<?php endif; ?>

