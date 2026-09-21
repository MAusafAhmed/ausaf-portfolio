<?php
/* =============================================================
   DATABASE  --  MySQL (PDO)

   All site content is read through this file. The project used to
   store everything in JSON files; it now lives in the database.
   ============================================================= */

require_once __DIR__ . '/config.php';

/* ---------- connection ---------- */

function db()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ));
    } catch (PDOException $ex) {
        db_down($ex->getMessage());
    }

    return $pdo;
}

/* Show a clear message instead of a raw PHP error when the DB is unreachable */
function db_down($detail)
{
    $isApi = strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false;

    if ($isApi) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('ok' => false, 'error' => 'Could not connect to the database.'));
        exit;
    }

    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8">'
        . '<div style="font:16px/1.6 system-ui;max-width:640px;margin:80px auto;padding:0 20px">'
        . '<h1 style="font-size:22px">Could not connect to the database</h1>'
        . '<p>MySQL is not running, or the credentials in <code>api/config.php</code> are wrong.</p>'
        . '<p>If the database has not been created yet, run '
        . '<a href="install.php"><strong>install.php</strong></a> once.</p>'
        . '<p style="color:#888;font-size:14px">' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div>';
    exit;
}

/* ---------- small query helpers ---------- */

function db_all($sql, $params = array())
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_one($sql, $params = array())
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function db_run($sql, $params = array())
{
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

/* Insert or update -- every key in $data is a column.
   Empty $id means INSERT, otherwise UPDATE. Returns the row id. */
function db_save($table, $data, $id = null)
{
    $cols = array_keys($data);

    if ($id) {
        $sets = array();
        foreach ($cols as $c) {
            $sets[] = '`' . $c . '` = :' . $c;
        }
        $data['__id'] = $id;
        db_run('UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE id = :__id', $data);
        return (int) $id;
    }

    $holders = array();
    foreach ($cols as $c) {
        $holders[] = ':' . $c;
    }
    db_run(
        'INSERT INTO `' . $table . '` (`' . implode('`, `', $cols) . '`) VALUES (' . implode(', ', $holders) . ')',
        $data
    );
    return (int) db()->lastInsertId();
}

function db_delete($table, $id)
{
    return db_run('DELETE FROM `' . $table . '` WHERE id = ?', array((int) $id));
}

/* Next sort position for any ordered list table */
function next_order($table)
{
    $row = db_one('SELECT COALESCE(MAX(sort_order), 0) + 1 AS n FROM `' . $table . '`');
    return $row ? (int) $row['n'] : 1;
}

/* ---------- settings (key/value) ---------- */

function settings_all($fresh = false)
{
    static $cache = null;

    if ($cache !== null && !$fresh) {
        return $cache;
    }

    $cache = array();
    foreach (db_all('SELECT `key`, `value` FROM settings') as $row) {
        $cache[$row['key']] = $row['value'];
    }
    return $cache;
}

/* The shortcut used throughout the templates: s('about_heading') */
function s($key, $default = '')
{
    $all = settings_all();
    return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
}

function settings_save($pairs)
{
    $stmt = db()->prepare(
        'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );

    foreach ($pairs as $key => $value) {
        $stmt->execute(array($key, (string) $value));
    }

    /* the cache is stale now -- reload it from the database */
    settings_all(true);
}

/* ---------- content lists ---------- */

function categories_all()
{
    return db_all('SELECT * FROM categories ORDER BY sort_order, name');
}

function projects_all()
{
    return db_all(
        'SELECT p.*, c.name AS category
         FROM projects p
         LEFT JOIN categories c ON c.id = p.category_id
         ORDER BY p.sort_order, p.created_at DESC'
    );
}

function skills_all()
{
    return db_all('SELECT * FROM skills ORDER BY sort_order, id');
}

function counters_all()
{
    return db_all('SELECT * FROM counters ORDER BY sort_order, id');
}

function services_all()
{
    return db_all('SELECT * FROM services ORDER BY sort_order, id');
}

function testimonials_all()
{
    return db_all('SELECT * FROM testimonials ORDER BY sort_order, id');
}

function resume_items($kind = null)
{
    if ($kind === null) {
        return db_all('SELECT * FROM resume_items ORDER BY kind, sort_order, id');
    }
    return db_all(
        'SELECT * FROM resume_items WHERE kind = ? ORDER BY sort_order, id',
        array($kind)
    );
}

/* ---------- messages ---------- */

function messages_all()
{
    return db_all('SELECT * FROM messages ORDER BY created_at DESC, id DESC');
}

function messages_unread_count()
{
    $row = db_one('SELECT COUNT(*) AS n FROM messages WHERE is_read = 0');
    return $row ? (int) $row['n'] : 0;
}
