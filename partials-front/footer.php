<?php
$baseUrl = defined('SITEURL') ? SITEURL : '';
?>
<!-- Footer Main - Mẫu ADSDIGI -->
<footer class="footer-main footer-adsdigi-style">
    <div class="container">
        <div class="footer-grid">
            <!-- Cột 1: Công ty + Liên hệ + Mạng xã hội -->
            <div class="footer-col footer-company">
                <div class="footer-logo-wrap">
                    <span class="footer-logo-text">WowFood</span>
                </div>
                <p class="footer-company-name">Giao đồ ăn nhanh – Tươi ngon mỗi ngày</p>
                <p class="footer-desc">Chúng tôi tin rằng bữa ăn ngon không chỉ đơn giản là món ăn, mà còn là trải nghiệm giao hàng nhanh chóng và thân thiện.</p>
                <ul class="footer-info-list">
                    <li><span class="footer-info-icon">&#128205;</span> Địa chỉ: 18 Phố Viên - Bắc Từ Liêm - Hà Nội</li>
                    <li><span class="footer-info-icon">&#9742;</span> Hotline: 0983224809</li>
                    <li><span class="footer-info-icon">&#9993;</span> wowfood6868@gmail.com</li>
                </ul>
                <h4 class="footer-heading">Liên hệ với chúng tôi</h4>
                <ul class="footer-social-list">
                    <li><a href="#" target="_blank" rel="noopener" aria-label="Facebook"><img src="https://img.icons8.com/fluent/48/000000/facebook-new.png" alt="Facebook"/></a></li>
                    <li><a href="#" target="_blank" rel="noopener" aria-label="Zalo"><img src="https://img.icons8.com/color/48/000000/zalo.png" alt="Zalo"/></a></li>
                    <li><a href="#" target="_blank" rel="noopener" aria-label="Instagram"><img src="https://img.icons8.com/fluent/48/000000/instagram-new.png" alt="Instagram"/></a></li>
                </ul>
            </div>
            <!-- Cột 2: Danh mục -->
            <div class="footer-col footer-categories">
                <h4 class="footer-heading">Danh mục</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>index.php">Trang chủ</a></li>
                    <li><a href="<?php echo $baseUrl; ?>categories.php">Thực đơn</a></li>
                    <li><a href="<?php echo $baseUrl; ?>user/cart.php">Giỏ hàng</a></li>
                    <li><a href="<?php echo $baseUrl; ?>privacy-policy.php">Chính sách bảo mật</a></li>
                    <li><a href="<?php echo $baseUrl; ?>terms.php">Điều khoản sử dụng</a></li>
                    <li><a href="<?php echo $baseUrl; ?>user/request-refund.php">Chính sách khiếu nại</a></li>
                </ul>
            </div>
            <!-- Cột 3: Fanpage - Video demo -->
            <div class="footer-col footer-fanpage">
                <h4 class="footer-heading">Video giới thiệu</h4>
                <div class="footer-video-wrap">
                    <iframe
                        src="https://www.youtube.com/embed/o0NwOxnLQ4A?autoplay=1&mute=1&loop=1&playlist=o0NwOxnLQ4A&controls=1&rel=0"
                        title="Review Food - WowFood"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        class="footer-video-iframe"
                    ></iframe>
                </div>
                <p class="footer-video-caption">Review món ăn – WowFood</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-copyright">&copy; <?php echo date('Y'); ?> WowFood. All rights reserved. Thiết kế bởi <a href="#">5 anh em</a></p>
        </div>
    </div>
</footer>

<!-- Nút liên hệ nổi (bên phải): Zalo, Messenger, Facebook – nền trắng, viền xanh nhạt -->
<div class="footer-float-buttons">
    <a href="tel:0983224809" class="footer-float-btn" title="Gọi điện" aria-label="Gọi điện"><img src="https://img.icons8.com/fluent/48/0062cc/phone.png" alt="Gọi điện" class="footer-float-btn-icon"/></a>
    <a href="https://zalo.me/0983224809" target="_blank" rel="noopener" class="footer-float-btn" title="Zalo" aria-label="Zalo"><img src="https://img.icons8.com/color/48/000000/zalo.png" alt="Zalo" class="footer-float-btn-icon footer-float-btn-icon-zalo"/></a>
    <a href="#" target="_blank" rel="noopener" class="footer-float-btn" title="Messenger" aria-label="Messenger"><img src="https://img.icons8.com/fluent/48/0062cc/facebook-messenger.png" alt="Messenger" class="footer-float-btn-icon"/></a>
    <a href="#" target="_blank" rel="noopener" class="footer-float-btn" title="Facebook" aria-label="Facebook"><img src="https://img.icons8.com/color/48/000000/facebook-new.png" alt="Facebook" class="footer-float-btn-icon"/></a>
</div>

</body>
</html>
