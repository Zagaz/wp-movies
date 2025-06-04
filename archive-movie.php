<?php 
get_header();
?>

<div class="max-w-6xl mx-auto px-4 py-10 bg-white rounded shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">All Movies</h1>

    <!-- Filter Form -->
    <form method="get" class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="s" value="<?php echo esc_attr(get_query_var('s')); ?>" placeholder="Search by title..." class="border rounded px-3 py-2">
        <input type="text" name="year" value="<?php echo esc_attr(get_query_var('year')); ?>" placeholder="Year..." class="border rounded px-3 py-2">
        <input type="text" name="genre" value="<?php echo esc_attr(get_query_var('genre')); ?>" placeholder="Genre..." class="border rounded px-3 py-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <?php
    // Build meta_query for year and genre
    $meta_query = [];
    if (!empty($_GET['year'])) {
        $meta_query[] = [
            'key' => 'release_date',
            'value' => $_GET['year'],
            'compare' => 'LIKE'
        ];
    }
    if (!empty($_GET['genre'])) {
        $meta_query[] = [
            'key' => 'genres',
            'value' => $_GET['genre'],
            'compare' => 'LIKE'
        ];
    }

    // Main query args
    $args = [
        'post_type' => 'movie',
        'posts_per_page' => 20,
        'orderby' => 'title',
        'order' => 'ASC',
        's' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
        'meta_query' => $meta_query
    ];

    $movies = new WP_Query($args);
    $image_url = 'https://image.tmdb.org/t/p/w500';
    $image_placeholder = get_site_url() . '/wp-content/uploads/2025/06/red_carpet-1.jpg';
    ?>

    <?php if ($movies->have_posts()) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php while ($movies->have_posts()) : $movies->the_post();
                $poster_url = get_field('poster_url');
                $release_date = get_field('release_date');
                $genres = get_field('genres');
                $poster = $poster_url ? $image_url . $poster_url : $image_placeholder;
            ?>
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
                    <a href="<?php the_permalink(); ?>" class="block">
                        <div style="aspect-ratio:2/3;" class="w-full">
                            <img src="<?php echo esc_url($poster); ?>" alt="<?php the_title_attribute(); ?>" class="object-cover rounded-t-lg w-full h-full" />
                        </div>
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800"><?php the_title(); ?></h3>
                            <?php if ($release_date): ?>
                                <p class="text-gray-500 text-sm mb-1">Release: <?php echo esc_html($release_date); ?></p>
                            <?php endif; ?>
                            <?php if ($genres): ?>
                                <p class="text-gray-500 text-sm mb-1">Genre: <?php echo esc_html($genres); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php else : ?>
        <p class="text-gray-600">No movies found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>