<?php 
//functions.php

  // Dinamically  load from /includes  '9facf375ac53c66a77dfa59841360240';

    $includes = glob(__DIR__ . '/includes/*.php');
    foreach ($includes as $file) {
        require_once $file;
    }
    

// Função que importa um filme e seus atores
function import_movie_with_cast($movie_id) {
    $api_key = '9facf375ac53c66a77dfa59841360240';

    // Busca os detalhes do filme
    $movie_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language=en-US");
    $movie_data = json_decode(wp_remote_retrieve_body($movie_response), true);

    if (empty($movie_data['title'])) {
        return;
    }

    // Cria o post do tipo movie
    $movie_post_id = wp_insert_post([
        'post_title'   => $movie_data['title'],
        'post_type'    => 'movie',
        'post_status'  => 'publish',
        'post_content' => $movie_data['overview'],
    ]);

    if (is_wp_error($movie_post_id)) return;

    // Salva a data de lançamento como campo personalizado
    update_field('release_date', $movie_data['release_date'], $movie_post_id);

    // Busca os atores (cast)
    $cast_response = wp_remote_get("https://api.themoviedb.org/3/movie/{$movie_id}/credits?api_key={$api_key}&language=en-US");
    $cast_data = json_decode(wp_remote_retrieve_body($cast_response), true);

    if (empty($cast_data['cast'])) return;

    $actor_ids = [];

    foreach (array_slice($cast_data['cast'], 0, 5) as $actor) {
        $actor_name = $actor['name'];

        // Verifica se ator já existe
        $existing = get_page_by_title($actor_name, OBJECT, 'actor');

        if ($existing) {
            $actor_id = $existing->ID;
        } else {
            // Cria novo post actor
            $actor_id = wp_insert_post([
                'post_title'  => $actor_name,
                'post_type'   => 'actor',
                'post_status' => 'publish'
            ]);
        }

        $actor_ids[] = $actor_id;
    }

    // Relaciona atores ao filme
    update_field('actors', $actor_ids, $movie_post_id);
}

// Função que busca os próximos lançamentos e chama a função de importação
function import_upcoming_movies_with_cast() {
    $api_key = 'SUA_API_KEY_DO_TMDB';

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

add_action('init', function() {
    if (isset($_GET['importar']) && $_GET['importar'] === 'filmes') {
        import_upcoming_movies_with_cast();
        echo 'Importação finalizada.';
        exit;
    }
});
