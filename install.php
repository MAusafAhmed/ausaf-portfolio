<?php
/* =============================================================
   INSTALLER  --  run once: http://portfolio.test/install.php

   It does three things:
     1. Creates the database (if it does not exist)
     2. Creates the tables (if they do not exist)
     3. Imports the old JSON data (projects + messages) and
        seeds the site's default content

   Running it again deletes nothing -- whatever already exists is
   left alone. Delete this file once the site goes live.
   ============================================================= */

require_once __DIR__ . '/api/config.php';

$log = array();
$fatal = null;

function step(&$log, $text, $ok = true)
{
    $log[] = array('text' => $text, 'ok' => $ok);
}

try {
    /* ---------- 1. database ---------- */

    $rootDsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    $pdo = new PDO($rootDsn, DB_USER, DB_PASS, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));

    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    step($log, 'Database `' . DB_NAME . '` is ready');

    $pdo->exec('USE `' . DB_NAME . '`');
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    /* ---------- 2. tables ---------- */

    $tables = array(

        'settings' => "CREATE TABLE IF NOT EXISTS settings (
            `key`   VARCHAR(64) NOT NULL PRIMARY KEY,
            `value` TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'categories' => "CREATE TABLE IF NOT EXISTS categories (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(60) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            UNIQUE KEY uniq_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'projects' => "CREATE TABLE IF NOT EXISTS projects (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            title       VARCHAR(120) NOT NULL,
            category_id INT NULL,
            image       VARCHAR(255) NOT NULL DEFAULT '',
            link        VARCHAR(300) NOT NULL DEFAULT '',
            sort_order  INT NOT NULL DEFAULT 0,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_cat (category_id),
            CONSTRAINT fk_project_cat FOREIGN KEY (category_id)
                REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'skills' => "CREATE TABLE IF NOT EXISTS skills (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            label      VARCHAR(120) NOT NULL,
            icon       VARCHAR(255) NOT NULL DEFAULT '',
            sort_order INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'counters' => "CREATE TABLE IF NOT EXISTS counters (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            `value`    VARCHAR(20) NOT NULL,
            label      VARCHAR(120) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'services' => "CREATE TABLE IF NOT EXISTS services (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            title       VARCHAR(120) NOT NULL,
            description TEXT NULL,
            icon        VARCHAR(255) NOT NULL DEFAULT '',
            sort_order  INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'testimonials' => "CREATE TABLE IF NOT EXISTS testimonials (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            name        VARCHAR(120) NOT NULL,
            designation VARCHAR(120) NOT NULL DEFAULT '',
            avatar      VARCHAR(255) NOT NULL DEFAULT '',
            rating      TINYINT NOT NULL DEFAULT 5,
            quote       TEXT NULL,
            sort_order  INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'resume_items' => "CREATE TABLE IF NOT EXISTS resume_items (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            kind       ENUM('education','experience') NOT NULL,
            period     VARCHAR(60) NOT NULL DEFAULT '',
            title      VARCHAR(160) NOT NULL,
            institute  VARCHAR(160) NOT NULL DEFAULT '',
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_kind (kind)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'messages' => "CREATE TABLE IF NOT EXISTS messages (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(100) NOT NULL,
            email      VARCHAR(150) NOT NULL,
            subject    VARCHAR(150) NOT NULL,
            message    TEXT NOT NULL,
            is_read    TINYINT(1) NOT NULL DEFAULT 0,
            mailed     TINYINT(1) NOT NULL DEFAULT 0,
            ip         VARCHAR(45) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    );

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
    }
    step($log, count($tables) . ' tables are ready');

    /* helper: is the table empty? */
    $isEmpty = function ($table) use ($pdo) {
        $n = $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        return (int) $n === 0;
    };

    /* ---------- 3a. settings ---------- */

    $lorem = 'Lorem ipsum dolor sit amet consectetur. Tellus quis quis ac dui dictum ut '
        . 'adipiscing gravida bibendum. Purus at nibh ut elit turpis ultrices.';

    $defaults = array(
        'site_title'        => 'Portfolio',

        'hero_greeting'     => 'Hi, I am',
        'hero_name_first'   => 'Ausaf',
        'hero_name_last'    => 'Ahmed',

        'nav_about'         => 'About Me',
        'nav_portfolio'     => 'Portfolio',
        'nav_services'      => 'Services',
        'nav_testimonials'  => 'Testimonials',
        'nav_resume'        => 'Resume',
        'nav_contact'       => 'Contact',

        'about_stroke'      => 'About Me',
        'about_label'       => 'About Me',
        'about_heading'     => 'Frontend & CMS Developer',
        'about_para'        => 'Lorem ipsum dolor sit amet consectetur. Tellus quis quis ac dui dictum ut '
            . 'adipiscing gravida bibendum. Purus at nibh ut elit turpis ultrices. Tortor porttitor '
            . 'congue tortor ipsum lacinia tincidunt feugiat volutpat. Libero sed condimentum mauris aliquam.',
        'about_image'       => 'assets/images/author-img.png',

        'portfolio_stroke'  => 'Portfolio',
        'portfolio_label'   => 'Portfolio',
        'portfolio_heading' => 'My Latest Work',
        'portfolio_para'    => $lorem,

        'services_stroke'   => 'Services',
        'services_label'    => 'Services',
        'services_heading'  => 'What I DO',
        'services_para'     => $lorem,

        'testimonials_stroke'  => 'Testimonials',
        'testimonials_label'   => 'Testimonials',
        'testimonials_heading' => 'What People Say',
        'testimonials_para'    => '',

        'resume_stroke'           => 'Resume',
        'resume_label'            => 'Resume',
        'resume_heading'          => 'Education & Experience',
        'resume_para'             => $lorem,
        'resume_education_title'  => 'Education',
        'resume_experience_title' => 'Experience',

        'contact_stroke'   => 'Contact',
        'contact_label'    => 'Get In Touch',
        'contact_heading'  => 'Let’s Connect',
        'contact_para'     => $lorem,
        'contact_phone'    => '+92 (336) 302 8259',
        'contact_email'    => 'ausafahmed824@gmail.com',
        'contact_address'  => 'House-82 Block 8 Azizabad FB Area Karachi',
    );

    $ins = $pdo->prepare('INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)');
    $added = 0;
    foreach ($defaults as $key => $value) {
        $ins->execute(array($key, $value));
        $added += $ins->rowCount();
    }
    step($log, $added ? $added . ' settings added' : 'Settings already present');

    /* ---------- 3b. import from projects.json ---------- */

    $catIds = array();

    if ($isEmpty('categories') || $isEmpty('projects')) {
        $store = array('categories' => array(), 'projects' => array());

        if (file_exists(DATA_FILE)) {
            $raw = json_decode(file_get_contents(DATA_FILE), true);
            if (is_array($raw)) {
                $store = array_merge($store, $raw);
            }
        }

        if (!$store['categories']) {
            $store['categories'] = array('Ecommerce', 'Informative', 'E-Book');
        }

        /* categories */
        $catIns = $pdo->prepare('INSERT IGNORE INTO categories (name, sort_order) VALUES (?, ?)');
        $i = 1;
        foreach ($store['categories'] as $name) {
            $catIns->execute(array($name, $i++));
        }
        foreach ($pdo->query('SELECT id, name FROM categories') as $row) {
            $catIds[$row['name']] = (int) $row['id'];
        }
        step($log, count($catIds) . ' categories imported');

        /* projects */
        if ($isEmpty('projects') && $store['projects']) {
            $projIns = $pdo->prepare(
                'INSERT INTO projects (title, category_id, image, link, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $n = 0;
            foreach ($store['projects'] as $p) {
                $cat = isset($p['category']) ? $p['category'] : '';
                $created = isset($p['created']) && $p['created']
                    ? date('Y-m-d H:i:s', strtotime($p['created']))
                    : date('Y-m-d H:i:s');

                $projIns->execute(array(
                    isset($p['title']) ? $p['title'] : 'Untitled',
                    isset($catIds[$cat]) ? $catIds[$cat] : null,
                    isset($p['image']) ? $p['image'] : '',
                    isset($p['link']) ? $p['link'] : '',
                    isset($p['order']) ? (int) $p['order'] : $n,
                    $created,
                ));
                $n++;
            }
            step($log, $n . ' projects imported from data/projects.json');
        }
    } else {
        step($log, 'Projects and categories already present -- skipped');
    }

    /* ---------- 3c. import from messages.json ---------- */

    if ($isEmpty('messages') && file_exists(MESSAGES_FILE)) {
        $old = json_decode(file_get_contents(MESSAGES_FILE), true);
        if (is_array($old) && $old) {
            $msgIns = $pdo->prepare(
                'INSERT INTO messages (name, email, subject, message, is_read, mailed, ip, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($old as $m) {
                $msgIns->execute(array(
                    isset($m['name']) ? $m['name'] : '',
                    isset($m['email']) ? $m['email'] : '',
                    isset($m['subject']) ? $m['subject'] : '',
                    isset($m['message']) ? $m['message'] : '',
                    !empty($m['read']) ? 1 : 0,
                    !empty($m['mailed']) ? 1 : 0,
                    isset($m['ip']) ? $m['ip'] : '',
                    isset($m['date']) ? date('Y-m-d H:i:s', strtotime($m['date'])) : date('Y-m-d H:i:s'),
                ));
            }
            step($log, count($old) . ' messages imported');
        }
    }

    /* ---------- 3d. seed the remaining content ---------- */

    if ($isEmpty('skills')) {
        $rows = array(
            array('HTML5 & CSS3 (90%)', 'assets/images/html-icon.png'),
            array('Shopify (78%)',      'assets/images/shopify-icon.png'),
            array('Wordpress (88%)',    'assets/images/wordpress-icon.png'),
        );
        $q = $pdo->prepare('INSERT INTO skills (label, icon, sort_order) VALUES (?, ?, ?)');
        foreach ($rows as $i => $r) {
            $q->execute(array($r[0], $r[1], $i + 1));
        }
        step($log, count($rows) . ' skills seeded');
    }

    if ($isEmpty('counters')) {
        $rows = array(
            array('2+',  'Years Of Experience'),
            array('4K+', 'Hours Of Working'),
            array('60+', 'Projects Done'),
        );
        $q = $pdo->prepare('INSERT INTO counters (`value`, label, sort_order) VALUES (?, ?, ?)');
        foreach ($rows as $i => $r) {
            $q->execute(array($r[0], $r[1], $i + 1));
        }
        step($log, count($rows) . ' counters seeded');
    }

    if ($isEmpty('services')) {
        $rows = array(
            array('Wordpress Websites', 'assets/images/wordpress-icon.png'),
            array('Shopify Websites',   'assets/images/shopify-icon.png'),
            array('Custom Websites',    'assets/images/custom-web.png'),
            array('User Testing',       'assets/images/user-testig.png'),
        );
        $q = $pdo->prepare('INSERT INTO services (title, description, icon, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($rows as $i => $r) {
            $q->execute(array($r[0], $lorem, $r[1], $i + 1));
        }
        step($log, count($rows) . ' services seeded');
    }

    if ($isEmpty('testimonials')) {
        $quote = 'Lorem ipsum dolor sit amet consectetur. Lacinia aenean non blandit vitae integer at '
            . 'lacus. Amet et fermentum dictumst lorem in lobortis. Purus turpis pretium sed odio '
            . 'sollicitudin tristique ut. Et arcu enim.';
        $q = $pdo->prepare(
            'INSERT INTO testimonials (name, designation, avatar, rating, quote, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        for ($i = 1; $i <= 4; $i++) {
            $q->execute(array('John Peter', 'CTO Zemfar', 'assets/images/avatar-one.png', 5, $quote, $i));
        }
        step($log, '4 testimonials seeded');
    }

    if ($isEmpty('resume_items')) {
        $rows = array(
            array('education',  '2020 - 2023',    'Bachelors Degree of business',    '@ University of IT'),
            array('education',  '2020 - 2023',    'Bachelors Degree of business',    '@ University of IT'),
            array('education',  '2020 - 2023',    'Bachelors Degree of business',    '@ University of IT'),
            array('experience', '2020 - Present', 'Senior Frontend & CMS Developer', '@ Zemfar'),
            array('experience', '2018 - 2020',    'Senior Cms Developer',            '@ Zemfar'),
            array('experience', '2015 - 2018',    'Cms Developer',                   '@ Zemfar'),
        );
        $q = $pdo->prepare(
            'INSERT INTO resume_items (kind, period, title, institute, sort_order) VALUES (?, ?, ?, ?, ?)'
        );
        $order = array('education' => 0, 'experience' => 0);
        foreach ($rows as $r) {
            $order[$r[0]]++;
            $q->execute(array($r[0], $r[1], $r[2], $r[3], $order[$r[0]]));
        }
        step($log, count($rows) . ' resume entries seeded');
    }
} catch (Throwable $ex) {
    $fatal = $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Installer &middot; Portfolio</title>
    <style>
        body {
            margin: 0;
            background: #121212;
            color: #eee;
            font: 16px/1.7 system-ui, sans-serif;
        }

        .wrap {
            max-width: 680px;
            margin: 70px auto;
            padding: 0 24px;
        }

        h1 {
            font-size: 26px;
            margin: 0 0 6px;
        }

        .sub {
            color: #999;
            margin: 0 0 32px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0 0 32px;
        }

        li {
            padding: 12px 16px;
            background: rgb(61 89 117 / 22%);
            border-radius: 10px;
            margin-bottom: 8px;
        }

        li::before {
            content: '\2713';
            color: #4fbf8b;
            font-weight: 700;
            margin-right: 10px;
        }

        li.bad::before {
            content: '\2717';
            color: #e2576a;
        }

        .box {
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .err {
            background: rgb(226 87 106 / 15%);
            color: #ffb3bd;
        }

        .warn {
            background: rgb(255 187 0 / 12%);
            color: #ffd98a;
        }

        a.btn {
            display: inline-block;
            padding: 14px 28px;
            background: #77A7D4;
            color: #121212;
            text-decoration: none;
            border-radius: 300px;
            font-weight: 700;
            margin-right: 10px;
        }

        code {
            background: rgb(255 255 255 / 8%);
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1>Portfolio installer</h1>
        <p class="sub">Database setup &middot; running this once is enough</p>

        <?php if ($fatal): ?>
            <div class="box err">
                <strong>Stopped:</strong><br>
                <?php echo htmlspecialchars($fatal, ENT_QUOTES, 'UTF-8'); ?>
                <p style="margin:12px 0 0">
                    Is MySQL running? Are <code>DB_USER</code> and <code>DB_PASS</code>
                    correct in <code>api/config.php</code>?
                </p>
            </div>
        <?php endif; ?>

        <ul>
            <?php foreach ($log as $item): ?>
                <li class="<?php echo $item['ok'] ? '' : 'bad'; ?>">
                    <?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (!$fatal): ?>
            <div class="box warn">
                Everything is set up. Delete <code>install.php</code> before the site goes live &mdash;
                otherwise anyone else could run it too.
            </div>

            <a class="btn" href="index.php">Open the site</a>
            <a class="btn" href="admin/">Open the dashboard</a>
        <?php endif; ?>
    </div>
</body>

</html>
