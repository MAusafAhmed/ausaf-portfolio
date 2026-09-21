<?php
$skills   = skills_all();
$counters = counters_all();
?>
<div class="about-me-secion" id="about">

    <div class="about-info">
        <div class="about-info-img">
            <img src="<?php echo e(s('about_image', 'assets/images/author-img.png')); ?>" alt="Author Img">
        </div>
        <div class="about-info-desc">
            <h3 class="sub-heading-before"><?php echo e(s('about_stroke')); ?></h3>
            <h4 class="sub-heading"><?php echo e(s('about_label')); ?></h4>
            <h2 class="section-heading"><?php echo e(s('about_heading')); ?></h2>

            <?php if ($skills): ?>
                <div class="about-info-desc-btns">
                    <?php foreach ($skills as $skill): ?>
                        <a href="#about" class="btn-primary">
                            <?php if (!empty($skill['icon'])): ?>
                                <img src="<?php echo e($skill['icon']); ?>" alt="">
                            <?php endif; ?>
                            <span><?php echo e($skill['label']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p class="para"><?php echo e(s('about_para')); ?></p>
        </div>
    </div>

    <?php if ($counters): ?>
        <div class="about-counters">
            <?php foreach ($counters as $counter): ?>
                <div class="counter-num-heading">
                    <h3 class="counter-number"><?php echo e($counter['value']); ?></h3>
                    <h3 class="counter-heading"><?php echo e($counter['label']); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
