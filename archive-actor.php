<?php 
get_header();
?>

<div class="max-w-6xl mx-auto px-4 py-10 bg-white rounded shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">All Actors</h1>

    <!-- Filter Form -->
    <form method="get" class="mb-8 flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label class="block text-gray-700 mb-1" for="filter-name">Name</label>
            <input type="text" id="filter-name" name="s" value="<?php echo esc_attr(get_query_var('s')); ?>" placeholder="Search by actor name..." class="border rounded px-3 py-2 w-full">
        </div>
        <div class="flex-1">
            <label class="block text-gray-700 mb-1" for="filter-movie">Movie</label>
            <input type="text" id="filter-movie" name="movie" value="<?php echo esc_attr(get_query_var('movie')); ?>" placeholder="Search by movie title..." class="border rounded px-3 py-2 w-full">
        </div>
        <div class="flex flex-col gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Filter</button>
            <?php if (!empty($_GET['s']) || !empty($_GET['movie'])): ?>
                <a href="<?php echo esc_url(get_post_type_archive_link('actor')); ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded text-center w-full">Clear All</a>
            <?php endif; ?>
        </div>
    </form>

    <?php
    // Build meta_query for movie filter
    $meta_query = [];
    if (!empty($_GET['movie'])) {
        $meta_query[] = [
            'key' => 'cast_movies', // Change this to your actual ACF field for related movies
            'value' => $_GET['movie'],
            'compare' => 'LIKE'
        ];
    }

    // Pagination setup
    $paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

    // Main query args
    $args = [
        'post_type' => 'actor',
        'posts_per_page' => 20,
        'orderby' => 'title',
        'order' => 'ASC',
        's' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
        'meta_query' => $meta_query,
        'paged' => $paged,
    ];

    $actors = new WP_Query($args);
    $image_url = 'https://image.tmdb.org/t/p/w500';
    $image_placeholder = get_template_directory_uri() . '/assets/red_carpet.jpg';
    ?>

    <?php if ($actors->have_posts()) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($actors->have_posts()) : $actors->the_post();
                $photo = get_field('profile_path');
                $actor_image = $photo ? $image_url . $photo : $image_placeholder;
            ?>
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
                    <a href="<?php the_permalink(); ?>" class="block">
                        <div style="aspect-ratio:2/3;" class="w-full">
                            <img src="<?php echo esc_url($actor_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="object-cover rounded-t-lg w-full h-full" />
                        </div>
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800"><?php the_title(); ?></h3>
                        </div>
                    </a>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        // Pagination at the bottom
        $big = 999999999;
        $links = paginate_links([
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?paged=%#%',
            'current'   => $paged,
            'total'     => $actors->max_num_pages,
            'type'      => 'array',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ]);
        if ($links) {
            echo '<nav class="my-6 flex justify-center">';
            echo '<ul class="inline-flex items-center -space-x-px">';
            foreach ($links as $link) {
                // Add Tailwind classes to <a> and <span>
                $link = str_replace(
                    ['<a', '<span', 'page-numbers current', 'page-numbers dots', 'page-numbers prev', 'page-numbers next', 'page-numbers'],
                    [
                        '<a class="px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-blue-100 transition"',
                        '<span class="px-3 py-1 mx-1 rounded border border-gray-300 bg-blue-600 text-white font-semibold"',
                        'page-numbers current bg-blue-600 text-white font-semibold',
                        'page-numbers dots text-gray-400',
                        'page-numbers prev px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-blue-100 transition"',
                        'page-numbers next px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-blue-100 transition"',
                        'page-numbers px-3 py-1 mx-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-blue-100 transition"'
                    ],
                    $link
                );
                echo "<li>$link</li>";
            }
            echo '</ul>';
            echo '</nav>';
        }
        ?>
    <?php else : ?>
        <p class="text-gray-600">No actors found.</p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>