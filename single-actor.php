<?php 

/**
 * Template for displaying actors archive pages
 * 
 * @package WordPress
 * @subpackage YourThemeName
 * @since YourThemeVersion
 * */
 

get_header(); ?>
<div class="actor-archive">
    <h1>Actors Archive</h1>
    <?php if (have_posts()) : ?>
        <ul class="actor-list">
            <?php while (have_posts()) : the_post(); ?>
                <li class="actor-item
">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="actor-thumbnail">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="actor-name
"><?php the_title(); ?></h2>
                    </a>
                    <div class="actor-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>No actors found.</p>
    <?php endif; ?>
</div>






<?php get_footer(); ?>

