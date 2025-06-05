<?php
get_header(); // Load header
  $api_key = TMDB_API_KEY;

if (have_posts()) :
    while (have_posts()) : the_post();

        // Get ACF fields
        $tmdb_id = get_field('tmdb_id');
        $poster_url   = get_field('poster_url');
        $release_date = get_field('release_date');
        $genre        = get_field('genres');
        $overview = get_the_content();
        $production_companies = get_field('production_companies');
        $original_language = get_field('original_language');
        $cast = get_field('cast');
        $crew = get_field('crew');
        $trailer = get_field('trailer');
        $production_companies = get_field('production_companies');
        $movie_popularity = number_format((float)get_field('movie_popularity'), 2, '.', '');
        $similar_movies = get_field('similar_movies');
        $alternative_titles = get_field('alternative_titles');





?>
        <div class="movie-detail px-30">
            <?php if ($poster_url): ?>
                <?php
                $image_url = 'https://image.tmdb.org/t/p/w500';
                ?>

                <img src="<?php echo esc_url($image_url . $poster_url) ?>" alt="<?php the_title(); ?>" style="max-width: 300px;">
            <?php endif; ?>


            <h2 class="text-4xl font-bold"><?php the_title(); ?></h2>
            <p><strong>Release Date:</strong> <?php echo esc_html($release_date); ?></p>
            <p><strong>Genre:</strong> <?php echo esc_html($genre); ?></p>
            <p><strong>Overview: </strong><?php echo esc_html($overview); ?></p>
            <p><strong>Production Companies:</strong> <?php echo esc_html($production_companies); ?></p>
            <p><strong>Original Language:</strong> <?php echo esc_html($original_language); ?></p>
            <p><strong>Popularity:</strong> <?php echo esc_html($movie_popularity); ?></p>
            <p><strong>List of similar movies:</strong> <?php echo esc_html($similar_movies); ?></p>
            <p><strong>Cast:</strong></p>
            <?php
            foreach ($cast as $actor) {
                $url = get_site_url() . '/actor/' . sanitize_title($actor);
                echo '<a href="' . esc_url($url) . '">' . esc_html($actor) . '</a>, ';
            }
            ?>
            <div class="video-wrapper">
                <?php // This is trailer 
                ?>
                <p><strong>Alternative Titles:</strong><br> <?php echo esc_html($alternative_titles); ?></p>
                <br>
                <p><strong>Trailer:</strong></p>
                <?php if ($trailer): ?>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo esc_html($trailer); ?>" frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <p>No trailer available.</p>
                <?php endif; ?>
            </div>
            <div class="reviews-wrapper  mt-10 mb-20">
                <h1 class="
                text-2xl font-bold
                ">Reviews</h1>
                <?php
                $review_url = "https://api.themoviedb.org/3/movie/{$tmdb_id}/reviews?language=en-US&page=1&api_key={$api_key}";
                $review_res = json_decode( wp_remote_get( $review_url )['body'] );
                if ( ! empty( $review_res->results ) ) {
                    foreach ( $review_res->results as $review ) {
                ?>
                        <div class="review">
                            <p><strong><?php echo esc_html( $review->author ); ?>:</strong></p>
                            <p><?php echo esc_html( $review->content ); ?></p>
                        </div>
                <?php
                    }
                } else {
                ?>
                    <p>No reviews found.</p>
                <?php
                }
                ?>

            </div>



        </div>

<?php
    endwhile;
endif;

get_footer();
