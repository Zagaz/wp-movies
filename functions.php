<?php 

function wp_movies_enqueue_styles() {
    // enquerue the src/output.css file
    wp_enqueue_style('wp-movies-style', get_template_directory_uri() . '/src/output.css', array(), '1.0.0', 'all');
}

add_action('wp_enqueue_scripts', 'wp_movies_enqueue_styles');

