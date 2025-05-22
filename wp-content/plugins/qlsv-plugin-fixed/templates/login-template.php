<?php
/**
 * Template đăng nhập tùy chỉnh
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

get_header();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

// Xử lý lỗi đăng nhập
$error = '';
if (isset($_GET['login']) && $_GET['login'] == 'failed') {
    $error = '<div class="login-error">Tên đăng nhập hoặc mật khẩu không chính xác.</div>';
} elseif (isset($_GET['login']) && $_GET['login'] == 'empty') {
    $error = '<div class="login-error">Vui lòng nhập tên đăng nhập và mật khẩu.</div>';
}

// Thêm style tùy chỉnh
wp_enqueue_style('qlsv-login-styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/qlsv-login.css', array(), '1.0.0');
?>

<div id="login">
    <form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
        <?php echo $error; ?>
        
        <p>
            <input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" autocomplete="username" required placeholder="username">
        </p>

        <p>
            <div class="password-field-container">
                <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="current-password" required placeholder="password">
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
            </div>
        </p>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="LOG IN">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url()); ?>">
            <input type="hidden" name="testcookie" value="1">
        </p>
        
        <p id="nav">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot password?</a>
        </p>
    </form>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('user_pass');
        const passwordIcon = document.querySelector('.password-toggle');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.textContent = '👁️‍🗨️';
        } else {
            passwordInput.type = 'password';
            passwordIcon.textContent = '👁️';
        }
    }
</script>

<?php get_footer(); ?> 