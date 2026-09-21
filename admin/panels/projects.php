<?php
defined('ADMIN_PANEL') || exit;

$categories = categories_all();
$projects   = projects_all();
$editing    = editing_row('projects');

$filter  = isset($_GET['cat']) ? $_GET['cat'] : 'All';
$visible = $projects;

if ($filter !== 'All') {
    $visible = array_values(array_filter($projects, function ($p) use ($filter) {
        return $p['category'] === $filter;
    }));
}
?>

<section class="panel" id="item-form">
    <h2 class="panel-title"><?php echo $editing ? 'Edit project' : 'Add a new project'; ?></h2>

    <?php if (!$categories): ?>
        <div class="alert alert-error">
            Create a category first from the <a href="dashboard.php?tab=categories">Categories</a> tab.
        </div>
    <?php else: ?>
        <form method="post" action="actions.php" enctype="multipart/form-data" class="grid-form">
            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
            <input type="hidden" name="action" value="project_save">
            <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?php echo (int) $editing['id']; ?>">
            <?php endif; ?>

            <label class="field">
                <span>Title *</span>
                <input type="text" name="title" required maxlength="120"
                    value="<?php echo $editing ? e($editing['title']) : ''; ?>"
                    placeholder="Zemfar Ecommerce Store">
            </label>

            <label class="field">
                <span>Category *</span>
                <select name="category_id" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo (int) $c['id']; ?>"
                            <?php echo ($editing && (int) $editing['category_id'] === (int) $c['id']) ? 'selected' : ''; ?>>
                            <?php echo e($c['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Live link (optional)</span>
                <input type="text" name="link" maxlength="300"
                    value="<?php echo $editing ? e($editing['link']) : ''; ?>"
                    placeholder="https://example.com">
            </label>

            <label class="field">
                <span>Order (lower numbers appear first)</span>
                <input type="number" name="order" min="0" step="1"
                    value="<?php echo $editing ? (int) $editing['sort_order'] : ''; ?>"
                    placeholder="auto">
            </label>

            <label class="field field-wide">
                <span>Image <?php echo $editing ? '(leave empty to keep the current one)' : '*'; ?></span>
                <input type="file" name="image" accept="image/*" <?php echo $editing ? '' : 'required'; ?>>
                <small class="hint">JPG / PNG / WEBP / GIF &middot; max <?php echo round(MAX_UPLOAD_BYTES / 1048576); ?>MB</small>
            </label>

            <?php if ($editing && $editing['image']): ?>
                <div class="field field-wide current-img">
                    <span>Current image</span>
                    <img src="../<?php echo e($editing['image']); ?>" alt="">
                </div>
            <?php endif; ?>

            <div class="field-wide form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Add'; ?></button>
                <?php if ($editing): ?>
                    <a class="btn btn-ghost" href="dashboard.php?tab=projects">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-head">
        <h2 class="panel-title">All projects</h2>
        <div class="filter-chips">
            <a class="chip <?php echo $filter === 'All' ? 'active' : ''; ?>" href="dashboard.php?tab=projects">All</a>
            <?php foreach ($categories as $c): ?>
                <a class="chip <?php echo $filter === $c['name'] ? 'active' : ''; ?>"
                    href="dashboard.php?tab=projects&cat=<?php echo urlencode($c['name']); ?>"><?php echo e($c['name']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$visible): ?>
        <p class="empty">There are no projects here yet.</p>
    <?php else: ?>
        <div class="project-grid">
            <?php foreach ($visible as $p): ?>
                <article class="project-card">
                    <div class="project-thumb">
                        <img src="../<?php echo e($p['image']); ?>" alt="<?php echo e($p['title']); ?>" loading="lazy">
                        <span class="order-badge">#<?php echo (int) $p['sort_order']; ?></span>
                    </div>
                    <div class="project-body">
                        <h3><?php echo e($p['title']); ?></h3>
                        <span class="cat-tag"><?php echo e($p['category'] !== null ? $p['category'] : 'No category'); ?></span>
                        <?php if (!empty($p['link'])): ?>
                            <a class="project-link" href="<?php echo e($p['link']); ?>" target="_blank" rel="noopener">
                                <?php echo e(parse_url($p['link'], PHP_URL_HOST)); ?> &nearr;
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php row_actions('projects', $p, 'project_delete', 'Delete this project?'); ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
