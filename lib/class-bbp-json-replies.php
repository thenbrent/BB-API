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
	 * Add actions and filters for the post type
	 *
	 * This method should be called after instantiation to automatically add the
	 * required filters for the post type.
	 */
	public function register_filters() {
		parent::register_filters();

		add_action( 'json_insert_post', array( $this, 'add_protected_meta' ), 10, 3 );
	}

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

	/**
	 * When inserting a new reply, make sure the protected meta data is set correctly.
	 *
	 * We can't use WP_JSON_Posts::add_meta() here because the required meta is deemed
	 * protected by @see is_protected_meta().
	 *
	 * @see WP_JSON_Posts::insert_post()
	 * @see WP_JSON_Posts::add_meta()
	 */
	public function add_protected_meta( $post, $data, $update ) {

		if ( ! $update && $this->type == $post['post_type'] ) {

			// Forum meta
			$reply_meta = array(
				'author_ip' => bbp_current_author_ip(),
				'forum_id'  => bbp_get_topic_forum_id( $post['post_parent'] ),
				'topic_id'  => $post['post_parent'],
			);

			// Insert reply meta
			foreach ( $reply_meta as $meta_key => $meta_value ) {
				update_post_meta( $post['ID'], '_bbp_' . $meta_key, $meta_value );
			}

			// Update the topic
			$topic_id = bbp_get_reply_topic_id( $post['ID'] );
			if ( !empty( $topic_id ) ) {
				bbp_update_topic( $topic_id );
			}
		}
	}
}