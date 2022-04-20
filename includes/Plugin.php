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
		$meta_key = 'use_schedule';
		new Assets();
		new Term_UI( __DIR__, $meta_key );
		new Term_Meta( $meta_key );
		new Post_Meta( 'schedule_terms' );
	}

}
