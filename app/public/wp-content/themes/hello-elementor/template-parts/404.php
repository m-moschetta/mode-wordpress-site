<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package HelloElementor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<main id="content" class="site-main">

	<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
		<header class="page-header">
			<h1 class="entry-title"><?php echo esc_html__( 'The page can&rsquo;t be found.', 'hello-elementor' ); ?></h1>
		</header>
	<?php endif; ?>

	<section class="page-content" aria-label="Error message">
		<p><?php echo esc_html__( 'It looks like nothing was found at this location.', 'hello-elementor' ); ?></p>
	</section>

</main>
