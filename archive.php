<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

$templates = array( 'archive.twig', 'index.twig' );

$data = Timber::get_context();

$data['title'] = 'Archive';
$data['pagination'] = Timber::get_pagination();
if ( is_day() ) {
	$data['title'] = 'Archive: '.get_the_date( 'D M Y' );
} else if ( is_month() ) {
	$data['title'] = 'Archive: '.get_the_date( 'M Y' );
} else if ( is_year() ) {
	$data['title'] = 'Archive: '.get_the_date( 'Y' );
} else if ( is_tag() ) {
	$data['title'] = single_tag_title( '', false );
} else if ( is_category() ) {
	$data['title'] = single_cat_title( '', false );
	array_unshift( $templates, 'archive-' . get_query_var( 'cat' ) . '.twig' );
} else if ( is_post_type_archive() ) {
	$data['title'] = post_type_archive_title( '', false );
	if(get_post_type() == 'results' || get_post_type() == 'videos') {
		$data['title'] = '';
	}
	array_unshift( $templates, 'archive-' . get_post_type() . '.twig' );
} else if (strlen(get_query_var( 'debt' )) > 0) {
	$data['title'] = '';
	$data['selected_debt_type'] = get_query_var('debt');
	// If we're in the custom debt taxonomy, we want to use the results archive template
	array_unshift( $templates, 'archive-results.twig' );
}

$data['posts'] = Timber::get_posts();
Timber::render( $templates, $data );
