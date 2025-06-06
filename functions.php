<?php
// functions.php

// Dynamically load from /includes
$includes = glob(__DIR__ . '/includes/*.php');
foreach ($includes as $file) {
  require_once $file;
}
/**
 * Define TMDB_API_KEY in wp-config.php for security and easier management.
 * Example: define('TMDB_API_KEY', 'your_actual_api_key_here');
 */
if ( ! defined( 'TMDB_API_KEY' ) ) {
    define( 'TMDB_API_KEY', '9facf375ac53c66a77dfa59841360240' );
}

/**
 * Imports upcoming movies and their cast/crew details from TMDB.
 * Fetches a list of upcoming movies and then processes each one.
 */
function import_upcoming_movies_and_cast() {
    $api_key = TMDB_API_KEY;

    // 1. Fetch list of upcoming movie IDs (or basic data)
    $discover_url = "https://api.themoviedb.org/3/discover/movie?primary_release_date.gte=" . date('Y-m-d') . "&language=en-US&sort_by=popularity.desc&api_key={$api_key}";
    $discover_response = wp_remote_get($discover_url);

    if (is_wp_error($discover_response) || wp_remote_retrieve_response_code($discover_response) !== 200) {
        error_log('TMDB Import: Failed to fetch upcoming movies. Response: ' . (is_wp_error($discover_response) ? $discover_response->get_error_message() : wp_remote_retrieve_response_code($discover_response) . ' - ' . wp_remote_retrieve_response_message($discover_response)));
        return;
    }
    $discover_data = json_decode(wp_remote_retrieve_body($discover_response), true);
    $upcoming_movies_partial = $discover_data['results'] ?? [];

    if (empty($upcoming_movies_partial)) {
        error_log('TMDB Import: No upcoming movies found to import.');
        return;
    }

    // Limit the number of movies to import, e.g., top 10
    // $upcoming_movies_partial = array_slice($upcoming_movies_partial, 0, 10);

    foreach ($upcoming_movies_partial as $partial_movie_data) {
        $tmdb_movie_id = $partial_movie_data['id'];

        // 2. Check if movie already exists by tmdb_id
        $existing_movie_query = new WP_Query([
            'post_type'  => 'movie',
            'meta_key'   => 'tmdb_id',
            'meta_value' => $tmdb_movie_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ]);
        if (!empty($existing_movie_query->posts)) {
            // error_log("TMDB Import: Movie with TMDB ID {$tmdb_movie_id} already exists. Skipping.");
            continue;
        }

        // 3. Fetch full movie details including credits, videos, similar, alternative_titles
        $movie_detail_url = "https://api.themoviedb.org/3/movie/{$tmdb_movie_id}?api_key={$api_key}&language=en-US&append_to_response=credits,videos,similar,alternative_titles";
        $movie_detail_response = wp_remote_get($movie_detail_url);

        if (is_wp_error($movie_detail_response) || wp_remote_retrieve_response_code($movie_detail_response) !== 200) {
            error_log("TMDB Import: Failed to fetch details for movie TMDB ID {$tmdb_movie_id}. Skipping.");
            continue;
        }
        $movie_data = json_decode(wp_remote_retrieve_body($movie_detail_response), true);

        if (empty($movie_data['title']) || empty($movie_data['overview'])) {
            error_log("TMDB Import: Movie TMDB ID {$tmdb_movie_id} ('{$movie_data['title']}') has no title or overview. Skipping.");
            continue;
        }
        
        // Fallback: Check by title (less reliable)
        $existing_by_title = new WP_Query([
            'post_type'      => 'movie',
            'title'          => $movie_data['title'],
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);
        if (!empty($existing_by_title->posts)) {
            // error_log("TMDB Import: Movie with title '{$movie_data['title']}' already exists (fallback check). Skipping TMDB ID {$tmdb_movie_id}.");
            continue; 
        }

        // 4. Create Movie Post
        $movie_post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($movie_data['title']),
            'post_content' => wp_kses_post($movie_data['overview']),
            'post_type'    => 'movie',
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($movie_post_id)) {
            error_log("TMDB Import: Failed to insert movie '{$movie_data['title']}': " . $movie_post_id->get_error_message());
            continue;
        }

        // 5. Update Movie ACF Fields
        update_field('tmdb_id', $movie_data['id'], $movie_post_id);
        update_field('release_date', $movie_data['release_date'] ?? '', $movie_post_id);
        update_field('poster_url', $movie_data['poster_path'] ?? '', $movie_post_id);
        update_field('original_language', $movie_data['original_language'] ?? '', $movie_post_id);
        update_field('movie_popularity', $movie_data['popularity'] ?? '', $movie_post_id);

        // Production Companies
        $production_companies_names = [];
        if (!empty($movie_data['production_companies'])) {
            foreach ($movie_data['production_companies'] as $company) {
                $production_companies_names[] = sanitize_text_field($company['name']);
            }
        }
        update_field('production_companies', !empty($production_companies_names) ? implode(', ', $production_companies_names) : '', $movie_post_id);

        // Genres
        $genre_names = [];
        if (!empty($movie_data['genres'])) {
            foreach ($movie_data['genres'] as $genre) {
                $genre_names[] = sanitize_text_field($genre['name']);
            }
        }
        update_field('genres', !empty($genre_names) ? implode(', ', $genre_names) : '', $movie_post_id);
        
        // Trailer
        $trailer_key = '';
        if (!empty($movie_data['videos']['results'])) {
            foreach ($movie_data['videos']['results'] as $video) {
                if (isset($video['type'], $video['site'], $video['key']) && strtolower($video['type']) === 'trailer' && strtolower($video['site']) === 'youtube') {
                    $trailer_key = sanitize_text_field($video['key']);
                    break;
                }
            }
        }
        update_field('trailer', $trailer_key ?: '', $movie_post_id);

        // Similar Movies (titles) - limited to 5
        $similar_movie_titles = [];
        if (!empty($movie_data['similar']['results'])) {
            foreach (array_slice($movie_data['similar']['results'], 0, 5) as $similar_movie) {
                if (!empty($similar_movie['title'])) {
                    $similar_movie_titles[] = sanitize_text_field($similar_movie['title']);
                }
            }
        }
        update_field('similar_movies', !empty($similar_movie_titles) ? implode(', ', $similar_movie_titles) : '', $movie_post_id);

        // Alternative Titles - limited to 5 unique titles
        $alt_titles = [];
        if (!empty($movie_data['alternative_titles']['titles'])) {
            foreach (array_slice($movie_data['alternative_titles']['titles'], 0, 10) as $alt_title) { // Check more to find unique ones
                if (!empty($alt_title['title']) && count($alt_titles) < 5) {
                    $alt_titles[] = sanitize_text_field($alt_title['title']);
                }
            }
        }
        update_field('alternative_titles', !empty($alt_titles) ? implode(', ', array_unique($alt_titles)) : '', $movie_post_id);

        // 6. Process Cast
        $cast_credits = $movie_data['credits']['cast'] ?? [];
        $processed_cast_names = [];
        $processed_cast_post_ids = [];

        foreach (array_slice($cast_credits, 0, 15) as $actor_credit) { // Limit number of cast members processed
            if (empty($actor_credit['id']) || empty($actor_credit['name'])) {
                continue;
            }

            $actor_post_id = null;
            // Check if actor exists by tmdb_actor_id
            $existing_actor_by_tmdb_id_query = new WP_Query([
                'post_type'  => 'actor', 'meta_key'   => 'tmdb_actor_id',
                'meta_value' => $actor_credit['id'], 'posts_per_page' => 1, 'fields' => 'ids',
            ]);
            if (!empty($existing_actor_by_tmdb_id_query->posts)) {
                $actor_post_id = $existing_actor_by_tmdb_id_query->posts[0];
            } else {
                // Check by name as a fallback
                $existing_actor_by_name_query = new WP_Query([
                    'post_type' => 'actor', 'title' => sanitize_text_field($actor_credit['name']),
                    'posts_per_page' => 1, 'fields' => 'ids',
                ]);
                if (!empty($existing_actor_by_name_query->posts)) {
                    $actor_post_id = $existing_actor_by_name_query->posts[0];
                    // If found by name, update their TMDB ID if it's missing or different
                    update_field('tmdb_actor_id', $actor_credit['id'], $actor_post_id);
                }
            }

            if (!$actor_post_id) { // Actor does not exist, create them
                $actor_post_id = wp_insert_post([
                    'post_title'   => sanitize_text_field($actor_credit['name']),
                    'post_type'    => 'actor',
                    'post_status'  => 'publish',
                ]);
                if (is_wp_error($actor_post_id)) {
                    error_log("TMDB Import: Failed to insert actor '{$actor_credit['name']}': " . $actor_post_id->get_error_message());
                    continue;
                }
                update_field('tmdb_actor_id', $actor_credit['id'], $actor_post_id);

                // Fetch full actor details + images
                $actor_detail_url = "https://api.themoviedb.org/3/person/{$actor_credit['id']}?api_key={$api_key}&language=en-US&append_to_response=images";
                $actor_detail_response = wp_remote_get($actor_detail_url);
                if (!is_wp_error($actor_detail_response) && wp_remote_retrieve_response_code($actor_detail_response) === 200) {
                    $actor_details_data = json_decode(wp_remote_retrieve_body($actor_detail_response), true);
                    update_field('biography', !empty($actor_details_data['biography']) ? wp_kses_post($actor_details_data['biography']) : '', $actor_post_id);
                    update_field('birthday', $actor_details_data['birthday'] ?? '', $actor_post_id);
                    update_field('deathday', $actor_details_data['deathday'] ?? '', $actor_post_id);
                    update_field('place_of_birth', !empty($actor_details_data['place_of_birth']) ? sanitize_text_field($actor_details_data['place_of_birth']) : '', $actor_post_id);
                    update_field('known_for_department', !empty($actor_details_data['known_for_department']) ? sanitize_text_field($actor_details_data['known_for_department']) : '', $actor_post_id);
                    update_field('profile_path', $actor_details_data['profile_path'] ?? ($actor_credit['profile_path'] ?? ''), $actor_post_id);
                    update_field('popularity', $actor_details_data['popularity'] ?? ($actor_credit['popularity'] ?? ''), $actor_post_id);
                    update_field('homepage', !empty($actor_details_data['homepage']) ? esc_url_raw($actor_details_data['homepage']) : '', $actor_post_id);

                    $actor_image_paths = [];
                    if (!empty($actor_details_data['images']['profiles'])) {
                        foreach (array_slice($actor_details_data['images']['profiles'], 0, 10) as $image_profile) { // Limit images
                            if (!empty($image_profile['file_path'])) {
                                $actor_image_paths[] = $image_profile['file_path'];
                            }
                        }
                    }
                    update_field('images_file_path', $actor_image_paths, $actor_post_id);
                } else {
                     // Fallback: update with basic info from credits if full fetch fails
                    update_field('profile_path', $actor_credit['profile_path'] ?? '', $actor_post_id);
                    update_field('popularity', $actor_credit['popularity'] ?? '', $actor_post_id);
                    error_log("TMDB Import: Failed to fetch full details for actor TMDB ID {$actor_credit['id']}. Basic info from credits saved.");
                }
            }
            $processed_cast_names[] = sanitize_text_field($actor_credit['name']);
            $processed_cast_post_ids[] = $actor_post_id;
        }
        // Update movie with cast info
        // Assumes 'cast' ACF field on movie stores an array of names (for single-movie.php)
        // Assumes 'cast_post_ids' ACF field on movie stores an array of actor post IDs (e.g., for relationship)
        update_field('cast', $processed_cast_names, $movie_post_id); 
        update_field('cast_post_ids', $processed_cast_post_ids, $movie_post_id);

        // 7. Process Key Crew Members
        $crew_credits = $movie_data['credits']['crew'] ?? [];
        $key_crew_roles = ['Director', 'Writer', 'Screenplay', 'Producer', 'Director of Photography', 'Original Music Composer'];
        $movie_crew_display_list = [];
        $unique_crew_added = []; // To avoid duplicates like "Name (Director), Name (Producer)"

        foreach ($crew_credits as $crew_member_data) {
            if (empty($crew_member_data['name']) || empty($crew_member_data['job'])) {
                continue;
            }
            if (in_array($crew_member_data['job'], $key_crew_roles)) {
                $crew_person_key = $crew_member_data['id'] . '-' . $crew_member_data['job'];
                if (!isset($unique_crew_added[$crew_person_key])) {
                    $movie_crew_display_list[] = sanitize_text_field($crew_member_data['name']) . " (" . sanitize_text_field($crew_member_data['job']) . ")";
                    $unique_crew_added[$crew_person_key] = true;
                }
            }
        }
        // Assumes 'crew' ACF field on movie stores a comma-separated string of key crew names/roles
        update_field('crew', !empty($movie_crew_display_list) ? implode(', ', $movie_crew_display_list) : '', $movie_post_id);

        // error_log("TMDB Import: Successfully imported movie: {$movie_data['title']} (Post ID: {$movie_post_id})");

    } // End foreach movie from discover
    // error_log("TMDB Import: Movie import process finished.");
} // End function import_upcoming_movies_and_cast

