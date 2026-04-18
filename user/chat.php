<?php
require_once('../config/constants.php');
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = SITEURL . 'user/chat.php';
    header('location:' . SITEURL . 'user/login.php');
    exit;
}

$order_code = isset($_GET['order_code']) ? trim((string)$_GET['order_code']) : '';

// Khi user vào trang chat thì coi các thông báo chat (order_code = 'CHAT') là đã đọc
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

include('../partials-front/menu.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat với Admin - WowFood</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/chat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="chat-container" style="margin-top: 100px; padding: 20px;">
        <div class="chat-wrapper">
            <div class="chat-header">
                <h2><i class="bi bi-chat-dots"></i> Chat với Admin</h2>
                <p>Chúng tôi sẽ phản hồi trong thời gian sớm nhất</p>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <!-- Messages will be loaded here -->
            </div>
            
            <div class="chat-input-container">
                <form id="chatForm" class="chat-form">
                    <input type="hidden" name="order_code" id="orderCode" value="<?php echo htmlspecialchars($order_code); ?>">
                    <input type="text" id="messageInput" placeholder="Nhập tin nhắn của bạn..." autocomplete="off" required>
                    <button type="submit" id="sendButton">Gửi</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const SITEURL = '<?php echo SITEURL; ?>';
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
            messageDiv.className = `message ${msg.sender_type === 'user' ? 'message-sent' : 'message-received'}`;

            const senderName = msg.sender_type === 'user'
                ? (msg.user_name || 'Bạn')
                : (msg.admin_name || 'Admin');

            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-sender">${escapeHtml(senderName)}</div>
                    <div class="message-text">${escapeHtml(msg.message || '')}</div>
                    <div class="message-time">${escapeHtml(formatTime(msg.created_at))}</div>
                </div>
            `;

            messagesDiv.appendChild(messageDiv);
        }

        function scrollToBottom() {
            const messagesDiv = document.getElementById('chatMessages');
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function loadMessages(isInitial = false) {
            if (isInitial) {
                lastMessageId = 0;
                document.getElementById('chatMessages').innerHTML = '';
            }

            const url = SITEURL + 'api/get-user-messages.php?last_id=' + encodeURIComponent(lastMessageId);
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (!data || !data.success) return;
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            addMessageToChat(msg);
                            lastMessageId = Math.max(lastMessageId, msg.id || 0);
                        });
                        scrollToBottom();
                    } else if (isInitial) {
                        // Không có tin nhắn, giữ giao diện đơn giản
                        document.getElementById('chatMessages').innerHTML =
                            '<p style="text-align:center;color:#999;margin:0;">Chưa có tin nhắn nào.</p>';
                    }
                })
                .catch(() => {});
        }

        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const orderCodeEl = document.getElementById('orderCode');

            const message = (input.value || '').trim();
            if (!message) return;

            sendButton.disabled = true;
            sendButton.textContent = 'Đang gửi...';

            const fd = new FormData();
            fd.append('message', message);
            fd.append('order_code', orderCodeEl ? orderCodeEl.value : '');

            fetch(SITEURL + 'api/send-user-message.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        input.value = '';
                        loadMessages(false);
                    } else {
                        alert('Lỗi: ' + (data && data.message ? data.message : 'Không rõ'));
                    }
                    sendButton.disabled = false;
                    sendButton.textContent = 'Gửi';
                })
                .catch(() => {
                    alert('Có lỗi xảy ra khi gửi tin nhắn');
                    sendButton.disabled = false;
                    sendButton.textContent = 'Gửi';
                });
        });

        window.addEventListener('load', function() {
            loadMessages(true);
            pollingInterval = setInterval(() => loadMessages(false), 2000);
        });

        window.addEventListener('beforeunload', function() {
            if (pollingInterval) clearInterval(pollingInterval);
        });
    </script>

</body>
</html>

