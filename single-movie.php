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

<div class="max-w-4xl mx-auto px-2 sm:px-4 py-6 sm:py-10 bg-white rounded shadow-md">
    <div class="flex flex-col md:flex-row gap-6 md:gap-10">
        <?php if ($poster_url): ?>
            <?php $image_url = 'https://image.tmdb.org/t/p/w500'; ?>
            <div class="flex-shrink-0 flex justify-center md:block mb-4 md:mb-0">
                <img src="<?php echo esc_url($image_url . $poster_url) ?>" alt="<?php the_title(); ?>" class="rounded-lg shadow w-40 sm:w-56 md:w-64 h-auto mx-auto md:mx-0">
            </div>
        <?php endif; ?>

        <div class="flex-1">
            <h2 class="text-2xl sm:text-4xl font-bold mb-4 text-center md:text-left"><?php the_title(); ?></h2>
            <div class="space-y-2 text-gray-700 text-center md:text-left">
                <p><strong>Release Date:</strong> <?php echo esc_html($release_date); ?></p>
                <p><strong>Genre:</strong> <?php echo esc_html($genre); ?></p>
                <p><strong>Overview:</strong> <?php echo esc_html($overview); ?></p>
                <p><strong>Production Companies:</strong> <?php echo esc_html($production_companies); ?></p>
                <p><strong>Original Language:</strong> <?php echo esc_html($original_language); ?></p>
                <p><strong>Popularity:</strong> <?php echo esc_html($movie_popularity); ?></p>
                <p><strong>List of similar movies:</strong> <?php echo esc_html($similar_movies); ?></p>
                <div>
                    <p><strong>Cast:</strong></p>
                    <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                        <?php
                        if (is_array($cast)) {
                            foreach ($cast as $actor) {
                                $url = get_site_url() . '/actor/' . sanitize_title($actor);
                                echo '<a href="' . esc_url($url) . '" class="text-blue-600 hover:underline">' . esc_html($actor) . '</a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="video-wrapper mt-8">
        <p><strong>Alternative Titles:</strong><br> <?php echo esc_html($alternative_titles); ?></p>
        <br>
        <p><strong>Trailer:</strong></p>
        <?php if ($trailer): ?>
            <div class="aspect-w-16 aspect-h-9 w-full max-w-2xl mx-auto">
                <iframe class="w-full h-64 sm:h-80 rounded" src="https://www.youtube.com/embed/<?php echo esc_html($trailer); ?>" frameborder="0" allowfullscreen></iframe>
            </div>
        <?php else: ?>
            <p>No trailer available.</p>
        <?php endif; ?>
    </div>

    <div class="reviews-wrapper mt-10 mb-20">
        <h1 class="text-2xl font-bold mb-4">Reviews</h1>
        <?php
        $review_url = "https://api.themoviedb.org/3/movie/{$tmdb_id}/reviews?language=en-US&page=1&api_key={$api_key}";
        $review_res = json_decode(wp_remote_get($review_url)['body']);
        if (!empty($review_res->results)) {
            foreach ($review_res->results as $review) {
        ?>
                <div class="review mb-6 p-4 bg-gray-50 rounded shadow">
                    <p class="font-semibold"><?php echo esc_html($review->author); ?>:</p>
                    <p class="text-gray-700"><?php echo esc_html($review->content); ?></p>
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