// Define a custom cron schedule (e.g., weekly)
add_filter( 'cron_schedules', 'wp_movies_add_cron_intervals' );

function wp_movies_add_cron_intervals( $schedules ) {
    $schedules['weekly'] = array(
        'interval' => 604800, // 7 days in seconds (7 * 24 * 60 * 60)
        'display'  => esc_html__( 'Once Weekly' ),
    );
    // You can add more custom schedules here if needed
    return $schedules;
}



// Schedule the import event if it's not already scheduled
if ( ! wp_next_scheduled( 'wp_movies_daily_import_hook' ) ) {
  // You can change 'daily' to 'hourly', 'twicedaily', or your custom interval
  wp_schedule_event( time(), 'daily', 'wp_movies_daily_import_hook' );
}

// Hook the import function to our scheduled event
add_action( 'wp_movies_daily_import_hook', 'import_upcoming_movies_and_cast' );

// Optional: Add a manual trigger for administrators
add_action('admin_post_nopriv_run_movie_import', 'import_upcoming_movies_and_cast_manual_trigger');
add_action('admin_post_run_movie_import', 'import_upcoming_movies_and_cast_manual_trigger');

function import_upcoming_movies_and_cast_manual_trigger() {
    // Optional: Add a capability check if you want to restrict this
    // if ( !current_user_can( 'manage_options' ) ) { wp_die( 'Permission denied.' ); }
    import_upcoming_movies_and_cast();
    // Optional: Redirect back to an admin page or show a success message
    // wp_redirect( admin_url( 'edit.php?post_type=movie&import_status=success' ) );
    // exit;
    wp_die('Movie import process triggered. Check PHP error logs for details. <a href="'.admin_url('edit.php?post_type=movie').'">Back to movies</a>');
}
