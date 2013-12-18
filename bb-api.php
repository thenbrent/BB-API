<?php
/*
Plugin Name: bbPress JSON API
Description: Extend the <a href="">WP API</a> to create a JSON-based REST API for bbPress forums, topics & replies.
Author: Brent Shepherd
Author URI: http://brent.io
Version: 1.0

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @package bbPress JSON API
 * @version 1.0
 * @author Brent Shepherd <inline@brent.io>
 * @copyright Copyright (c) 2013 Brent Shepherd
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */


/**
 * Register the default JSON API filters
 *
 * Order is important here, because a topic has many replies, the $bbp_json_replies global
 * needs to be set before creating an instace of 
 *
 * @internal This will live in default-filters.php
 */
function bbp_json_api_filters( $server ) {
	global $bbp_json_forums, $bbp_json_topics, $bbp_json_replies;

	// Replies
	if ( ! class_exists( 'BBP_JSON_Replies' ) ) {
		require_once( 'lib/class-bbp-json-replies.php' );
	}

	$bbp_json_replies = new BBP_JSON_Replies( $server );

	// Topics
	if ( ! class_exists( 'BBP_JSON_Topics' ) ) {
		require_once( 'lib/class-bbp-json-topics.php' );
	}

	$bbp_json_topics = new BBP_JSON_Topics( $server );

	// Forums
	if ( ! class_exists( 'BBP_JSON_Forums' ) ) {
		require_once( 'lib/class-bbp-json-forums.php' );
	}

	$bbp_json_forums = new BBP_JSON_Forums( $server );
}
add_action( 'wp_json_server_before_serve', 'bbp_json_api_filters', 10, 1 );
