<?php 
function register_movie_cpt() {
    register_post_type('movie', [
        'labels' => [
            'name' => 'Movies',
            'singular_name' => 'Movie',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-video-alt2',
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'movies'],
        'show_in_rest' => true, // important for ACF compatibility
    ]);
}
add_action('init', 'register_movie_cpt');
