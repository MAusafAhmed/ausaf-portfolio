<?php
require_once __DIR__ . '/../api/auth.php';

start_session();

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$blocked = login_blocked_for();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if ($blocked > 0) {
        $error = 'Too many failed attempts. Please try again in ' . ceil($blocked / 60) . ' minute(s).';
    } else {
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $pass  = isset($_POST['password']) ? $_POST['password'] : '';

        $emailOk = hash_equals(strtolower(ADMIN_EMAIL), strtolower($email));
        $passOk  = hash_equals(ADMIN_PASSWORD, $pass);

        if ($emailOk && $passOk) {
            clear_failed_logins();
            log_in();
            header('Location: dashboard.php');
            exit;
        }

        record_failed_login();
        $error = 'Incorrect email or password.';
        $blocked = login_blocked_for();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login &middot; Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Glory:wght@400;500;600;700&family=Bayon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>

<body class="login-body">
    <form class="login-card" method="post" autocomplete="on">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">

        <h1 class="login-title">Admin Panel</h1>
        <p class="login-sub">Sign in to manage your portfolio</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" required autofocus placeholder="you@example.com">
        </label>

        <label class="field">
            <span>Password</span>
            <input type="password" name="password" required placeholder="••••••••">
        </label>

        <button type="submit" class="btn btn-primary btn-block">Login</button>

        <a class="login-back" href="../index.php">&larr; Back to website</a>
    </form>
</body>

</html>
