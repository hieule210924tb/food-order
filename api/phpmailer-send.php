<?php
/**
 * Gửi email sử dụng PHPMailer
 * Sử dụng các file PHPMailer trong thư mục src/
 */

require_once(__DIR__ . '/../config/constants.php');
require_once(__DIR__ . '/../config/email-config.php');

// Include PHPMailer files từ thư mục src/
$phpmailer_path = __DIR__ . '/../src/PHPMailer.php';
$smtp_path = __DIR__ . '/../src/SMTP.php';
$exception_path = __DIR__ . '/../src/Exception.php';

if (file_exists($phpmailer_path) && file_exists($smtp_path) && file_exists($exception_path)) {
    require_once($exception_path);
    require_once($smtp_path);
    require_once($phpmailer_path);
}

function sendEmailWithPHPMailer($to, $subject, $body) {
    try {
        // Kiểm tra xem class PHPMailer có tồn tại không (có thể là PHPMailer\PHPMailer\PHPMailer hoặc PHPMailer)
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $exceptionClass = 'PHPMailer\PHPMailer\Exception';
            $encryption = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = $encryption;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 0; // Tắt debug (đặt = 2 để bật debug khi cần)
        // Chuyển debug output vào file thay vì output ra browser
        $mail->Debugoutput = function($str, $level) {
  
        };
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    } catch (\Exception $e) {
        error_log("General Error: " . $e->getMessage());
        return false;
    }
}

?>

