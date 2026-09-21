<?php
defined('ADMIN_PANEL') || exit;

$services = services_all();
$editing  = editing_row('services');
?>

<section class="panel" id="item-form">
    <h2 class="panel-title">Services section text</h2>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('services_stroke', 'Large background text');
        setting_field('services_label', 'Small blue heading');
        setting_field('services_heading', 'Main heading');
        setting_field('services_para', 'Paragraph', 4);
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title"><?php echo $editing ? 'Edit service' : 'Add a new service'; ?></h2>

    <form method="post" action="actions.php" enctype="multipart/form-data" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="service_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editing['id']; ?>">
        <?php endif; ?>

        <label class="field">
            <span>Title *</span>
            <input type="text" name="title" required maxlength="120"
                value="<?php echo $editing ? e($editing['title']) : ''; ?>"
                placeholder="Wordpress Websites">
        </label>

        <label class="field">
            <span>Icon <?php echo $editing ? '(leave empty to keep the current one)' : ''; ?></span>
            <input type="file" name="icon" accept="image/*">
        </label>

        <label class="field field-wide">
            <span>Description</span>
            <textarea name="description" rows="4"><?php echo $editing ? e($editing['description']) : ''; ?></textarea>
        </label>

        <?php if ($editing && $editing['icon']): ?>
            <div class="field field-wide current-img">
                <span>Current icon</span>
                <img src="../<?php echo e($editing['icon']); ?>" alt="">
            </div>
        <?php endif; ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Add'; ?></button>
            <?php if ($editing): ?>
                <a class="btn btn-ghost" href="dashboard.php?tab=services">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">All services</h2>

    <?php if (!$services): ?>
        <p class="empty">There are no services yet.</p>
    <?php else: ?>
        <div class="project-grid">
            <?php foreach ($services as $sv): ?>
                <article class="project-card">
                    <?php if (!empty($sv['icon'])): ?>
                        <div class="project-thumb">
                            <img src="../<?php echo e($sv['icon']); ?>" alt="" loading="lazy">
                            <span class="order-badge">#<?php echo (int) $sv['sort_order']; ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="project-body">
                        <h3><?php echo e($sv['title']); ?></h3>
                        <span class="cat-tag"><?php echo e(mb_substr((string) $sv['description'], 0, 60)); ?>&hellip;</span>
                    </div>
                    <?php row_actions('services', $sv, 'service_delete', 'Delete this service?'); ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
