<div class="footer admin-footer">
    <div class="footer-inner">
        <ul class="footer-social">
            <li><a href="#"><img src="https://img.icons8.com/fluent/50/000000/facebook-new.png" alt="Facebook" /></a></li>
            <li><a href="#"><img src="https://img.icons8.com/fluent/48/000000/instagram-new.png" alt="Instagram" /></a></li>
            <li><a href="#"><img src="https://img.icons8.com/fluent/48/000000/twitter.png" alt="Twitter" /></a></li>
        </ul>
        <p class="footer-copy">All rights reserved. Designed By <a href="">5 anh em</a></p>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php
            
            function extractMessage($html) {
                $html = strip_tags($html);
                return trim($html);
            }
            
            
            $sessionMessages = [
                'add', 'update', 'delete', 'remove', 'upload', 
                'login', 'register-success', 'login-success',
                'user-not-found', 'pwd-not-match', 'change-pwd',
                'no-category-found', 'failed-remove', 'order',
                'no-login-message'
            ];
            
            foreach($sessionMessages as $key) {
                if(isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
                    $message = extractMessage($_SESSION[$key]);
                    if(!empty($message)) {
                        
                        $icon = 'info';
                        $title = 'Thông báo';
                        
                        if(strpos(strtolower($_SESSION[$key]), 'success') !== false || 
                           strpos(strtolower($message), 'thành công') !== false ||
                           strpos(strtolower($message), 'successfully') !== false) {
                            $icon = 'success';
                            $title = 'Thành công!';
                        } elseif(strpos(strtolower($_SESSION[$key]), 'error') !== false || 
                                 strpos(strtolower($message), 'lỗi') !== false ||
                                 strpos(strtolower($message), 'failed') !== false ||
                                 strpos(strtolower($message), 'not') !== false) {
                            $icon = 'error';
                            $title = 'Lỗi!';
                        } elseif(strpos(strtolower($message), 'warning') !== false) {
                            $icon = 'warning';
                            $title = 'Cảnh báo!';
                        }
                        
                        echo "Swal.fire({
                            icon: '" . $icon . "',
                            title: '" . $title . "',
                            text: '" . addslashes($message) . "',
                            showConfirmButton: true,
                            timer: 3000
                        });";
                    }
                    unset($_SESSION[$key]);
                }
            }
            ?>
</script>
</div><!-- .admin-right -->
</div><!-- .admin-layout -->
</body>
</html>