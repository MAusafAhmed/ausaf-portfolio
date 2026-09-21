<?php
/* =============================================================
   Admin dashboard -- the shell. Each tab's content lives in panels/.
   ============================================================= */

require_once __DIR__ . '/../api/auth.php';

require_login();

$TABS = array(
    'projects'     => 'Projects',
    'categories'   => 'Categories',
    'about'        => 'About',
    'services'     => 'Services',
    'testimonials' => 'Testimonials',
    'resume'       => 'Resume',
    'settings'     => 'Settings',
    'messages'     => 'Messages',
);

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'projects';
if (!isset($TABS[$tab])) {
    $tab = 'projects';
}

$unread = messages_unread_count();
$flash  = take_flash();
$token  = csrf_token();

/* After saving, come back to this same tab (but leave edit mode) */
$backParams = array('tab' => $tab);
if ($tab === 'projects' && !empty($_GET['cat'])) {
    $backParams['cat'] = $_GET['cat'];
}
if ($tab === 'resume' && !empty($_GET['kind'])) {
    $backParams['kind'] = $_GET['kind'];
}
$backUrl = 'dashboard.php?' . http_build_query($backParams);

/* Which row is currently being edited? (?edit=<id>) */
function editing_row($table)
{
    $id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    if (!$id) {
        return null;
    }
    return db_one('SELECT * FROM `' . $table . '` WHERE id = ?', array($id));
}

/* Edit / Delete buttons shown under every list item */
function row_actions($tab, $row, $deleteAction, $confirmText, $extra = array())
{
    global $token, $backUrl;

    $params = array_merge(array('tab' => $tab, 'edit' => (int) $row['id']), $extra);
    $editUrl = 'dashboard.php?' . http_build_query($params) . '#item-form';
?>
    <div class="project-actions">
        <a class="btn btn-small" href="<?php echo e($editUrl); ?>">Edit</a>
        <form method="post" action="actions.php" onsubmit="return confirm('<?php echo e($confirmText); ?>');">
            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
            <input type="hidden" name="action" value="<?php echo e($deleteAction); ?>">
            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
            <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
        </form>
    </div>
<?php
}

/* A single settings text field / textarea */
function setting_field($key, $label, $rows = 0, $hint = '')
{
    $value = s($key);
?>
    <label class="field <?php echo $rows ? 'field-wide' : ''; ?>">
        <span><?php echo e($label); ?></span>
        <?php if ($rows): ?>
            <textarea name="settings[<?php echo e($key); ?>]" rows="<?php echo (int) $rows; ?>"><?php echo e($value); ?></textarea>
        <?php else: ?>
            <input type="text" name="settings[<?php echo e($key); ?>]" value="<?php echo e($value); ?>">
        <?php endif; ?>
        <?php if ($hint !== ''): ?><small class="hint"><?php echo e($hint); ?></small><?php endif; ?>
    </label>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard &middot; Portfolio Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Glory:wght@400;500;600;700&family=Bayon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <span class="brand-dot"></span>
                <strong>Portfolio Admin</strong>
            </div>
            <nav class="topbar-nav">
                <a href="../index.php" target="_blank">View site &nearr;</a>
                <a href="logout.php" class="danger-link">Logout</a>
            </nav>
        </div>
    </header>

    <main class="wrap">

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['text']); ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat">
                <span class="stat-num"><?php echo count(projects_all()); ?></span>
                <span class="stat-label">Projects</span>
            </div>
            <div class="stat">
                <span class="stat-num"><?php echo count(services_all()); ?></span>
                <span class="stat-label">Services</span>
            </div>
            <div class="stat">
                <span class="stat-num"><?php echo count(testimonials_all()); ?></span>
                <span class="stat-label">Testimonials</span>
            </div>
            <div class="stat">
                <span class="stat-num"><?php echo count(messages_all()); ?></span>
                <span class="stat-label">Messages<?php echo $unread ? ' (' . $unread . ' new)' : ''; ?></span>
            </div>
        </div>

        <nav class="tabs">
            <?php foreach ($TABS as $key => $label): ?>
                <a class="tab-link <?php echo $tab === $key ? 'active' : ''; ?>"
                    href="dashboard.php?tab=<?php echo e($key); ?>">
                    <?php echo e($label); ?><?php if ($key === 'messages' && $unread): ?><span class="pill"><?php echo $unread; ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php
        /* Panels must only load from here, never from a direct URL */
        define('ADMIN_PANEL', true);
        require __DIR__ . '/panels/' . $tab . '.php';
        ?>

    </main>

</body>

</html>
