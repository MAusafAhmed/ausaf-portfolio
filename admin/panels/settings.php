<?php defined('ADMIN_PANEL') || exit; ?>

<section class="panel" id="item-form">
    <h2 class="panel-title">Site &amp; hero</h2>
    <p class="panel-note">The browser tab name and the text at the top of the left sidebar.</p>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('site_title', 'Browser tab title');
        setting_field('hero_greeting', 'Small line above the name', 0, 'For example: Hi, I am');
        setting_field('hero_name_first', 'Name (white part)');
        setting_field('hero_name_last', 'Name (blue part)');
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">Menu labels</h2>
    <p class="panel-note">This only changes the visible text &mdash; each link still points to the same section.</p>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('nav_about', 'About item');
        setting_field('nav_portfolio', 'Portfolio item');
        setting_field('nav_services', 'Services item');
        setting_field('nav_testimonials', 'Testimonials item');
        setting_field('nav_resume', 'Resume item');
        setting_field('nav_contact', 'Contact item');
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">Portfolio section text</h2>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('portfolio_stroke', 'Large background text');
        setting_field('portfolio_label', 'Small blue heading');
        setting_field('portfolio_heading', 'Main heading');
        setting_field('portfolio_para', 'Paragraph', 3);
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">Contact section</h2>
    <p class="panel-note">The phone, email and address appear as buttons on the website.</p>

    <form method="post" action="actions.php" class="grid-form">
        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
        <input type="hidden" name="action" value="settings_save">
        <input type="hidden" name="back" value="<?php echo e($backUrl); ?>">

        <?php
        setting_field('contact_stroke', 'Large background text');
        setting_field('contact_label', 'Small blue heading');
        setting_field('contact_heading', 'Main heading');
        setting_field('contact_para', 'Paragraph', 3);
        setting_field('contact_phone', 'Phone number');
        setting_field('contact_email', 'Email address');
        setting_field('contact_address', 'Address');
        ?>

        <div class="field-wide form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2 class="panel-title">Email &amp; login</h2>
    <p class="panel-note">
        These live in a file rather than here &mdash; open <code>api/config.php</code>.
        That is where the admin password and the Gmail App Password
        (<code>SMTP_PASS</code>) are set.
    </p>

    <?php if (SMTP_ENABLED && SMTP_PASS === ''): ?>
        <div class="alert alert-warn">
            Email is not configured yet &mdash; contact form messages are only being stored in the
            <a href="dashboard.php?tab=messages">Messages</a> tab.
        </div>
    <?php else: ?>
        <div class="alert alert-success">Email is configured &mdash; messages are also delivered to <?php echo e(MAIL_TO); ?>.</div>
    <?php endif; ?>
</section>
