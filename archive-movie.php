<?php 
get_header();
?>

<div class="max-w-6xl mx-auto px-4 py-10 bg-white rounded shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">All Movies</h1>

    <!-- Filter Form -->
    <form method="get" class="mb-8 flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label class="block text-gray-700 mb-1" for="filter-title">Title</label>
            <input type="text" id="filter-title" name="s" value="<?php echo esc_attr(get_query_var('s')); ?>" placeholder="Search by title..." class="border-2 placeholder-gray-500 border rounded px-3 py-2 w-full text-gray-900">
        </div>
        <div class="flex-1">
            <label class="block text-gray-700 mb-1" for="filter-year">Year</label>
            <input type="text" id="filter-year" name="year" value="<?php echo isset($_GET['year']) ? esc_attr($_GET['year']) : ''; ?>" placeholder="e.g. 2025" class="border rounded px-3 py-2 w-full placeholder-gray-500 text-gray-900">
        </div>
        <div class="flex-1">
            <label class="block text-gray-700 mb-1" for="filter-genre">Genre</label>
            <input type="text" id="filter-genre" name="genre" value="<?php echo esc_attr(get_query_var('genre')); ?>" placeholder="e.g. Action" class="border rounded px-3 py-2 w-full placeholder-gray-500 text-gray-900">
        </div>
        <div class="flex flex-col gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Filter</button>
            <?php if (!empty($_GET['s']) || !empty($_GET['year']) || !empty($_GET['genre'])): ?>
                <a href="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded text-center w-full">Clear All</a>
            <?php endif; ?>
        </div>
    </form>

    <?php
    // Build meta_query for year and genre
    $meta_query = [];
    if (isset($_GET['year']) && $_GET['year'] !== '') {
        $meta_query[] = [
            'key' => 'release_date',
            'value' => '^' . preg_quote($_GET['year'], '/') . '-',
            'compare' => 'REGEXP'
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