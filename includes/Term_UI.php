<?php
/**
 * Term_UI class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

use JJJ\WP\Term\Meta\UI;

/**
 * Term meta UI controller.
 */
class Term_UI extends UI {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '2.0.0';

	/**
	 * Database version.
	 *
	 * @var string
	 */
	public $db_version = 202204190000;

	/**
	 * Metadata key.
	 *
	 * @var string
	 */
	public $meta_key = '';

	/**
	 * Constrouctor.
	 *
	 * @param string $file Plugin file.
	 * @param string $meta_key Metadata key.
	 */
	public function __construct( $file, string $meta_key ) {
		$this->meta_key = $meta_key;
		$this->labels   = array(
			'singular' => esc_html__( 'Scheduled', 'schedule-posts' ),
		);

		parent::__construct( $file );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/admin.asset.php';

		foreach ( $asset_file['dependencies'] as $style ) {
			wp_enqueue_style( $style );
		}

		wp_enqueue_script(
			'schedule-terms-admin',
			plugins_url( 'build/admin.js', __DIR__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}

	/**
	 * Output.
	 *
	 * @param mixed $meta term meta.
	 */
	protected function format_output( $meta = '' ) {
		if ( $meta ) {
			?>
			<span data-schedule-terms-active><?php esc_html_e( 'Use Schedule', 'schedule-posts' ); ?></span>
			<?php
		}
	}

	/**
	 * Output the form field
	 *
	 * @param \WP_Term|null $term term meta.
	 */
	protected function form_field( $term = null ) {
		$value = isset( $term->term_id )
			? $this->get_meta( $term->term_id )
			: '';

		?>
		<input
			type="checkbox"
			name="term-<?php echo esc_attr( $this->meta_key ); ?>"
			id="term-<?php echo esc_attr( $this->meta_key ); ?>"
			<?php checked( ! ! $value, true, true ); ?>
		/>
		<?php
	}

	/**
	 * Output the form field
	 */
	protected function quick_edit_form_field() {
		?>
		<input type="checkbox" name="term-<?php echo esc_attr( $this->meta_key ); ?>">
		<?php
	}


}
