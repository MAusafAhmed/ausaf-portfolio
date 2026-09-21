<?php
require_once __DIR__ . '/lib.php';

function start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('PORTFOLIO_ADMIN');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params(0, '/', '', $https, true);
    session_start();
}

function is_logged_in()
{
    start_session();
    return !empty($_SESSION['admin']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: index.php?next=' . urlencode(basename($_SERVER['PHP_SELF'])));
        exit;
    }
}

function log_in()
{
    start_session();
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    $_SESSION['login_time'] = time();
}

function log_out()
{
    start_session();
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------- CSRF ---------- */
function csrf_token()
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function check_csrf()
{
    start_session();
    $sent = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(419);
        exit('Your session has expired. Please refresh the page and try again.');
    }
}

/* ---------- login throttling ---------- */
function login_blocked_for()
{
    start_session();
    if (!empty($_SESSION['lock_until']) && $_SESSION['lock_until'] > time()) {
        return $_SESSION['lock_until'] - time();
    }
    return 0;
}

function record_failed_login()
{
    start_session();
    $_SESSION['fails'] = isset($_SESSION['fails']) ? $_SESSION['fails'] + 1 : 1;
    if ($_SESSION['fails'] >= 5) {
        $_SESSION['lock_until'] = time() + 300;   // 5 min
        $_SESSION['fails'] = 0;
    }
}

function clear_failed_logins()
{
    start_session();
    unset($_SESSION['fails'], $_SESSION['lock_until']);
}

/* ---------- flash messages ---------- */
function flash($type, $text)
{
    start_session();
    $_SESSION['flash'] = array('type' => $type, 'text' => $text);
}

function take_flash()
{
    start_session();
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}
