<?php
/**
 * Plugin class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

/**
 * Plugin base class.
 */
class Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$term_meta_key = 'schedule_terms_active';
		$post_meta_key = 'schedule_terms';
		new Assets();
		new Term_UI( __DIR__, $term_meta_key );
		new Term_Meta( $term_meta_key );
		new Post_Meta( $post_meta_key );
		new Term_Manager( $post_meta_key, $term_meta_key );
	}
}
