<?php
get_header(); // Load header

if (have_posts()) :
    while (have_posts()) : the_post();

        // Get ACF fields
        $poster_url   = get_field('poster_url');
        $release_date = get_field('release_date');
        $genre        = get_field('genre');
        $overview     = get_field('overview');
        $production_companies = get_field('production_companies');
        $original_language = get_field('original_language');
        $cast = get_field('crew');
        $actors = get_field('cast');
?>
        <div class="movie-detail">
            <h1><?php the_title(); ?></h1>
            <?php if ($poster_url): ?>
                <?php
                $image_url = 'https://image.tmdb.org/t/p/w500';
                ?>

                <img src="<?php echo esc_url($image_url . $poster_url) ?>" alt="<?php the_title(); ?>" style="max-width: 300px;">
            <?php endif; ?>

            <p><strong>Release Date:</strong> <?php echo esc_html($release_date); ?></p>
            <p><strong>Genre:</strong> <?php echo esc_html($genre); ?></p>
            <p><strong>Overview:</strong><br><?php echo esc_html($overview); ?></p>
            <p><strong>Production Companies:</strong> <?php echo esc_html($production_companies); ?></p>
            <p><strong>Original Language:</strong> <?php echo esc_html($original_language); ?></p>
            <p><strong>Cast:</strong></p>
            <?php
            var_dump($cast); // Debugging line to check the cast data
            ?>
            <p><strong>Actors:</strong></p>
            <?php
            var_dump($actors); // Debugging line to check the actors data

            ?>
        </div>

<?php
    endwhile;
endif;

get_footer();
