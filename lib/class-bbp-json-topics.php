<?php
/**
 * Topic post type handlers
 *
 * @package bbPress
 * @subpackage JSON API
 */

/**
 * Topic post type handlers
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
class BBP_JSON_Topics extends WP_JSON_CustomPostType {

	/**
	 * Base route
	 *
	 * @var string
	 */
	protected $base = '/topics';

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $type = 'topic'; // bbp_get_topic_post_type()

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
	 * Register the page-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {

		$routes = parent::register_routes( $routes );

		// Add topic's replies route
		$routes[ $this->base . '/(?P<id>\d+)/replies'] = array(
			array( array( $this, 'get_posts_by_parent' ), WP_JSON_Server::READABLE ),
		);

		// Add user's topics route
		$routes['/users/(?P<user>\d+)' . $this->base] = array(
			array( array( $this, 'get_users_posts' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
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
	 * Get the topics that a user created
	 *
	 * Kind of like bbp_get_user_topics_started()
	 *
	 * @see WP_JSON_Posts::get_posts()
	 */
	public function get_users_posts( $user, $context = 'view' ) {

		return parent::get_posts( array( 'author' => $user ), $context );
	}

	/**
	 * Get the topics that a user created
	 *
	 * Kind of like bbp_get_user_topics_started()
	 *
	 * @see WP_JSON_Posts::get_posts()
	 */
	public function get_posts_by_parent( $id, $context = 'view' ) {
		global $bbp_json_replies;

		return $bbp_json_replies->get_posts( array( 'post_parent' => $id ), $context );
	}

	/**
	 * When inserting a new topic, make sure the protected meta data is set correctly.
	 *
	 * We can't use WP_JSON_Posts::add_meta() here because the required meta is deemed
	 * protected by @see is_protected_meta().
	 *
	 * @see WP_JSON_Posts::insert_post()
	 * @see WP_JSON_Posts::add_meta()
	 */
	public function add_protected_meta( $post, $data, $update ) {

		if ( ! $update && $this->type == $post['post_type'] ) {

			$topic_meta = array(
				'author_ip'          => bbp_current_author_ip(),
				'forum_id'           => $post['post_parent'],
				'topic_id'           => $post['ID'],
				'voice_count'        => 1,
				'reply_count'        => 0,
				'reply_count_hidden' => 0,
				'last_reply_id'      => 0,
				'last_active_id'     => $post['ID'],
				'last_active_time'   => get_post_field( 'post_date', $post['ID'], 'db' ),
			);

			// Insert topic meta
			foreach ( $topic_meta as $meta_key => $meta_value ) {
				update_post_meta( $post['ID'], '_bbp_' . $meta_key, $meta_value );
			}

			// Update the forum
			if ( ! empty( $post['post_parent'] ) ) {
				bbp_update_forum( array( 'forum_id' => $forum_id ) );
			}
		}
	}
}