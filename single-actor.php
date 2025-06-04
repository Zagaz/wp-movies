<?php 
/**
 * Template for displaying actors archive pages
 * 
 * @package WordPress
 * @subpackage YourThemeName
 * @since YourThemeVersion
 */

get_header(); 
?>

<?php 
if (have_posts()) : 
    while (have_posts()) : the_post(); 

        // ACF Fields
        $tmdb_actor_id = get_field('tmdb_actor_id');
        $name = get_the_title();
        $bio = get_field('biography');
        $photo = get_field('profile_path');
        $birthday = get_field('birthday') ?: 'Not Available';
        $deathday = get_field('deathday');
        $place_of_birth = get_field('place_of_birth') ?: 'Not Available';
        $homepage = get_field('homepage');
        $popularity = get_field('popularity');
        $homepage= get_field('homepage');
        $images_file_path = get_field('images_file_path');

        // Profile image base URL
        $image_url = 'https://image.tmdb.org/t/p/w500';
        ?>

        <div class="max-w-6xl mx-auto px-4 py-10 bg-white rounded shadow-md">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Profile Picture -->
                <?php if ($photo): ?>
                    <div class="flex-shrink-0">
                        <img src="<?php echo esc_url($image_url . $photo); ?>" alt="<?php echo esc_attr($name); ?>" class="rounded-md w-48 h-auto shadow">
                    </div>
                <?php endif; ?>

                <!-- Actor Info -->
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4"><?php echo esc_html($name); ?></h1>

             

                    <!-- Personal Info -->
                    <div class="space-y-1 text-gray-700">
                        <p><strong>Birthday:</strong> <?php echo esc_html($birthday); ?></p>
                        <p><strong>Birthplace:</strong> <?php echo esc_html($place_of_birth); ?></p>
                        <?php if ($deathday): ?>
                            <p><strong>Deathday:</strong> <?php echo esc_html($deathday); ?></p>
                        <?php endif; ?>
                        <?php if ($homepage): ?>
                            <p><strong>Homepage:</strong> <a href="<?php echo esc_url($homepage); ?>" class="text-blue-600 hover:underline"><?php echo esc_html($homepage); ?></a></p>
                        <?php endif; ?>
                        <?php if ($popularity): ?>
                            <p><strong>Popularity:</strong> <?php echo esc_html($popularity); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Biography Section -->
            <?php if ($bio): ?>
                <div class="mt-8">
                    <h2 class="text-2xl font-semibold mb-2 text-gray-800">Biography</h2>
                    <p class="text-gray-700 leading-relaxed"><?php echo esc_html($bio); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php
    endwhile;
endif;

get_footer();
?>
