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


  $url = "https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language=en-US";
  $url2 = "https://api.themoviedb.org/3/discover/movie?&primary_release_date.gte=2025-06-03&language=en-US&api_key={$api_key} ";
  $movie_response = wp_remote_get($url2);
  $movie_data = json_decode(wp_remote_retrieve_body($movie_response), true);
  $movies_res = $movie_data['results'] ?? [];

  // just keep the first 10 movies
  $movies = array_slice($movies_res, 0, 10);

  foreach ($movies as $movie) {
    // Defensive: skip if no title or overview
    if (empty($movie['title']) || empty($movie['overview'])) {
      continue;
    }

    // Check if this movie already exists by tmdb_id
    $existing = new WP_Query([
      'post_type'  => 'movie',
      'meta_query' => [
        [
          'key'   => 'tmdb_id',
          'value' => $movie['id'],
        ]
      ],
      'posts_per_page' => 1,
      'fields' => 'ids',
    ]);
    if (!empty($existing->posts)) {
      continue; // Skip if already exists
    }

    // Create the movie post
    $movie_post_id = wp_insert_post([

      'post_title'   => $movie['title'],
      'post_content' => $movie['overview'],
      'post_type'    => 'movie',
      'post_status'  => 'publish',
    ]);

    if (is_wp_error($movie_post_id)) continue;

    // Save the movie's TMDB ID and other details
    update_field('tmdb_id', $movie['id'] ?? '', $movie_post_id);
    update_field('release_date', $movie['release_date'] ?? '', $movie_post_id);
    update_field('poster_url', $movie['poster_path'] ?? '', $movie_post_id);
    update_field('production_companies', $movie['production_companies'] ?? '', $movie_post_id);
    update_field('original_language', $movie['original_language'] ?? '', $movie_post_id);


    $genres_ids = $movie['genre_ids'] ?? [];



    // using this id's fetch the name of the genre and compare to $genres_ids
    $genre_url = "https://api.themoviedb.org/3/genre/movie/list?language=en-US&api_key={$api_key}";
    $genre_response = wp_remote_get($genre_url);
    $genre_data = json_decode(wp_remote_retrieve_body($genre_response), true);
    $genres = [];
    if (!empty($genre_data['genres'])) {
      foreach ($genre_data['genres'] as $genre) {
        if (in_array($genre['id'], $genres_ids)) {
          $genres[] = $genre['name'];
        }
      }
    }
    // Convert genres array to a comma-separated string
    $genres = implode(', ', $genres);
    // Save the genres as a custom field on the movie post
    if (!empty($genres)) {
      update_field('genres', $genres, $movie_post_id); // Text
    } else {
      update_field('genres', 'Not available', $movie_post_id); // Text

    }

    //trailer 
    // url https://api.themoviedb.org/3/movie/movie_id/videos?language=en-US'
    $trailer_url = "https://api.themoviedb.org/3/movie/{$movie['id']}/videos?language=en-US&api_key={$api_key}";
    $trailer_response = wp_remote_get($trailer_url);
    $trailer_data = json_decode(wp_remote_retrieve_body($trailer_response), true);
    $trailer_key = '';
    if (!empty($trailer_data['results'])) {
      foreach ($trailer_data['results'] as $video) {
        if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
          $trailer_key = $video['key'];
          break; // Stop after finding the first trailer
        }
      }
    }

    $trailer_status ='';
    // Save the trailer key as a custom field on the movie post
    if (!empty($trailer_key)) {
      $trailer_status=$trailer_key;
    } else {
      $trailer_status = 'Not available';
    }
    
   update_field('trailer', $trailer_status, $movie_post_id);


    


    // Production Companies
    $production_companies = [];
    if (!empty($movie['production_companies'])) {
      foreach ($movie['production_companies'] as $company) {
        $production_companies[] = $company['name'];
      }
    }
    $production_companies = implode(', ', $production_companies);
    update_field('production_companies', $production_companies, $movie_post_id);

    // Actors
    $actors_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie['id']}/credits?api_key={$api_key}&language=en-US");
    $actors_response['body'] = json_decode($actors_response['body'], true);
    $actors_data =   $actors_response['body'];
    $cast_names = [];
    if (!empty($actors_data['cast'])) {
      // Collect all actor IDs that don't exist yet to fetch their details in batch
      $new_actors = [];
      foreach ($actors_data['cast'] as $actor) {
        // Check if actor already exists by tmdb_actor_id
        $existing_actor = new WP_Query([
          'post_type'      => 'actor',
          'meta_query'     => [
            [
              'key'   => 'tmdb_actor_id',
              'value' => $actor['id'],
            ]
          ],
          'posts_per_page' => 1,
          'fields'         => 'ids',
        ]);

        if (!empty($existing_actor->posts)) {
          $actor_post_id = $existing_actor->posts[0];
        } else {
          // Create the actor post first
          $actor_post_id = wp_insert_post([
            'post_title'   => $actor['name'],
            'post_type'    => 'actor',
            'post_status'  => 'publish',
          ]);
          update_field('tmdb_actor_id', $actor['id'], $actor_post_id); // Text

          // Queue for details fetch
          $new_actors[] = [
            'id' => $actor['id'],
            'post_id' => $actor_post_id,
            'profile_path' => $actor['profile_path'] ?? '',
            'popularity' => $actor['popularity'] ?? '',
          ];
        }

        if (is_wp_error($actor_post_id)) continue;

        // Save the actor's (cast) name and id list on the movie post in the same array 
        $cast_names[] = $actor['name'];
        $cast_ids[] = $actor_post_id;

        // Actors Images
        $url_images = "https://api.themoviedb.org/3/person/287/images?api_key={$api_key}";
        $images_response = wp_remote_get($url_images);
        $images_data = json_decode(wp_remote_retrieve_body($images_response), true);
        $images = $images_data['profiles'] ?? [];

        $images_file_path = [];
        if (!empty($images)) {
          foreach ($images as $image) {

            // Check if the image URL is valid
            if (!empty($image['file_path'])) {
              $images_file_path[] = $image['file_path'];
            }
          }
        }
        // Save the cast images as a custom field on the actors post type
        update_field('images_file_path', $images_file_path, $actor_post_id); // Text

     

      }

      // Fetch details for all new actors (sequentially, as TMDB API does not support batch)
      foreach ($new_actors as $new_actor) {
        $actor_details_response = wp_remote_get("https://api.themoviedb.org/3/person/{$new_actor['id']}?api_key={$api_key}&language=en-US");
        if (is_wp_error($actor_details_response)) {
          continue;
        }
        $actor_details_data = json_decode(wp_remote_retrieve_body($actor_details_response), true);

        // Save additional actor details, using null coalescing to avoid warnings
        update_field('biography', $actor_details_data['biography'] ?? '', $new_actor['post_id']); // Text Area
        update_field('birthday', $actor_details_data['birthday'] ?? '', $new_actor['post_id']); // Date Picker
        update_field('deathday', $actor_details_data['deathday'] ?? '', $new_actor['post_id']); // Date Picker
        update_field('place_of_birth', $actor_details_data['place_of_birth'] ?? '', $new_actor['post_id']); // Text
        update_field('known_for_department', $actor_details_data['known_for_department'] ?? '', $new_actor['post_id']); // Text
        update_field('profile_path', $new_actor['profile_path'], $new_actor['post_id']); // Text
        update_field('popularity', $new_actor['popularity'], $new_actor['post_id']); // Text
        update_field('homepage', $actor_details_data['homepage'] ?? '', $new_actor['post_id']); // Text
      }
    }
    // CREW
    $crew_names = [];
    if (!empty($actors_data['crew'])) {
      foreach ($actors_data['crew'] as $crew_member) {
        $crew_names[] = $crew_member['name'] ?? ''; // Collect unique crew names
        // Create or update the crew post
        $crew_post_id = wp_insert_post([
          'post_title'   => $crew_member['name'],
          'post_type'    => 'actor',
          'post_status'  => 'publish',
        ]);

        if (is_wp_error($crew_post_id)) continue;

        // Save the crew's id list on the movie post
        $crew_names[] = $crew_member['name'];
        $crew_ids[] = $crew_post_id;
      }
    }
    // Save the actor and crew IDs as a custom field on the movie post
    update_field('crew', $crew_ids, $movie_post_id); // Text
    update_field('crew_id', $crew_names, $movie_post_id); // Text
    update_field('cast', $cast_names, $movie_post_id); // Text
    update_field('cast_id', $cast_ids, $movie_post_id); // Text

  } // <-- This closes the foreach ($movies as $movie)
} // <-- ADD THIS to close the function import_movie_with_cast
