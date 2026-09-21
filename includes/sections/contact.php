<?php
$phone   = s('contact_phone');
$email   = s('contact_email');
$address = s('contact_address');

/* Keep only digits and + for the tel: link */
$telHref = preg_replace('/[^0-9+]/', '', $phone);
?>
<div class="get-in-touch-section" id="contact">
    <?php section_head('contact'); ?>

    <div class="contact-ifo">
        <?php if ($phone !== ''): ?>
            <div class="number">
                <a href="tel:<?php echo e($telHref); ?>"><?php echo e($phone); ?></a>
            </div>
        <?php endif; ?>

        <?php if ($email !== ''): ?>
            <div class="email">
                <a href="mailto:<?php echo e($email); ?>"><?php echo e($email); ?></a>
            </div>
        <?php endif; ?>

        <?php if ($address !== ''): ?>
            <div class="address">
                <a href="#contact"><?php echo e($address); ?></a>
            </div>
        <?php endif; ?>
    </div>

    <div class="contact-form">
        <form id="contact-form" action="api/contact.php" method="post" novalidate>
            <div class="form-name">
                <label for="cf-name">Name</label>
                <input id="cf-name" type="text" name="name" required maxlength="100"
                    autocomplete="name" placeholder="Name">
            </div>
            <div class="form-email">
                <label for="cf-email">Email</label>
                <input id="cf-email" type="email" name="email" required maxlength="150"
                    autocomplete="email" placeholder="Email Address">
            </div>
            <div class="form-subject">
                <label for="cf-subject">Subject</label>
                <input id="cf-subject" type="text" name="subject" required maxlength="150"
                    placeholder="Subject">
            </div>
            <div class="form-message">
                <label for="cf-message">Message</label>
                <textarea id="cf-message" name="message" rows="7" required maxlength="5000"
                    placeholder="Message"></textarea>
            </div>

            <!-- honeypot: people leave this empty, bots fill it in -->
            <div class="hp-field" aria-hidden="true">
                <label for="cf-website">Website</label>
                <input id="cf-website" type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-submit">
                <button type="submit">Send Message</button>
            </div>
            <p class="form-status" role="status" aria-live="polite"></p>
        </form>
    </div>
</div>
