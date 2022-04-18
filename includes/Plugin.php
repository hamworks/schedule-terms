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
		new Assets();
	}

}
