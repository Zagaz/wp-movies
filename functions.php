<?php
// functions.php

// Dynamically load from /includes
$includes = glob(__DIR__ . '/includes/*.php');
foreach ($includes as $file) {
  require_once $file;
}



// Function that imports a movie and its actors
function import_movie_with_cast($movie_id)
{
  $api_key = '9facf375ac53c66a77dfa59841360240';

  // Fetch movie details
  $movie_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language=en-US");
  $movie_data = json_decode(wp_remote_retrieve_body($movie_response), true);

  if (empty($movie_data['title'])) {
    return;
  }

  //exit;

  // Create the movie post
  $movie_post_id = wp_insert_post([
    'post_title'   => $movie_data['title'],
    'post_content' => $movie_data['overview'],
    'post_type'    => 'movie',
    'post_status'  => 'publish',
  ]);


  if (is_wp_error($movie_post_id)) return;

  // Save on the custom fields using ACF
  update_field('tmdb_id', $movie_data['id'], $movie_post_id); // Text
  update_field('release_date', $movie_data['release_date'], $movie_post_id); // Date Picker
  update_field('poster_url', $movie_data['poster_path'], $movie_post_id); // Image URL
  update_field('production_companies', $movie_data['production_companies'], $movie_post_id); // text 
  update_field('original_language', $movie_data['original_language'], $movie_post_id); // Text
 
  // Genres
  $genres = [];
  if (!empty($movie_data['genres'])) {
    foreach ($movie_data['genres'] as $genre) {
      $genres[] = $genre['name'];
    }
  }
  $genres = implode(', ', $genres);
  update_field('genres', $genres, $movie_post_id);

  // Production Companies
  $production_companies = [];
  if (!empty($movie_data['production_companies'])) {
    foreach ($movie_data['production_companies'] as $company) {
      $production_companies[] = $company['name'];
    }
  }
  $production_companies = implode(', ', $production_companies);
  update_field('production_companies', $production_companies, $movie_post_id);
 
  // Cast
  $cast_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie_id}/credits?api_key={$api_key}&language=en-US");
  $cast_data = json_decode(wp_remote_retrieve_body($cast_response), true);
  $cast_list = [];
  if (!empty($cast_data['cast'])) {
    foreach ($cast_data['cast'] as $actor) {
      $cast_list[] = [
        'name' => $actor['name'],
      ];
    }
  }

  $cast_list = implode(', ', array_map(function ($actor) {
    return $actor['name'];
  }, $cast_list));
  // Save the cast as a custom field
  update_field('cast', $cast_list, $movie_post_id);

// Actors
  $actor_ids = [];

  foreach (array_slice($cast_data['cast'], 0, 5) as $actor) {
    $actor_name = $actor['name'];

    // Check if actor already exists using WP_Query
    $actor_query = new WP_Query([
      'post_type'      => 'actor',
      'title'          => $actor_name,
      'posts_per_page' => 1,
      'fields'         => 'ids',
    ]);

    if (!empty($actor_query->posts)) {
      $actor_id = $actor_query->posts[0];
    } else {
      // Create new actor post
      $actor_id = wp_insert_post([
        'post_title'  => $actor_name,
        'post_type'   => 'actor',
        'post_status' => 'publish'
      ]);
    }

    $actor_ids[] = $actor_id;
  }

  // Link actors to the movie
  update_field('actors', $actor_ids, $movie_post_id);
}


// Function that fetches upcoming movies and calls the import function
function import_upcoming_movies_with_cast()
{
  $api_key = '9facf375ac53c66a77dfa59841360240';

  $response = wp_remote_get("https://api.themoviedb.org/3/movie/upcoming?api_key={$api_key}&language=en-US&page=1");
  $data = json_decode(wp_remote_retrieve_body($response), true);



  if (empty($data['results'])) {
    return;
  }

  $movies = array_slice($data['results'], 0, 10);

  foreach ($movies as $movie) {
    import_movie_with_cast($movie['id']);
  }
}

add_action('init', function () {
  if (isset($_GET['importar']) && $_GET['importar'] === 'filmes') {
    import_upcoming_movies_with_cast();
    echo 'Import completed.';
    exit;
  }
});
