<?php
/* Public JSON endpoint.

   The website no longer uses this -- projects are rendered directly by PHP
   (includes/sections/portfolio.php). It is kept only in case this data is
   ever needed somewhere else. */

require_once __DIR__ . '/lib.php';

header('Cache-Control: no-store');

$out = array();
foreach (projects_all() as $p) {
    $out[] = array(
        'id'       => (int) $p['id'],
        'title'    => $p['title'],
        'category' => $p['category'],
        'image'    => $p['image'],
        'link'     => $p['link'],
    );
}

$categories = array();
foreach (categories_all() as $c) {
    $categories[] = $c['name'];
}

json_out(array(
    'ok'         => true,
    'categories' => $categories,
    'projects'   => $out,
));
