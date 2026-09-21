<?php $services = services_all(); ?>
<div class="services-section" id="services">
    <?php section_head('services'); ?>

    <div class="services">
        <?php foreach ($services as $service): ?>
            <div class="service">
                <div class="service-img">
                    <img src="<?php echo e($service['icon']); ?>" alt="">
                </div>
                <div class="service-title">
                    <h3 class="service-heading"><?php echo e($service['title']); ?></h3>
                </div>
                <div class="service-desc">
                    <p class="para"><?php echo e($service['description']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
