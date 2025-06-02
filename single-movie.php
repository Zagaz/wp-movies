<?php
get_header(); // Load header

if (have_posts()) :
    while (have_posts()) : the_post();

        // Get ACF fields
        $poster_url   = get_field('poster_url');
        $release_date = get_field('release_date');
        $genre        = get_field('genre');
        $overview     = get_field('overview');
        ?>
        
        <div class="movie-detail">
            <h1><?php the_title(); ?></h1>
            

            <?php if ($poster_url): ?>
                /<?php 
           
                $image_url = 'https://image.tmdb.org/t/p/w500' . $image_path; ?>

                <img src="<?php echo esc_url( $image_url.$poster_url) ?>" alt="<?php the_title(); ?>" style="max-width: 300px;">
            <?php endif; ?>

            <p><strong>Release Date:</strong> <?php echo esc_html($release_date); ?></p>
            <p><strong>Genre:</strong> <?php echo esc_html($genre); ?></p>
            <p><strong>Overview:</strong><br><?php echo esc_html($overview); ?></p>

            <?php
$trailer_url = get_field('trailer_url');

if ($trailer_url):
    // Extract YouTube video ID (assumindo que o link é tipo https://www.youtube.com/watch?v=VIDEO_ID)
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $trailer_url, $matches);
    $video_id = $matches[1] ?? null;

    if ($video_id):
        ?>
        <div class="movie-trailer" style="margin-top: 20px;">
            <h2>Trailer</h2>
            <iframe width="560" height="315"
                    src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>
        </div>
        <?php
    endif;
endif;
?>

        </div>

        <?php
    endwhile;
endif;

get_footer(); 
