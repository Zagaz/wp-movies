<?php 

// for Home page
// This is the Upcoming Movies

?>


<div class="max-w-6xl mx-auto px-4 py-10 bg-white rounded shadow-md mt-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Upcoming Movies</h2>
    <?php
    // Query next 10 upcoming movies, ordered by release_date ASC
    $args = array(
        'post_type'      => 'movie',
        'posts_per_page' => 10,
        'meta_key'       => 'release_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => 'release_date',
                'value'   => date('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE'
            )
        )
    );
    $movies = new WP_Query($args);

    // Group movies by month and year
    $grouped_movies = [];
    if ($movies->have_posts()) :
        while ($movies->have_posts()) : $movies->the_post();
            $release_date = get_field('release_date');
            if ($release_date) {
                $month_year = date_i18n('F Y', strtotime($release_date));
                $grouped_movies[$month_year][] = get_the_ID();
            }
        endwhile;
        wp_reset_postdata();
    endif;
    ?>

    <?php if (!empty($grouped_movies)) : ?>
        <?php foreach ($grouped_movies as $month_year => $movie_ids) : ?>
            <h3 class="text-2xl font-semibold text-amber-700 mt-10 mb-4"><?php echo esc_html($month_year); ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($movie_ids as $movie_id) :
                    $poster_url   = get_field('poster_url', $movie_id);
                    $release_date = get_field('release_date', $movie_id);
                    $genres        = get_field('genres', $movie_id);
                    $overview     = get_field('overview', $movie_id);
                    $production_companies = get_field('production_companies', $movie_id);
                    $original_language = get_field('original_language', $movie_id);
                    $image_url = 'https://image.tmdb.org/t/p/w500';
                    $image_placeholder = get_site_url() . '/wp-content/uploads/2025/06/red_carpet-1.jpg';
                    $poster = $poster_url ? $image_url . $poster_url : $image_placeholder;
                    ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
                        <a href="<?php echo get_permalink($movie_id); ?>" class="block">
                            <div style="aspect-ratio:2/3;" class="w-full">
                                <img src="<?php echo esc_url($poster); ?>" alt="<?php echo esc_attr(get_the_title($movie_id)); ?>" class="object-cover rounded-t-lg w-full h-full" />
                            </div>
                            <div class="p-4">
                                <h3 class="text-xl font-semibold text-gray-800"><?php echo esc_html(get_the_title($movie_id)); ?></h3>
                                <?php if ($release_date): ?>
                                    <p class="text-gray-500 text-sm mb-1">Release: <?php echo esc_html($release_date); ?></p>
                                <?php endif; ?>
                                <?php if ($genres): ?>
                                    <p class="text-gray-500 text-sm mb-1">Genre: <?php echo esc_html($genres); ?></p>
                                <?php endif; ?>
                   
                         
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p class="text-gray-600">No upcoming movies found.</p>
    <?php endif; ?>

</div>
