<?php
defined('ADMIN_PANEL') || exit;

$skills   = skills_all();
$counters = counters_all();

/* This tab holds two lists, so ?what= tells us which one is being edited */
$what        = isset($_GET['what']) ? $_GET['what'] : '';
$editSkill   = $what === 'skill'   ? editing_row('skills')   : null;
$editCounter = $what === 'counter' ? editing_row('counters') : null;
?>

<section class="panel" id="item-form">
    <h2 class="panel-title">About section text</h2>

    <form method="post" action="actions.php" enctype="multipart/form-data" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('about_stroke', 'Large background text', 0, 'Shown as an outline behind the heading');
        setting_field('about_label', 'Small blue heading');
        setting_field('about_heading', 'Main heading');
        setting_field('about_para', 'Paragraph', 5);
        ?>

        <label class="field field-wide">
            <span>Change your photo</span>
            <input type="file" name="about_image_file" accept="image/*">
            <small class="hint">Leave empty to keep the current one &middot; max <?php echo round(MAX_UPLOAD_BYTES / 1048576); ?>MB</small>
        </label>

        <?php if (s('about_image') !== ''): ?>
            <div class="field field-wide current-img">
                <span>Current photo</span>
                <img src="../<?php echo e(s('about_image')); ?>" alt="">
            </div>
        <?php endif; ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<!-- ============================= SKILLS ============================= -->
<section class="panel">
    <h2 class="panel-title"><?php echo $editSkill ? 'Edit skill' : 'Add a new skill'; ?></h2>
    <p class="panel-note">These are the pill buttons in the About section &mdash; for example "HTML5 &amp; CSS3 (90%)".</p>

    <form method="post" action="actions.php" enctype="multipart/form-data" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="skill_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
        <?php if ($editSkill): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editSkill['id']; ?>">
        <?php endif; ?>

        <label class="field">
            <span>Text *</span>
            <input type="text" name="label" required maxlength="120"
                value="<?php echo $editSkill ? e($editSkill['label']) : ''; ?>"
                placeholder="HTML5 &amp; CSS3 (90%)">
        </label>

        <label class="field">
            <span>Icon <?php echo $editSkill ? '(leave empty to keep the current one)' : ''; ?></span>
            <input type="file" name="icon" accept="image/*">
        </label>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editSkill ? 'Update' : 'Add'; ?></button>
            <?php if ($editSkill): ?>
                <a class="btn btn-ghost" href="dashboard.php?tab=about">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!$skills): ?>
        <p class="empty">There are no skills yet.</p>
    <?php else: ?>
        <div class="project-grid">
            <?php foreach ($skills as $sk): ?>
                <article class="project-card">
                    <div class="project-body">
                        <h3><?php echo e($sk['label']); ?></h3>
                        <?php if (!empty($sk['icon'])): ?>
                            <span class="cat-tag"><?php echo e(basename($sk['icon'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php row_actions('about', $sk, 'skill_delete', 'Delete this skill?', array('what' => 'skill')); ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ============================= COUNTERS ============================= -->
<section class="panel">
    <h2 class="panel-title"><?php echo $editCounter ? 'Edit counter' : 'Add a new counter'; ?></h2>
    <p class="panel-note">On the website these count up from zero as the visitor scrolls.</p>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="counter_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
        <?php if ($editCounter): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editCounter['id']; ?>">
        <?php endif; ?>

        <label class="field">
            <span>Number *</span>
            <input type="text" name="value" required maxlength="20"
                value="<?php echo $editCounter ? e($editCounter['value']) : ''; ?>"
                placeholder="60+">
            <small class="hint">For example 2+, 4K+, 60+</small>
        </label>

        <label class="field">
            <span>Label *</span>
            <input type="text" name="label" required maxlength="120"
                value="<?php echo $editCounter ? e($editCounter['label']) : ''; ?>"
                placeholder="Projects Done">
        </label>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editCounter ? 'Update' : 'Add'; ?></button>
            <?php if ($editCounter): ?>
                <a class="btn btn-ghost" href="dashboard.php?tab=about">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!$counters): ?>
        <p class="empty">There are no counters yet.</p>
    <?php else: ?>
        <div class="project-grid">
            <?php foreach ($counters as $cn): ?>
                <article class="project-card">
                    <div class="project-body">
                        <h3><?php echo e($cn['value']); ?></h3>
                        <span class="cat-tag"><?php echo e($cn['label']); ?></span>
                    </div>
                    <?php row_actions('about', $cn, 'counter_delete', 'Delete this counter?', array('what' => 'counter')); ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
