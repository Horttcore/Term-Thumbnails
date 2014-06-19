<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}



/**
 * Retrieve Term Thumbnail ID.
 *
 * @param int $term_id Optional. Term ID.
 * @return int/bool Attachment ID or FALSE
 * @since 0.0
 */
function get_term_thumbnail_id( $term_id = null ) {

	$thumbnails = get_option( 'term-thumbnails' );

	if  ( isset( $thumbnails[$term_id] ) )
		return $thumbnails[$term_id];
	else
		return FALSE;

} // end get_post_thumbnail_id



/**
 * Conditional tag
 *
 * @param int $term_id Term ID
 * @return bool Term has thumbnail
 * @author Ralf Hortt
 **/
function has_term_thumbnail( $term_id = '')
{

	$thumbnails = get_option( 'term-thumbnails' );

	if  ( isset( $thumbnails[$term_id] ) )
		return TRUE;
	else
		return FALSE;

} // end has_term_thumbnail



/**
 * Display term thumbnail
 *
 * @param string|array $size Optional. Image size. Defaults to 'post-thumbnail', which theme sets using set_post_thumbnail_size( $width, $height, $crop_flag );.
 * @param string|array $attr Optional. Query string or array of attributes.
 * @author Ralf Hortt
 * @since 1.0.0
 **/
function the_term_thumbnail( $size = 'post-thumbnail', $attr = '' )
{

	if ( is_category() ) :
		$term_id = get_query_var( 'cat' );
	elseif ( is_tag() ) :
		$term_id = get_query_var( 'tag' );
	elseif ( is_tax() ) :
		$term_id = get_queried_object()->term_id;
	endif;

	echo get_term_thumbnail( $term_id, $size, $attr );

} // end the_term_thumbnail



/**
 * Get term thumbnail
 *
 * @param int $term_id Optional. Post ID.
 * @param string $size Optional. Image size. Defaults to 'post-thumbnail'.
 * @param string|array $attr Optional. Query string or array of attributes.
 * @return str HTML output
 * @author Ralf Hortt
 * @since 1.0.0
 **/
function get_term_thumbnail( $term_id = null, $size = 'post-thumbnail', $attr = '' )
{

	$term_thumbnail_id = get_term_thumbnail_id( $term_id );

	$size = apply_filters( 'term_thumbnail_size', $size );

	if ( $term_thumbnail_id )
		$html = wp_get_attachment_image( $term_thumbnail_id, $size, false, $attr );
	else
		$html = '';

	return apply_filters( 'term_thumbnail_html', $html, $term_id, $term_thumbnail_id, $size, $attr );

} // end get_term_thumbnail
