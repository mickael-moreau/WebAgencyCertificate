<?php
/**
 * The template for displaying all single posts.
 *
 * @see storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		while ( have_posts() ) :
            if ( is_singular( 'wa-mission' ) ) {
                echo "<strong>" . get_the_date() . "</strong>";
            }
            the_post();
			do_action( 'wa-mission_single_post_before' );

			get_template_part( 'content', 'single' );

			do_action( 'wa-mission_single_post_after' );

		endwhile; // End of the loop.
		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
