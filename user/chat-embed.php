<?php
/**
 * Giao diện chat tối giản để nhúng trong iframe (popup góc màn hình).
 */
require_once(__DIR__ . '/../config/constants.php');
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Content-Type: text/html; charset=utf-8');
    $login = htmlspecialchars(SITEURL . 'user/login.php', ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;font-family:system-ui,sans-serif;padding:1rem;text-align:center;background:#f8f9fa;"><p style="margin:0 0 12px;">Vui lòng đăng nhập để chat.</p><a href="' . $login . '">Đăng nhập</a></body></html>';
    exit;
}

$order_code = isset($_GET['order_code']) ? trim((string) $_GET['order_code']) : '';

$conn->query("CREATE TABLE IF NOT EXISTS tbl_order_notification (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  order_code varchar(20) NOT NULL,
  user_id int(10) UNSIGNED NOT NULL,
  message varchar(255) NOT NULL,
  is_read tinyint(1) DEFAULT 0,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$uid = (int) $_SESSION['user_id'];
$conn->query("UPDATE tbl_order_notification SET is_read = 1 WHERE user_id = {$uid} AND order_code = 'CHAT'");

$surl = htmlspecialchars(SITEURL, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - WowFood</title>
  
    <link rel="stylesheet" href="<?php echo $surl; ?>css/chat-embed.css?ver=<?php echo rand(); ?>"/>
</head>
<body class="chat-embed-page">
<div class="chat-embed-shell">
    <div class="chat-embed-topstripe" aria-hidden="true"></div>
    <header class="chat-embed-header">
        <button type="button" class="chat-float-close" id="chatEmbedCloseBtn" aria-label="Đóng chat">&times;</button>
        <p class="chat-embed-header-title">Chat hỗ trợ</p>
        <p class="chat-embed-header-desc">Chúng tôi phản hồi trong thời gian sớm nhất.</p>
    </header>
    <div class="chat-embed-messages" id="chatMessages" role="log" aria-live="polite"></div>
    <div class="chat-embed-composer">
        <form id="chatForm" class="chat-embed-form">
            <input type="hidden" name="order_code" id="orderCode" value="<?php echo htmlspecialchars($order_code, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" class="chat-embed-input" id="messageInput" placeholder="Nhập tin nhắn..." autocomplete="off" required>
            <button type="submit" class="chat-embed-send" id="sendButton">Gửi</button>
        </form>
    </div>
</div>
<script>
    const SITEURL = '<?php echo $surl; ?>';
    let lastMessageId = 0;
    let pollingInterval = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        const date = new Date(timeString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    function addMessageToChat(msg) {
        const messagesDiv = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message ' + (msg.sender_type === 'user' ? 'message-sent' : 'message-received');
        const senderName = msg.sender_type === 'user'
            ? (msg.user_name || 'Bạn')
            : (msg.admin_name || 'Admin');
        var isUser = msg.sender_type === 'user';
        var senderHtml = isUser ? '' : ('<div class="message-sender">' + escapeHtml(senderName) + '</div>');
        messageDiv.innerHTML =
            '<div class="message-content">' +
            senderHtml +
            '<div class="message-text">' + escapeHtml(msg.message || '') + '</div>' +
            '<div class="message-time">' + escapeHtml(formatTime(msg.created_at)) + '</div>' +
            '</div>';
        messagesDiv.appendChild(messageDiv);
    }

    function scrollToBottom() {
        const messagesDiv = document.getElementById('chatMessages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function loadMessages(isInitial) {
        if (isInitial) {
            lastMessageId = 0;
            document.getElementById('chatMessages').innerHTML = '';
        }
        const url = SITEURL + 'api/get-user-messages.php?last_id=' + encodeURIComponent(lastMessageId);
        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success) return;
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(function (msg) {
                        addMessageToChat(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id || 0);
                    });
                    scrollToBottom();
                } else if (isInitial) {
                    document.getElementById('chatMessages').innerHTML =
                        '<p class="chat-embed-empty">Chưa có tin nhắn. Hãy nhắn cho chúng tôi!</p>';
                }
            })
            .catch(function () {});
    }

    document.getElementById('chatForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const orderCodeEl = document.getElementById('orderCode');
        const message = (input.value || '').trim();
        if (!message) return;
        sendButton.disabled = true;
        sendButton.textContent = '…';
        const fd = new FormData();
        fd.append('message', message);
        fd.append('order_code', orderCodeEl ? orderCodeEl.value : '');
        fetch(SITEURL + 'api/send-user-message.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    input.value = '';
                    loadMessages(false);
                } else {
                    alert('Lỗi: ' + (data && data.message ? data.message : 'Không rõ'));
                }
                sendButton.disabled = false;
                sendButton.textContent = 'Gửi';
            })
            .catch(function () {
                alert('Có lỗi xảy ra khi gửi tin nhắn');
                sendButton.disabled = false;
                sendButton.textContent = 'Gửi';
            });
    });

    (function () {
        var closeBtn = document.getElementById('chatEmbedCloseBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                try {
                    if (window.parent && window.parent !== window) {
                        window.parent.postMessage({ type: 'wowfood-chat-close' }, window.location.origin);
                    }
                } catch (err) {}
            });
        }
    })();

    window.addEventListener('load', function () {
        loadMessages(true);
        pollingInterval = setInterval(function () { loadMessages(false); }, 2000);
    });

    window.addEventListener('beforeunload', function () {
        if (pollingInterval) clearInterval(pollingInterval);
    });
</script>
</body>
</html>
