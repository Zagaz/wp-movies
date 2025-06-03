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
  //todo
  $api_key = '9facf375ac53c66a77dfa59841360240';

  // Fetch movie details
  $movie_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language=en-US");
  $movie_data = json_decode(wp_remote_retrieve_body($movie_response), true);

 
  if (empty($movie_data['title'])) {
    return;
  }


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
 
  //The cast
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

// using the $movie_data['id'] fetch api actors

  $actors_response = wp_remote_get( "https://api.themoviedb.org/3/movie/{$movie_data['id']}/credits?api_key={$api_key}&language=en-US");
  $actors_data = json_decode(wp_remote_retrieve_body($actors_response), true);
  $actor_ids = [];
  // $actors_data["cast"][0]['id'] ;
  // make a list onçy with the actor ids
  if (!empty($actors_data['cast'])) {
    foreach ($actors_data['cast'] as $actor) {
      $actor_ids[] = $actor['id']; // Collect unique actor IDs
      // Create or update the actor post
      $actor_post_id = wp_insert_post([
        'post_title'   => $actor['name'],
        'post_type'    => 'actor',
        'post_status'  => 'publish',
      ]);

      if (is_wp_error($actor_post_id)) continue;

      // Save the actor's id list on the movie post
      $actor_ids[] = $actor_post_id;
    }
  }

  echo '<pre>';
  echo "Actor IDs for movie ID {$movie_id}:";
  var_dump($actor_ids);
  echo '</pre>';
  // Save the Actors ids on the movie post
  update_field('actors', $actor_ids, $movie_post_id);



    // update_field('profile_path', $actor_data['profile_path'] ?? '', $actor_id); // Image URL
    // update_field('birthday', $actor_data['birthday'] ?? '', $actor_id); // Date Picker
    // update_field('deathday', $actor_data['deathday'] ?? '', $actor_id); // Text
    // update_field('place_of_birth', $actor_data['place_of_birth'] ?? '', $actor_id); // Text 
    // update_field('homepage', $actor_data['homepage'] ?? '', $actor_id); // Text
    // update_field('popularity', $actor_data['popularity'] ?? '', $actor_id); // Number




  // Link actors to the movie
}



