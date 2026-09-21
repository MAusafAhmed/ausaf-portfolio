<?php
/* =============================================================
   Every POST action in the admin panel.
   Does the work, then redirects back to the dashboard.
   ============================================================= */

require_once __DIR__ . '/../api/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

check_csrf();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$back   = isset($_POST['back']) ? $_POST['back'] : 'dashboard.php';

/* Only ever redirect back into our own dashboard */
if (!preg_match('#^dashboard\.php(\?[\w=&%.\-]*)?$#', $back)) {
    $back = 'dashboard.php';
}

/* ---------- small helpers ---------- */

function post($key, $default = '')
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/* Delete a row along with its uploaded image */
function delete_with_image($table, $id, $imageColumn, $label)
{
    $row = db_one('SELECT * FROM `' . $table . '` WHERE id = ?', array($id));

    if (!$row) {
        flash('error', $label . ' not found.');
        return;
    }

    if ($imageColumn && !empty($row[$imageColumn])) {
        delete_upload($row[$imageColumn]);
    }

    db_delete($table, $id);
    flash('success', $label . ' deleted.');
}

/* Save a simple list item (title + text + optional image).
   $fields  = column => value
   $imgCol  = which column holds the image path (null = no image)
   $file    = name of the file input in the form
   $folder  = uploads/<folder> */
function save_item($table, $id, $fields, $label, $imgCol = null, $file = null, $folder = 'content')
{
    $upload = array('ok' => true, 'path' => '');

    if ($imgCol && $file) {
        $upload = handle_image_upload($file, $folder);
        if (!$upload['ok']) {
            flash('error', $upload['error']);
            return;
        }
    }

    if ($id) {
        $old = db_one('SELECT * FROM `' . $table . '` WHERE id = ?', array($id));
        if (!$old) {
            flash('error', $label . ' not found.');
            return;
        }

        /* a new image was uploaded -- remove the old one */
        if ($imgCol && $upload['path'] !== '') {
            delete_upload($old[$imgCol]);
            $fields[$imgCol] = $upload['path'];
        }

        db_save($table, $fields, $id);
        flash('success', $label . ' updated.');
        return;
    }

    if ($imgCol) {
        $fields[$imgCol] = $upload['path'];
    }
    $fields['sort_order'] = next_order($table);

    db_save($table, $fields);
    flash('success', $label . ' added.');
}

/* ============================================================= */

