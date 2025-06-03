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

  // Fetch up-coming movie data from TMDB API
  // (`${BASE_URL}/movie/upcoming?api_key=${API_KEY}&language=en-US&page=1`);

  $movie_response = wp_remote_get("https://api.themoviedb.org/3/movie/upcoming?api_key={$api_key}&language=en-US&page=1");

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
 


// Actors

$actors_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie_data['id']}/credits?api_key={$api_key}&language=en-US");
if (is_wp_error($actors_response)) {
    return; // Stop if API call fails
}
$actors_data = json_decode(wp_remote_retrieve_body($actors_response), true);

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
   update_field ('crew_id', $crew_names, $movie_post_id); // Text
   update_field('cast', $cast_names, $movie_post_id); // Text
   update_field('cast_id', $cast_ids, $movie_post_id); // Text

}



