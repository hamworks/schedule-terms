<?php
/**
 * Asset class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

/**
 * Class used to register style and js.
 */
class Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/index.asset.php';

		foreach ( $asset_file['dependencies'] as $style ) {
			wp_enqueue_style( $style );
		}

		wp_register_script(
			'schedule-terms',
			plugins_url( 'build/index.js', __DIR__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
		wp_enqueue_script( 'schedule-terms' );
	}

}
