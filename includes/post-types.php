<?php 

// Movies Post Type Registration
function register_movie_cpt() {
    register_post_type('movie', [
        'labels' => [
            'name' => 'Movies',
            'singular_name' => 'Movie',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-editor-video',
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'movies'],
        'show_in_rest' => true, // important for ACF compatibility
    ]);
}
add_action('init', 'register_movie_cpt');

// Register custom post type "Actor"
function register_actor_post_type() {
    register_post_type('actor', array(
        'labels' => array(
            'name' => 'Actors',
            'singular_name' => 'Actor'
        ),
        'public' => true,
         'has_archive' => true,
          'menu_icon' => 'dashicons-universal-access-alt',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => ['slug' => 'actors'],
        'show_in_rest' => true,
    ));
}
add_action('init', 'register_actor_post_type');

