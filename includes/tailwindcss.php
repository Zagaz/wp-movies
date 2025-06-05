<?php 

function wp_movies_enqueue_styles() {
    // Enqueue the src/output.css file with cache busting
    wp_enqueue_style('wp-movies-style', get_template_directory_uri() . '/src/output.css', array(), filemtime(get_template_directory() . '/src/output.css'), 'all');
}

add_action('wp_enqueue_scripts', 'wp_movies_enqueue_styles');

add_action('wp_enqueue_scripts', function() {
    $css_file = get_template_directory() . '/dist/output.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'tailwindcss',
            get_template_directory_uri() . '/dist/output.css',
            [],
            filemtime($css_file)
        );
    }
});

