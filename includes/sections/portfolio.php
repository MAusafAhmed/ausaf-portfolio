<?php
$categories = categories_all();
$projects   = projects_all();

/* The first tab is always "All" -- it holds every project */
$tabs = array(array('name' => 'All', 'projects' => $projects));

foreach ($categories as $cat) {
    $tabs[] = array(
        'name'     => $cat['name'],
        'projects' => array_values(array_filter($projects, function ($p) use ($cat) {
            return $p['category'] === $cat['name'];
        })),
    );
}
?>
<div class="portfolio-section" id="portfolio">
    <?php section_head('portfolio'); ?>

    <div class="tab portfolio-tabs">
        <?php foreach ($tabs as $i => $tab): ?>
            <button type="button" class="tablinks<?php echo $i === 0 ? ' active' : ''; ?>"
                data-tab="<?php echo e($tab['name']); ?>"><?php echo e($tab['name']); ?></button>
        <?php endforeach; ?>
    </div>

    <div class="portfolio-panels">
        <?php foreach ($tabs as $i => $tab): ?>
            <div class="tabcontent<?php echo $i === 0 ? ' active' : ''; ?>" data-tab="<?php echo e($tab['name']); ?>">
                <div class="portfolios">
                    <?php if (!$tab['projects']): ?>
                        <p class="para portfolio-empty">No projects in this category yet.</p>
                    <?php else: ?>
                        <?php foreach ($tab['projects'] as $project) {
                            project_card($project);
                        } ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