switch ($action) {

    /* ---------------- PROJECTS ---------------- */

    case 'project_save': {
        $id         = (int) post('id');
        $title      = clean_text(post('title'), 120);
        $categoryId = (int) post('category_id');
        $link       = clean_url(post('link'));
        $order      = (int) post('order');

        if ($title === '') {
            flash('error', 'A project title is required.');
            break;
        }

        $cat = db_one('SELECT id FROM categories WHERE id = ?', array($categoryId));
        if (!$cat) {
            flash('error', 'Please select a category.');
            break;
        }

        $upload = handle_image_upload('image', 'projects');
        if (!$upload['ok']) {
            flash('error', $upload['error']);
            break;
        }

        $data = array(
            'title'       => $title,
            'category_id' => $categoryId,
            'link'        => $link,
        );

        /* --- EDIT --- */
        if ($id) {
            $old = db_one('SELECT * FROM projects WHERE id = ?', array($id));
            if (!$old) {
                flash('error', 'Project not found.');
                break;
            }

            if ($upload['path'] !== '') {
                delete_upload($old['image']);
                $data['image'] = $upload['path'];
            }
            $data['sort_order'] = $order ? $order : (int) $old['sort_order'];

            db_save('projects', $data, $id);
            flash('success', 'Project updated.');
            break;
        }

        /* --- ADD --- */
        if ($upload['path'] === '') {
            flash('error', 'An image is required for a new project.');
            break;
        }

        $data['image']      = $upload['path'];
        $data['sort_order'] = $order ? $order : next_order('projects');
        $data['created_at'] = date('Y-m-d H:i:s');

        db_save('projects', $data);
        flash('success', 'Project added.');
        break;
    }

    case 'project_delete': {
        delete_with_image('projects', (int) post('id'), 'image', 'Project');
        break;
    }

    /* ---------------- CATEGORIES ---------------- */

    case 'category_add': {
        $name = clean_text(post('name'), 60);

        if ($name === '') {
            flash('error', 'Please enter a category name.');
            break;
        }
        if (strcasecmp($name, 'All') === 0) {
            flash('error', 'The "All" tab is created automatically, there is no need to add it.');
            break;
        }
        if (db_one('SELECT id FROM categories WHERE name = ?', array($name))) {
            flash('error', 'That category already exists.');
            break;
        }

        db_save('categories', array('name' => $name, 'sort_order' => next_order('categories')));
        flash('success', 'Category "' . $name . '" added.');
        break;
    }

    case 'category_rename': {
        $id   = (int) post('id');
        $name = clean_text(post('new'), 60);

        if ($name === '') {
            flash('error', 'Please enter a valid name.');
            break;
        }
        if (!db_one('SELECT id FROM categories WHERE id = ?', array($id))) {
            flash('error', 'Category not found.');
            break;
        }

        $clash = db_one('SELECT id FROM categories WHERE name = ? AND id <> ?', array($name, $id));
        if ($clash) {
            flash('error', 'A category with that name already exists.');
            break;
        }

        db_save('categories', array('name' => $name), $id);
        flash('success', 'Category renamed.');
        break;
    }

    case 'category_delete': {
        $id = (int) post('id');

        $used = db_one('SELECT COUNT(*) AS n FROM projects WHERE category_id = ?', array($id));
        if ($used && (int) $used['n'] > 0) {
            flash('error', 'Delete or move this category\'s projects first.');
            break;
        }

        db_delete('categories', $id);
        flash('success', 'Category deleted.');
        break;
    }

    /* ---------------- ABOUT: skills + counters ---------------- */

    case 'skill_save': {
        $label = clean_text(post('label'), 120);
        if ($label === '') {
            flash('error', 'Please enter a skill name.');
            break;
        }
        save_item('skills', (int) post('id'), array('label' => $label), 'Skill', 'icon', 'icon', 'skills');
        break;
    }

    case 'skill_delete': {
        delete_with_image('skills', (int) post('id'), 'icon', 'Skill');
        break;
    }

    case 'counter_save': {
        $value = clean_text(post('value'), 20);
        $label = clean_text(post('label'), 120);

        if ($value === '' || $label === '') {
            flash('error', 'Please enter both the number and the label.');
            break;
        }

        save_item('counters', (int) post('id'), array('value' => $value, 'label' => $label), 'Counter');
        break;
    }

    case 'counter_delete': {
        delete_with_image('counters', (int) post('id'), null, 'Counter');
        break;
    }

    /* ---------------- SERVICES ---------------- */

    case 'service_save': {
        $title = clean_text(post('title'), 120);
        if ($title === '') {
            flash('error', 'A service title is required.');
            break;
        }

        save_item(
            'services',
            (int) post('id'),
            array('title' => $title, 'description' => clean_text(post('description'), 2000)),
            'Service',
            'icon',
            'icon',
            'services'
        );
        break;
    }

    case 'service_delete': {
        delete_with_image('services', (int) post('id'), 'icon', 'Service');
        break;
    }

    /* ---------------- TESTIMONIALS ---------------- */

    case 'testimonial_save': {
        $name = clean_text(post('name'), 120);
        if ($name === '') {
            flash('error', 'Please enter the client\'s name.');
            break;
        }

        $rating = (int) post('rating', 5);
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;

        save_item(
            'testimonials',
            (int) post('id'),
            array(
                'name'        => $name,
                'designation' => clean_text(post('designation'), 120),
                'rating'      => $rating,
                'quote'       => clean_text(post('quote'), 2000),
            ),
            'Testimonial',
            'avatar',
            'avatar',
            'testimonials'
        );
        break;
    }

    case 'testimonial_delete': {
        delete_with_image('testimonials', (int) post('id'), 'avatar', 'Testimonial');
        break;
    }

    /* ---------------- RESUME ---------------- */

    case 'resume_save': {
        $kind  = post('kind') === 'experience' ? 'experience' : 'education';
        $title = clean_text(post('title'), 160);

        if ($title === '') {
            flash('error', 'Please enter a title (the degree or job title).');
            break;
        }

        save_item(
            'resume_items',
            (int) post('id'),
            array(
                'kind'      => $kind,
                'period'    => clean_text(post('period'), 60),
                'title'     => $title,
                'institute' => clean_text(post('institute'), 160),
            ),
            'Resume entry'
        );
        break;
    }

    case 'resume_delete': {
        delete_with_image('resume_items', (int) post('id'), null, 'Resume entry');
        break;
    }

    /* ---------------- SETTINGS (all text content) ---------------- */

    case 'settings_save': {
        $incoming = post('settings', array());
        if (!is_array($incoming)) {
            $incoming = array();
        }

        /* Only keys that already exist in the table -- keeps junk out */
        $known = settings_all();
        $pairs = array();

        foreach ($incoming as $key => $value) {
            if (array_key_exists($key, $known)) {
                $pairs[$key] = clean_text($value, 5000);
            }
        }

        /* The About photo is uploaded separately */
        $upload = handle_image_upload('about_image_file', 'content');
        if (!$upload['ok']) {
            flash('error', $upload['error']);
            break;
        }
        if ($upload['path'] !== '') {
            $old = s('about_image');
            if (strpos($old, 'uploads/') === 0) {
                delete_upload($old);
            }
            $pairs['about_image'] = $upload['path'];
        }

        if (!$pairs) {
            flash('error', 'Nothing was changed.');
            break;
        }

        settings_save($pairs);
        flash('success', 'Changes saved.');
        break;
    }

    /* ---------------- MESSAGES ---------------- */

    case 'message_delete': {
        db_delete('messages', (int) post('id'));
        flash('success', 'Message deleted.');
        break;
    }

    case 'messages_mark_read': {
        db_run('UPDATE messages SET is_read = 1 WHERE is_read = 0');
        flash('success', 'All messages marked as read.');
        break;
    }

    default:
        flash('error', 'Unknown action.');
}

header('Location: ' . $back);
exit;
