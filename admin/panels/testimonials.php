<?php
defined('ADMIN_PANEL') || exit;

$testimonials = testimonials_all();
$editing      = editing_row('testimonials');
?>

<section class="panel" id="item-form">
    <h2 class="panel-title">Testimonials section text</h2>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('testimonials_stroke', 'Large background text');
        setting_field('testimonials_label', 'Small blue heading');
        setting_field('testimonials_heading', 'Main heading');
        setting_field('testimonials_para', 'Paragraph (can be left empty)', 3);
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title"><?php echo $editing ? 'Edit testimonial' : 'Add a new testimonial'; ?></h2>

    <form method="post" action="actions.php" enctype="multipart/form-data" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="testimonial_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editing['id']; ?>">
        <?php endif; ?>

        <label class="field">
            <span>Client name *</span>
            <input type="text" name="name" required maxlength="120"
                value="<?php echo $editing ? e($editing['name']) : ''; ?>"
                placeholder="John Peter">
        </label>

        <label class="field">
            <span>Job title</span>
            <input type="text" name="designation" maxlength="120"
                value="<?php echo $editing ? e($editing['designation']) : ''; ?>"
                placeholder="CTO Zemfar">
        </label>

        <label class="field">
            <span>Stars</span>
            <select name="rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>"
                        <?php echo ($editing && (int) $editing['rating'] === $i) ? 'selected' : ''; ?>>
                        <?php echo $i; ?> star<?php echo $i === 1 ? '' : 's'; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </label>

        <label class="field">
            <span>Photo <?php echo $editing ? '(leave empty to keep the current one)' : ''; ?></span>
            <input type="file" name="avatar" accept="image/*">
        </label>

        <label class="field field-wide">
            <span>What they said</span>
            <textarea name="quote" rows="4"><?php echo $editing ? e($editing['quote']) : ''; ?></textarea>
        </label>

        <?php if ($editing && $editing['avatar']): ?>
            <div class="field field-wide current-img">
                <span>Current photo</span>
                <img src="../<?php echo e($editing['avatar']); ?>" alt="">
            </div>
        <?php endif; ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Add'; ?></button>
            <?php if ($editing): ?>
                <a class="btn btn-ghost" href="dashboard.php?tab=testimonials">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">All testimonials</h2>

    <?php if (!$testimonials): ?>
        <p class="empty">There are no testimonials yet.</p>
    <?php else: ?>
        <div class="project-grid">
            <?php foreach ($testimonials as $t): ?>
                <article class="project-card">
                    <?php if (!empty($t['avatar'])): ?>
                        <div class="project-thumb">
                            <img src="../<?php echo e($t['avatar']); ?>" alt="" loading="lazy">
                            <span class="order-badge"><?php echo str_repeat('&#9733;', (int) $t['rating']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="project-body">
                        <h3><?php echo e($t['name']); ?></h3>
                        <span class="cat-tag"><?php echo e($t['designation']); ?></span>
                    </div>
                    <?php row_actions('testimonials', $t, 'testimonial_delete', 'Delete this testimonial?'); ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
