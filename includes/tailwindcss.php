<?php 

// function wp_movies_enqueue_styles() {
//     // Enqueue the src/output.css file with cache busting
//     wp_enqueue_style('wp-movies-style', get_template_directory_uri() . '/src/output.css', array(), filemtime(get_template_directory() . '/src/output.css'), 'all');
// }

// add_action('wp_enqueue_scripts', 'wp_movies_enqueue_styles');

function wp_movies_enqueue_scripts() {
    wp_enqueue_style(
        'wp-movies-style',
        get_template_directory_uri() . '/dist/style.css',
        [],
        filemtime(get_template_directory() . '/dist/output.css') // force reload on update
    );
}
add_action('wp_enqueue_scripts', 'wp_movies_enqueue_scripts');