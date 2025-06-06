<?php 

// for Home page
// This is the Top 10 Most Popular Actors

?>

<div class="max-w-6xl mx-auto px-4 py-10 bg-slate-700 rounded shadow-md mt-8">

    <h2 class="text-3xl font-bold text-gray-100 mb-4">🎭Top 10 Most Popular Actors</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <?php
        $args = array(
            'post_type' => 'actor',
            'posts_per_page' => 10,
            'meta_key' => 'popularity',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        );
        $image_url = 'https://image.tmdb.org/t/p/w500';
        $image_placeholder = get_site_url() . '/wp-content/uploads/2025/06/red_carpet-1.jpg';
        $actors = new WP_Query($args);
        if ($actors->have_posts()) :
            while ($actors->have_posts()) : $actors->the_post();
                $actor_image = get_field('profile_path');
                $actor_image_url = $actor_image ? $image_url . $actor_image : $image_placeholder;
        ?>
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
                    <a href="<?php the_permalink(); ?>" class="block">
                        <div style="aspect-ratio:1/1;" class="w-full">
                            <img src="<?php echo esc_url($actor_image_url); ?>" alt="<?php the_title_attribute(); ?>" class="object-cover rounded-t-lg w-full h-full" />
                        </div>
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800"><?php the_title(); ?></h3>
                            <div>
                                <span class="block text-gray-500 text-sm">Popularity: 
                                    <?php
                                    $popularity = get_field('popularity');
                                    echo $popularity ? number_format($popularity, 2) : 'N/A';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile;
            wp_reset_postdata();
        else : ?>
            <p class="text-gray-600">No actors found.</p>
        <?php endif; ?>
    </div>
</div>