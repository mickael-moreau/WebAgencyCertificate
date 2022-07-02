<?php
/**
 * 🌖🌖 Copyright Monwoo 2022 🌖🌖, build by Miguel Monwoo,
 * service@monwoo.com.
 *
 * Footer template overwrite of Twenty Twenty template
 * 
 * Displaying the footer with our WA Config footer update
 * if WA Config footer enabled is true.
 *
 * Contains the opening of the #site-footer div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package
 * @since 0.0.1
 * @author service@monwoo.com
 * @ignore
 */
// * @ package waConfig\templates\themes\twentyTwenty

namespace WA\Config\Templates\Themes\TwentyTwenty {
// TODO : refactor, not used yet, since use CSS hide instead, r&d keep looking or simply remove this file...
?>
			<footer id="site-footer" class="header-footer-group">

				<div class="section-inner">

					<div class="footer-credits">

						<p class="footer-copyright">&copy;
							<?php
							echo date_i18n(
								/* translators: Copyright date format, see https://www.php.net/manual/datetime.format.php */
								_x( 'Y', 'copyright date format', 'twentytwenty' )
							);
							?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
						</p><!-- .footer-copyright -->

						<?php
						if ( function_exists( 'the_privacy_policy_link' ) ) {
							the_privacy_policy_link( '<p class="privacy-policy">', '</p>' );
						}
						?>

						<p class="powered-by-wordpress powered-by-wa">
							<?php
                                $waConfig = \WA\Config\Core\AppInterface::instance();
                                $renderMethod = \WA\Config\Frontend\EditableFooter::class
                                . '::e_footer_render';
                                if (method_exists($waConfig, $renderMethod)) {
                                    if (!$waConfig->$renderMethod()) {
                                        ?>
                                        <p class="powered-by-wordpress">
                                            <a href="<?php echo esc_url( __( 'https://wordpress.org/', 'twentytwenty' ) ); ?>">
                                                <?php _e( 'Powered by WordPress', 'twentytwenty' ); ?>
                                            </a>
                                        </p><!-- .powered-by-wordpress -->
                                        <?php
                                    }
                                }
                            ?>
						</p><!-- .powered-by-wordpress -->

					</div><!-- .footer-credits -->

					<a class="to-the-top" href="#site-header">
						<span class="to-the-top-long">
							<?php
							/* translators: %s: HTML character for up arrow. */
							printf( __( 'To the top %s', 'twentytwenty' ), '<span class="arrow" aria-hidden="true">&uarr;</span>' );
							?>
						</span><!-- .to-the-top-long -->
						<span class="to-the-top-short">
							<?php
							/* translators: %s: HTML character for up arrow. */
							printf( __( 'Up %s', 'twentytwenty' ), '<span class="arrow" aria-hidden="true">&uarr;</span>' );
							?>
						</span><!-- .to-the-top-short -->
					</a><!-- .to-the-top -->

				</div><!-- .section-inner -->

			</footer><!-- #site-footer -->

		<?php wp_footer(); ?>

	</body>
</html>
<?php
}