<?php
defined('ADMIN_PANEL') || exit;

$editing = editing_row('resume_items');

/* Which column should be preselected when adding a new entry */
$defaultKind = isset($_GET['kind']) && $_GET['kind'] === 'experience' ? 'experience' : 'education';
$formKind    = $editing ? $editing['kind'] : $defaultKind;

$columns = array(
    'education'  => array('title' => s('resume_education_title', 'Education'), 'items' => resume_items('education')),
    'experience' => array('title' => s('resume_experience_title', 'Experience'), 'items' => resume_items('experience')),
);
?>

<section class="panel" id="item-form">
    <h2 class="panel-title">Resume section text</h2>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('resume_stroke', 'Large background text');
        setting_field('resume_label', 'Small blue heading');
        setting_field('resume_heading', 'Main heading');
        setting_field('resume_para', 'Paragraph', 3);
        setting_field('resume_education_title', 'Left column name');
        setting_field('resume_experience_title', 'Right column name');
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title"><?php echo $editing ? 'Edit entry' : 'Add a new entry'; ?></h2>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="resume_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editing['id']; ?>">
        <?php endif; ?>

        <label class="field">
            <span>Which column? *</span>
            <select name="kind">
                <option value="education" <?php echo $formKind === 'education' ? 'selected' : ''; ?>>
                    <?php echo e($columns['education']['title']); ?>
                </option>
                <option value="experience" <?php echo $formKind === 'experience' ? 'selected' : ''; ?>>
                    <?php echo e($columns['experience']['title']); ?>
                </option>
            </select>
        </label>

        <label class="field">
            <span>Years</span>
            <input type="text" name="period" maxlength="60"
                value="<?php echo $editing ? e($editing['period']) : ''; ?>"
                placeholder="2020 - Present">
        </label>

        <label class="field">
            <span>Title *</span>
            <input type="text" name="title" required maxlength="160"
                value="<?php echo $editing ? e($editing['title']) : ''; ?>"
                placeholder="Senior Frontend &amp; CMS Developer">
        </label>

        <label class="field">
            <span>Institute / Company</span>
            <input type="text" name="institute" maxlength="160"
                value="<?php echo $editing ? e($editing['institute']) : ''; ?>"
                placeholder="@ Zemfar">
        </label>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Add'; ?></button>
            <?php if ($editing): ?>
                <a class="btn btn-ghost" href="dashboard.php?tab=resume">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<?php foreach ($columns as $kind => $column): ?>
    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title"><?php echo e($column['title']); ?></h2>
            <a class="btn btn-small" href="dashboard.php?tab=resume&kind=<?php echo e($kind); ?>#item-form">
                Add entry
            </a>
        </div>

        <?php if (!$column['items']): ?>
            <p class="empty">There are no entries in this column yet.</p>
        <?php else: ?>
            <div class="project-grid">
                <?php foreach ($column['items'] as $item): ?>
                    <article class="project-card">
                        <div class="project-body">
                            <h3><?php echo e($item['title']); ?></h3>
                            <span class="cat-tag"><?php echo e($item['period']); ?></span>
                            <?php if (!empty($item['institute'])): ?>
                                <span class="cat-tag"><?php echo e($item['institute']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php row_actions('resume', $item, 'resume_delete', 'Delete this entry?'); ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
