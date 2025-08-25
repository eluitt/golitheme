<?php get_header(); ?>
<main id="primary" class="site-main">
<?php if ( have_posts() ) : ?>
<?php while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<h1 class="gn-sr-only"><?php the_title(); ?></h1>
<?php the_content(); ?>
</article>
<?php endwhile; ?>
<?php else : ?>
<p><?php esc_html_e( 'No content found.', 'golitheme' ); ?></p>
<?php endif; ?>
</main>
<?php get_footer(); ?>
