<?php
/* =============================================================
   Shared helpers -- escaping, validation, uploads, JSON output.
   See db.php for data access.
   ============================================================= */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/* ---------- JSON response ---------- */
function json_out($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function fail($message, $status = 400)
{
    json_out(array('ok' => false, 'error' => $message), $status);
}

/* ---------- helpers ---------- */
function clean_text($value, $maxLen = 300)
{
    $value = is_string($value) ? $value : '';
    $value = str_replace(array("\r\n", "\r"), "\n", $value);
    $value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value));
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLen);
    }
    return substr($value, 0, $maxLen);
}

/* Allow http/https links only (blocks javascript: and friends) */
function clean_url($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $value)) {
        $value = 'https://' . ltrim($value, '/');
    }
    return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* ---------- image upload ---------- */
/* Returns array(ok => bool, path => 'uploads/projects/x.png', error => '...') */
function handle_image_upload($field, $subfolder = 'projects')
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return array('ok' => true, 'path' => '');   // no file was sent
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $map = array(
            UPLOAD_ERR_INI_SIZE   => 'The image exceeds the server limit (php.ini).',
            UPLOAD_ERR_FORM_SIZE  => 'The image is too large.',
            UPLOAD_ERR_PARTIAL    => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary folder was found on the server.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write to disk.',
        );
        $msg = isset($map[$file['error']]) ? $map[$file['error']] : 'The image upload failed.';
        return array('ok' => false, 'error' => $msg);
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return array('ok' => false, 'error' => 'The image must be smaller than ' . round(MAX_UPLOAD_BYTES / 1048576) . 'MB.');
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return array('ok' => false, 'error' => 'That is not a valid image file.');
    }

    $allowed = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    );
    if (!isset($allowed[$info[2]])) {
        return array('ok' => false, 'error' => 'Only JPG, PNG, GIF or WEBP files are allowed.');
    }

    /* keep the subfolder a plain name -- no path traversal */
    $subfolder = preg_replace('/[^a-z0-9_-]/i', '', (string) $subfolder);
    if ($subfolder === '') {
        $subfolder = 'projects';
    }

    $dir = BASE_DIR . '/uploads/' . $subfolder;
    $url = 'uploads/' . $subfolder;

    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        return array('ok' => false, 'error' => 'The uploads/' . $subfolder . ' folder could not be created.');
    }

    $name = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$info[2]];
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return array('ok' => false, 'error' => 'The image could not be saved. Check the folder permissions.');
    }
    @chmod($dest, 0644);

    return array('ok' => true, 'path' => $url . '/' . $name);
}

/* Only delete files that live inside our own uploads folder */
function delete_upload($relPath)
{
    if (!is_string($relPath) || strpos($relPath, 'uploads/') !== 0) {
        return;
    }
    $full = BASE_DIR . '/' . $relPath;
    $real = realpath($full);
    $base = realpath(BASE_DIR . '/uploads');
    if ($real && $base && strpos($real, $base) === 0 && is_file($real)) {
        @unlink($real);
    }
}
