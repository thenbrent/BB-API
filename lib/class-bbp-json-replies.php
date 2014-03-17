<?php
/**
 * Reply post type handlers
 *
 * @package bbPress
 * @subpackage JSON API
 */

/**
 * Reply post type handlers
 *
 * This class serves as a small addition on top of the basic post handlers to
 * add small functionality on top of the existing API.
 *
 * In addition, this class serves as a sample implementation of building on top
 * of the existing APIs for custom post types.
 *
 * @package bbPress
 * @subpackage bbPress JSON API
 */
class BBP_JSON_Replies extends WP_JSON_CustomPostType {

	/**
	 * Base route
	 *
	 * @var string
	 */
	protected $base = '/replies';

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $type = 'reply';

	/**
	 * Prepare post data
	 *
	 * @param array $post The unprepared post data
	 * @param array $fields The subset of post type fields to return
	 * @return array The prepared post data
	 */
	protected function prepare_post( $post, $context = 'view' ) {
		$_post = parent::prepare_post( $post, $context );

		// Override entity meta keys with the correct links
		$_post['meta']['links']['self'] = json_url( $this->base . '/' . get_page_uri( $post['ID'] ) );

		if ( ! empty( $post['post_parent'] ) )
			$_post['meta']['links']['up'] = json_url( $this->base . '/' . get_page_uri( (int) $post['post_parent'] ) );

		return apply_filters( 'json_prepare_page', $_post, $post, $context );
	}
}