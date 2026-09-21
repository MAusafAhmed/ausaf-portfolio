<?php
/* Document head plus the left sidebar (name and menu) */

/* The icon is only shown on small screens, where the menu collapses
   into a bar of icons; on desktop the label is shown instead. */
$nav = array(
    'about'        => array('label' => s('nav_about', 'About Me'),             'icon' => 'fa-user'),
    'portfolio'    => array('label' => s('nav_portfolio', 'Portfolio'),        'icon' => 'fa-briefcase'),
    'services'     => array('label' => s('nav_services', 'Services'),          'icon' => 'fa-cogs'),
    'testimonials' => array('label' => s('nav_testimonials', 'Testimonials'),  'icon' => 'fa-comment-dots'),
    'resume'       => array('label' => s('nav_resume', 'Resume'),              'icon' => 'fa-file-alt'),
    'contact'      => array('label' => s('nav_contact', 'Contact'),            'icon' => 'fa-paper-plane'),
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FONTS  LINKS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Glory:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Bayon&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <!-- SLICK SLIDER -->
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- The reveal rules only apply when JS is on, so content never stays hidden -->
    <script>document.documentElement.classList.add('js-anim');</script>

    <title><?php echo e(s('site_title', 'Portfolio')); ?></title>
</head>

<body>
    <section class="main">
        <div class="container">
            <div class="main-left">
                <h4 class="sub-heading-one"><?php echo e(s('hero_greeting')); ?></h4>
                <h1 class="main-heading">
                    <?php echo e(s('hero_name_first')); ?>
                    <span><?php echo e(s('hero_name_last')); ?></span>
                </h1>
                <nav class="menu-info" aria-label="Sections">
                    <ul>
                        <?php foreach ($nav as $id => $item): ?>
                            <li>
                                <a href="#<?php echo e($id); ?>">
                                    <i class="fas <?php echo e($item['icon']); ?> menu-icon" aria-hidden="true"></i>
                                    <span class="menu-label"><?php echo e($item['label']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
            <div class="main-right">
