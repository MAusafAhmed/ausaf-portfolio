<?php
/* =============================================================
   Frontend template helpers
   ============================================================= */

/* Every section shares the same heading block --
   outline text, sub heading, main heading, paragraph.
   The values come from settings: <prefix>_stroke, _label, _heading, _para */
function section_head($prefix, $headingTag = 'h1')
{
    $stroke  = s($prefix . '_stroke');
    $label   = s($prefix . '_label');
    $heading = s($prefix . '_heading');
    $para    = s($prefix . '_para');

    if ($stroke !== '') {
        echo '<h3 class="sub-heading-before">' . e($stroke) . "</h3>\n";
    }
    if ($label !== '') {
        echo '<h4 class="sub-heading">' . e($label) . "</h4>\n";
    }
    if ($heading !== '') {
        echo '<' . $headingTag . ' class="section-heading">' . e($heading) . '</' . $headingTag . ">\n";
    }
    if ($para !== '') {
        echo '<p class="para">' . e($para) . "</p>\n";
    }
}

/* A single portfolio card. Renders as <a> when there is a link, otherwise <div>. */
function project_card($project)
{
    $hasLink = !empty($project['link']);
    $tag     = $hasLink ? 'a' : 'div';
    $title   = isset($project['title']) ? $project['title'] : '';
    $cat     = isset($project['category']) ? $project['category'] : '';
    $image   = isset($project['image']) ? $project['image'] : '';

    echo '<div class="portfolio-content">';
    echo '<' . $tag . ' class="portfolio-item"';
    if ($hasLink) {
        echo ' href="' . e($project['link']) . '" target="_blank" rel="noopener noreferrer"';
    }
    echo '>';

    echo '<img src="' . e($image) . '" alt="' . e($title !== '' ? $title : 'Project') . '" loading="lazy">';

    echo '<div class="portfolio-overlay">';
    echo '<h4 class="portfolio-item-title">' . e($title) . '</h4>';
    echo '<span class="portfolio-item-cat">' . ($hasLink ? 'View project &#8599;' : e($cat)) . '</span>';
    echo '</div>';

    echo '</' . $tag . '></div>';
}

/* One resume timeline item (education or experience) */
function resume_item($item)
{
?>
    <div class="btn-info">
        <div class="sun">
            <button type="button"><?php echo e($item['period']); ?></button>
        </div>
        <div class="title">
            <h6><?php echo e($item['title']); ?></h6>
        </div>
        <div class="institute">
            <p><?php echo e($item['institute']); ?></p>
        </div>
    </div>
<?php
}

/* One resume column -- Education or Experience */
function resume_column($kind, $heading, $icon, $wrapperClass)
{
    $items = resume_items($kind);
?>
    <div class="<?php echo e($wrapperClass); ?>">
        <div class="heading-img">
            <img src="<?php echo e($icon); ?>" alt="">
            <h4 class="resume-title"><?php echo e($heading); ?></h4>
        </div>
        <div class="uni-offices">
            <div class="uni-office-img">
                <img src="assets/images/line.png" alt="">
            </div>
            <div class="btns-info">
                <?php foreach ($items as $item) {
                    resume_item($item);
                } ?>
            </div>
        </div>
    </div>
<?php
}
