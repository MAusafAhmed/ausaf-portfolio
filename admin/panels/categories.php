<?php
defined('ADMIN_PANEL') || exit;

$categories = categories_all();
$projects   = projects_all();
?>

<section class="panel">
    <h2 class="panel-title">New category</h2>
    <p class="panel-note">Each category becomes a tab on the website. The "All" tab is added automatically and always comes first.</p>

    <form method="post" action="actions.php" class="inline-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="category_add">
        <input type="hidden" name="back" value="dashboard.php?tab=categories">
        <input type="text" name="name" required maxlength="60" placeholder="e.g. Landing Page">
        <button type="submit" class="btn btn-primary">Add</button>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">Existing categories</h2>

    <?php if (!$categories): ?>
        <p class="empty">There are no categories yet.</p>
    <?php else: ?>
        <ul class="cat-list">
            <?php foreach ($categories as $c):
                $count = 0;
                foreach ($projects as $p) {
                    if ($p['category'] === $c['name']) $count++;
                }
            ?>
                <li>
                    <form method="post" action="actions.php" class="cat-row">
                        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                        <input type="hidden" name="action" value="category_rename">
                        <input type="hidden" name="id" value="<?php echo (int) $c['id']; ?>">
                        <input type="hidden" name="back" value="dashboard.php?tab=categories">
                        <input type="text" name="new" value="<?php echo e($c['name']); ?>" maxlength="60">
                        <span class="count"><?php echo $count; ?> project<?php echo $count === 1 ? '' : 's'; ?></span>
                        <button type="submit" class="btn btn-small">Rename</button>
                    </form>
                    <form method="post" action="actions.php" onsubmit="return confirm('Delete this category?');">
                        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                        <input type="hidden" name="action" value="category_delete">
                        <input type="hidden" name="id" value="<?php echo (int) $c['id']; ?>">
                        <input type="hidden" name="back" value="dashboard.php?tab=categories">
                        <button type="submit" class="btn btn-small btn-danger"
                            <?php echo $count ? 'disabled title="Remove this category\'s projects first"' : ''; ?>>Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
