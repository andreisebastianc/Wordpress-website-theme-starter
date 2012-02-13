<?php

$social_meta_box = array(
    'id' => 'social-meta-box',
    'title' => 'Detalii suplimentare Social Media',
    'page' => 'socialmedia',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'URL',
            'desc' => 'Adaugă adresa web a paginii.',
            'id' => 'url',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'meta_box',
            'id' => 'meta_box',
            'type' => 'hidden',
            'std' => 'social_meta_box',
            'hide' => true
        ),
    )
);

// Add meta box
function smc_social_add_box() {
    global $social_meta_box;
    add_meta_box($social_meta_box['id'], $social_meta_box['title'], 'default_meta_show_box', $social_meta_box['page'], $social_meta_box['context'], $social_meta_box['priority'], $social_meta_box['fields']);
}

add_action('admin_menu', 'smc_social_add_box');
