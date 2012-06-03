<?php

$actions_meta_box = array(
    'id' => 'actions-meta-box',
    'title' => 'About action',
    'page' => 'portfolio',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Date',
            'desc' => 'Set a date (yyyy-mm-dd).',
            'id' => 'start_date',
            'type' => 'date',
            'std' => ''
        ),
        array(
            'name' => 'Hour',
            'desc' => 'Set an hour (hh:mm).',
            'id' => 'end_time',
            'type' => 'date',
            'std' => ''
        ),
        array(
            'name' => 'Check',
            'desc' => 'Click me',
            'id' => 'pin',
            'type' => 'checkbox',
            'std' => '1'
        ),
        array(
            'name' => 'Text',
            'desc' => 'Text.',
            'id' => 'additional',
            'type' => 'text',
            'std' => '1'
        ),
        array(
            'name' => 'meta_box',
            'id' => 'meta_box',
            'type' => 'hidden',
            'std' => 'actions_meta_box',
            'hide' => true
        )
    )
);

// Add meta box
function custom_actions_add_box() {
    global $actions_meta_box;
    add_meta_box($actions_meta_box['id'], $actions_meta_box['title'], 'default_meta_show_box', $actions_meta_box['page'], $actions_meta_box['context'], $actions_meta_box['priority'],$actions_meta_box['fields']);
}

add_action('admin_menu', 'custom_actions_add_box');
