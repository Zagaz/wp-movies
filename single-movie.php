<?php
get_header(); // Load header

if (have_posts()) :
    while (have_posts()) : the_post();

        // Get ACF fields
        $poster_url   = get_field('poster_url');
        $release_date = get_field('release_date');
        $genre        = get_field('genres');
        $overview     = get_field('overview');
        $production_companies = get_field('production_companies');
        $original_language = get_field('original_language');
        $cast = get_field('cast');
        $crew = get_field('crew');
        $trailer = get_field('trailer');
        $production_companies = get_field('production_companies');
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
            <p><strong>Actors:</strong></p>
            <?php
            foreach ($cast as $actor) {
                $url = get_site_url() . '/actor/' . sanitize_title($actor);
                echo '<a href="' . esc_url($url) . '">' . esc_html($actor) . '</a>, ';
            }
            ?>

            <?php // This is trailer 
            ?>
            <p><strong>Trailer:</strong></p>
            <?php if ($trailer): ?>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo esc_html($trailer); ?>" frameborder="0" allowfullscreen></iframe>
            <?php else: ?>
                <p>No trailer available.</p>
            <?php endif; ?>



        </div>

<?php
    endwhile;
endif;

get_footer();
