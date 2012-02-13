<?php
//@todo make this reusable
$prefix = 'smc__';

//@todo comments here
function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

/**
 * simple breadcrumb generator for particular case of curl
 */
function the_breadcrumb() {
    global $post;
    echo '<div class="breadcrumbs">';
    if (!is_home()) {
        echo '<a href="';
        echo get_option('home');
        echo '">acasă</a> » ';
        if($post->post_type){
            echo '<a href="'.get_bloginfo('url').'/'.$post->post_type.'">';
            echo $post->post_type;
            echo '</a>';
        }
        if (is_category() || is_single()) {
            the_category();
            if (is_single()) {
                echo " » ";
                the_title();
            }
        } elseif (is_page()) {
            echo the_title();
        }
    }
    echo '</div>';
}

function the_backbutton($string_to_show){
    global $post;
    // echo '<a class="read-more alignright" href="'.get_bloginfo('url').'/'.$post->post_type.'">'.$string_to_show.'</a>';
    echo '<a class="read-more alignright" href="'.get_bloginfo('url').'/portofoliu">'.$string_to_show.'</a>';
}

/**
 * simple query builder for additional texts in template files
 */
function getAdditionalData($identifier,$post_type){
    $args = array(
        'meta_key' => 'page',
        'meta_value' => $identifier,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page'=> 1
    );
    $additional_data_query = new WP_Query($args);
    return $additional_data_query;
}


// rework gallery :P

add_shortcode('gallery','gallery_bypass');

function gallery_bypass($attr){
    global $post, $wp_locale;

    $output = gallery_shortcode($attr);

    //remove link
    if($attr['link'] == "none") {
        $output = preg_replace(array('/<a[^>]*>/', '/<\/a>/'), '', $output);
    }

    return $output;
}

// remove default style for gallery
add_filter('gallery_style',
    create_function(
        '$simple-gallery',
        'return preg_replace("#<style type=\'text/css\'>(.*?)</style>style>#s", "", $css);'
    )
);
