<?php

$book_meta_box = array(
    'id' => 'book-meta-box',
    'title' => 'Book details',
    'page' => 'book',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            //@todo logic for autocompleting the author name
            'name' => 'Author name',
            'desc' => 'Add author name.',
            'id' => 'author-name',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Publish year',
            'desc' => 'yyyy-mm-dd - some browsers support html5 elements better than others',
            'id' => 'book-publish-year',
            //@todo add time/year
            'type' => 'date',
            'std' => ''
        ),
        //this is required for the save function to work properly
        array(
            'name' => 'meta_box',
            'id' => 'meta_box',
            'type' => 'hidden',
            'std' => 'book_meta_box'
        ),
    )
);

// field with autocomplete or select box
add_action('admin_menu', 'book_add_box');

// Add meta box
// after you use this file as template, you have to manually edit the name of the fields $book_meta_box
function book_add_box() {
    global $book_meta_box;
    add_meta_box($book_meta_box['id'], $book_meta_box['title'], 'default_meta_show_box', $book_meta_box['page'], $book_meta_box['context'], $book_meta_box['priority'],$book_meta_box['fields']);
}

add_action('save_post', 'save_data');
