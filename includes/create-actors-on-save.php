<?php
// Cria automaticamente os atores do campo ACF "actors_list" ao salvar um post do tipo "movie"
function create_actors_from_actors_list($post_id) {
    // Garante que está salvando um post tipo 'movie'
    if (get_post_type($post_id) !== 'movie') {
        return;
    }

    // Evita loop infinito
    remove_action('save_post', 'create_actors_from_actors_list');

    // Recupera o valor do campo ACF
    $actors_list = get_field('actors_list', $post_id);

    if (!empty($actors_list)) {
        // Separa os nomes por vírgula
        $actor_names = array_map('trim', explode(',', $actors_list));

        foreach ($actor_names as $name) {
            if (!empty($name)) {
                $existing = get_page_by_title($name, OBJECT, 'actor');

                if (!$existing) {
                    wp_insert_post([
                        'post_title'  => $name,
                        'post_type'   => 'actor',
                        'post_status' => 'publish',
                    ]);
                }
            }
        }
    }

    // Reanexa o hook
  //  add_action('save_post', 'create_actors_from_actors_list');
}
//add_action('save_post', 'create_actors_from_actors_list');
