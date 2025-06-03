<?php get_header(); ?>

<h1 class="text-5xl text-cyan-900 font-bold text-center my-10">Hello home</h1>
<div class="max-w-6xl mx-auto px-4 py-10 bg-white rounded shadow-md">
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Latest Movies</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <?php
        $args = array(
            'post_type' => 'movie',
            'posts_per_page' => 10,
            // order by release date, descending
            'meta_key' => 'release_date',
            'orderby' => 'meta_value',
            'order' => 'DESC',
            
        );
        $movies = new WP_Query($args);
        if ($movies->have_posts()) :
            $i = 0;
            while ($movies->have_posts()) : $movies->the_post();
                $i++;
                // Get ACF fields
                $poster_url   = get_field('poster_url');
                $release_date = get_field('release_date');
                $genre        = get_field('genres');
                $overview     = get_field('overview');
                $production_companies = get_field('production_companies');
                $original_language = get_field('original_language');
                $cast = get_field('cast');
                $crew = get_field('crew');
                $image_url = 'https://image.tmdb.org/t/p/w500';

                // All cards have the same class (no special span for the first)
                $item_class = 'bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300';
                ?>
                <div class="<?php echo $item_class; ?>">
                    <a href="<?php the_permalink(); ?>" class="block">
                        <div style="aspect-ratio:2/3;" class="w-full">
                            <img src="<?php echo esc_url($image_url . $poster_url); ?>" alt="<?php the_title_attribute(); ?>" class="object-cover rounded-t-lg w-full h-full" />
                        </div>
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800"><?php the_title(); ?></h3>
                            <?php if ($release_date): ?>
                                <p class="text-gray-500 text-sm mb-1">Release: <?php echo esc_html($release_date); ?></p>
                            <?php endif; ?>
                            <?php if ($genre): ?>
                                <p class="text-gray-500 text-sm mb-1">Genre: <?php echo esc_html($genre); ?></p>
                            <?php endif; ?>
                            <?php if ($production_companies): ?>
                                <p class="text-gray-500 text-sm mb-1">Production: <?php echo esc_html($production_companies); ?></p>
                            <?php endif; ?>
                            <?php if ($original_language): ?>
                                <p class="text-gray-500 text-sm mb-1">Language: <?php echo esc_html($original_language); ?></p>
                            <?php endif; ?>
                            <?php if ($overview): ?>
                                <p class="text-gray-600 mt-2"><?php echo esc_html($overview); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endwhile;
            wp_reset_postdata();
        else : ?>
            <p class="text-gray-600">No movies found.</p>
        <?php endif; ?>
    </div>

<?php get_footer(); ?>