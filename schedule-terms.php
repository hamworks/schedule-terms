<?php
/**
 * Plugin Name:     Schedule Terms
 * Plugin URI:      https://github.com/hamworks/schedule-terms
 * Description:     Automatically set and unset the term when the time is up.
 * Author:          Toro_Unit, HAMWORKS
 * Author URI:      https://ham.works
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     schedule-terms
 * Domain Path:     /languages
 * Version: 1.3.2
 *
 * @package Schedule_Terms
 */

use HAMWORKS\WP\Schedule_Terms\Plugin;

require_once __DIR__ . '/vendor/autoload.php';

new Plugin();
