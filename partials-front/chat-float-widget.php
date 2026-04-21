<?php
/** Widget chat nổi góc màn hình — chỉ include khi đã đăng nhập user */
$chat_embed_url = htmlspecialchars(SITEURL . 'user/chat-embed.php', ENT_QUOTES, 'UTF-8');
?>
<div class="chat-float-widget" id="wowChatFloatRoot">
    <div class="chat-float-panel" id="wowChatFloatPanel" hidden>
        <iframe class="chat-float-iframe" id="wowChatFloatIframe" title="Chat WowFood" data-src="<?php echo $chat_embed_url; ?>"></iframe>
    </div>
    <button type="button" class="footer-float-btn chat-float-toggle" id="wowChatFloatToggle"
        aria-expanded="false" aria-controls="wowChatFloatPanel" title="Chat với Admin">
        <i class="bi bi-chat-dots-fill chat-float-toggle-icon" aria-hidden="true"></i>
        <span id="chatBadgeFloat" class="chat-badge is-hidden">0</span>
    </button>
</div>
<script>
(function () {
    var panel = document.getElementById('wowChatFloatPanel');
    var toggle = document.getElementById('wowChatFloatToggle');
    var iframe = document.getElementById('wowChatFloatIframe');
    if (!panel || !toggle || !iframe) return;

    function loadIframe() {
        var url = iframe.getAttribute('data-src');
        if (url && (!iframe.getAttribute('src') || iframe.getAttribute('src') === 'about:blank')) {
            iframe.setAttribute('src', url);
        }
    }

    function unloadIframe() {
        iframe.setAttribute('src', 'about:blank');
    }

    window.openWowChatFloat = function () {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        loadIframe();
    };

    window.closeWowChatFloat = function () {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        unloadIframe();
    };

    toggle.addEventListener('click', function () {
        if (panel.hidden) {
            window.openWowChatFloat();
        } else {
            window.closeWowChatFloat();
        }
    });

    /* Nút đóng nằm trong iframe (chat-embed.php) — gửi postMessage ra đây */
    window.addEventListener('message', function (e) {
        if (!e.data || e.data.type !== 'wowfood-chat-close') return;
        if (e.origin && e.origin !== window.location.origin) return;
        window.closeWowChatFloat();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) {
            window.closeWowChatFloat();
        }
    });

    function refreshBadgeWhenFloatReady() {
        if (typeof window.updateChatBadge === 'function') {
            window.updateChatBadge();
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refreshBadgeWhenFloatReady);
    } else {
        refreshBadgeWhenFloatReady();
    }
})();
</script>
