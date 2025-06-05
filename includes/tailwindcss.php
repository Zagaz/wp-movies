<?php 

function wp_movies_enqueue_styles() {
    // enquerue the src/output.css file
    wp_enqueue_style('wp-movies-style', get_template_directory_uri() . '/src/output.css', array(), '1.0.0', 'all');
    // avoid cache tailwindcss
    wp_enqueue_style('wp-movies-style-cache-buster', get_template_directory_uri() . '/src/output.css', array(), filemtime(get_template_directory() . '/src/output.css'), 'all');

}

add_action('wp_enqueue_scripts', 'wp_movies_enqueue_styles');

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'tailwindcss',
        get_template_directory_uri() . '/dist/output.css',
        [],
        filemtime(get_template_directory() . '/dist/output.css')
    );
});

