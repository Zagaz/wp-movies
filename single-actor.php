<?php
/**
 * Template for displaying a single actor
 * 
 * @package WordPress
 * @subpackage wp-movies
 */

get_header();

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
        $images_file_path = get_field('images_file_path');
        $image_url = 'https://image.tmdb.org/t/p/w500';
        $image_placeholder = get_site_url() . '/wp-content/uploads/2025/06/red_carpet-1.jpg';
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
            <div class="space-y-1 text-gray-700">
                <p><strong>Birthday:</strong> <?php echo esc_html($birthday); ?></p>
                <p><strong>Birthplace:</strong> <?php echo esc_html($place_of_birth); ?></p>
                <?php if ($deathday): ?>
                    <p><strong>Deathday:</strong> <?php echo esc_html($deathday); ?></p>
                <?php endif; ?>
                <?php if ($homepage): ?>
                    <p><strong>Homepage:</strong> <a href="<?php echo esc_url($homepage); ?>" class="text-blue-600 hover:underline" rel="noopener noreferrer" target="_blank"><?php echo esc_html($homepage); ?></a></p>
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

    <!-- Image Gallery Section -->
    <div class="image-gallery-wrapper">
        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-gray-800">Image Gallery</h2>
            <?php if ($images_file_path && is_array($images_file_path) && count($images_file_path) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    <?php foreach (array_slice($images_file_path, 0, 10) as $image): ?>
                        <div class="bg-gray-50 rounded shadow p-4 flex flex-col items-center">
                            <img
                                src="<?php echo esc_url($image_url . $image); ?>"
                                alt="<?php echo esc_attr($name); ?> Image"
                                class="w-full h-auto object-cover rounded mb-2 shadow">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No images available.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php // The movie list will be rendered here ?> 
    <div id="actor-movie-credits" class="mt-8"></div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pass PHP variables to JS
    const tmdbActorId = <?php echo json_encode($tmdb_actor_id); ?>;
    const apiKey = <?php echo json_encode($api_key); ?>;
    const imagePlaceholder = <?php echo json_encode(esc_url($image_placeholder)); ?>;

    fetchAndDisplayMovieCredits(tmdbActorId);

    async function fetchAndDisplayMovieCredits(tmdb_actor_id) {
        if (!tmdb_actor_id) return;
        try {
            const response = await fetch(
                `https://api.themoviedb.org/3/person/${encodeURIComponent(tmdb_actor_id)}/movie_credits?api_key=${apiKey}&language=en-US`
            );
            console.log('Fetching movie credits for actor ID:', tmdb_actor_id);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            const sortedMovies = Array.isArray(data.cast)
                ? data.cast.sort((a, b) => new Date(b.release_date) - new Date(a.release_date))
                : [];
            displayMovies(sortedMovies);
        } catch (error) {
            console.error('Error fetching movie credits:', error);
        }
    }
// Display the Movies
    function displayMovies(movies) {
        if (!Array.isArray(movies) || movies.length === 0) return;
        const container = document.getElementById('actor-movie-credits');
        container.innerHTML = `
            <h2 class="text-2xl font-semibold mb-2 text-gray-800">Movie Credits</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                ${movies.map(movie => `
                    <div class="bg-gray-50 rounded shadow p-4 flex flex-col items-center">
                        <img 
                            src="${movie.poster_path ? 'https://image.tmdb.org/t/p/w185' + movie.poster_path : imagePlaceholder}" 
                            alt="${movie.title ? escapeHtml(movie.title) : ''}" 
                            class="w-32 h-44 object-cover rounded mb-2 shadow"
                        >
                        <div class="text-center">
                            <div class="text-gray-700 text-sm mb-1">
                                ${movie.character ? 'as <span class="font-semibold">' + escapeHtml(movie.character) + '</span>' : ''}
                            </div>
                            <div class="font-medium text-base text-gray-900">${movie.title ? escapeHtml(movie.title) : ''}</div>
                            <div class="text-gray-500 text-xs">${movie.release_date ? escapeHtml(movie.release_date) : ''}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
}// End display movies

    // Simple HTML escape to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function (m) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[m];
        });
    }
});
</script>

<?php
    endwhile;
endif;

get_footer();
?>