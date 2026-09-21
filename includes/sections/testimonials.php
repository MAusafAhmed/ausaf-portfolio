<?php $testimonials = testimonials_all(); ?>
<div class="testimonial-section" id="testimonials">
    <?php section_head('testimonials'); ?>

    <div class="testimonial-slider">
        <?php foreach ($testimonials as $t): ?>
            <div class="testimonial-slider-one">
                <img src="assets/images/qoute.png" alt="">

                <div class="stars">
                    <?php for ($i = 0; $i < (int) $t['rating']; $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>

                <p class="para"><?php echo e($t['quote']); ?></p>

                <div class="avatar-img-box">
                    <div class="avatar-img">
                        <img src="<?php echo e($t['avatar']); ?>" alt="">
                    </div>
                    <div class="avatar-info">
                        <h5 class="testimonial-title"><?php echo e($t['name']); ?></h5>
                        <p class="testimonial-designation"><?php echo e($t['designation']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
