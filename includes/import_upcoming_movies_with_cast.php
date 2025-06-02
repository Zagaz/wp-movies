<?php 
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
    // Check if a post with this tmdb_id already exists
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

    if (empty($existing->posts)) {
      import_movie_with_cast($movie['id']);
    }
    // else: skip, already imported
  }
}

add_action('init', function () {
/*
TO DO:
 */
// check if post type 'movie' posts > 0 




  if (isset($_GET['importar']) && $_GET['importar'] === 'filmes') {
    import_upcoming_movies_with_cast();
    echo 'Import completed.';
    $log_time = date('Y-m-d H:i:s');
    error_log("Import completed at {$log_time}\n", 3, plugin_dir_path(__FILE__) . 'import_log.txt');
    exit;
  }
});
