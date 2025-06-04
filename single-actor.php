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
$api_key = '9facf375ac53c66a77dfa59841360240';
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
        $homepage = get_field('homepage');
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
            <div class="image-galery-wrapper">
                <?php
                // Image Gallery Section 

                if ($images_file_path && is_array($images_file_path) && count($images_file_path) > 0):
                    $images_to_show = array_slice($images_file_path, 0, 10);
                ?>
                    <div class="mt-8">
                        <h2 class="text-2xl font-semibold mb-2 text-gray-800">Image Gallery</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                            <?php foreach ($images_to_show as $image): ?>
                                <div class="bg-gray-50 rounded shadow p-4 flex flex-col items-center">
                                    <img
                                        src="<?php echo esc_url($image_url . $image); ?>"
                                        alt="<?php echo esc_attr($name); ?> Image"
                                        class="w-full h-auto object-cover rounded mb-2 shadow">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-8">
                        <h2 class="text-2xl font-semibold mb-2 text-gray-800">Image Gallery</h2>
                        <p class="text-gray-600">No images available.</p>
                    </div>
                <?php endif; ?>
            </div>
            <script>
                // Using Javascript to fetch and display movie credits
                fetchAndSortMovieCredits(<?php echo esc_js($tmdb_actor_id); ?>);

                async function fetchAndSortMovieCredits(tmdb_actor_id) {
                    try {
                        const response = await fetch(`https://api.themoviedb.org/3/person/${tmdb_actor_id}/movie_credits?api_key=<?php echo esc_js($api_key); ?>&language=en-US`);
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        const data = await response.json();
                        const sortedMovies = data.cast.sort((a, b) => new Date(b.release_date) - new Date(a.release_date));
                        displayMovies(sortedMovies);
                    } catch (error) {
                        console.error('Error fetching movie credits:', error);
                    }
                }

                function displayMovies(movies) {
                    if (!Array.isArray(movies) || movies.length === 0) return;
                    let container = document.getElementById('actor-movie-credits');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'actor-movie-credits';
                        container.className = 'mt-8';
                        document.querySelector('.max-w-6xl').appendChild(container);
                    }
                    container.innerHTML = `
                    <h2 class="text-2xl font-semibold mb-2 text-gray-800">Movie Credits</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        ${movies.map(movie => `
                            <div class="bg-gray-50 rounded shadow p-4 flex flex-col items-center">
                                <img 
                                    src="${movie.poster_path ? 'https://image.tmdb.org/t/p/w185' + movie.poster_path : 'https://via.placeholder.com/185x278?text=No+Image'}" 
                                    alt="${movie.title || movie.original_title}" 
                                    class="w-32 h-44 object-cover rounded mb-2 shadow"
                                >
                                <div class="text-center">
                                    <div class="text-gray-700 text-sm mb-1">${movie.character ? 'as <span class="font-semibold">' + movie.character + '</span>' : ''}</div>
                                    <div class="font-medium text-base text-gray-900">${movie.title || movie.original_title}</div>
                                    <div class="text-gray-500 text-xs">${movie.release_date ? movie.release_date : ''}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                }
            </script>
        </div>
<?php
    endwhile;
endif;

get_footer();
?>