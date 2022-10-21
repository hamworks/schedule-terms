<?php
/**
 * Term UI class.
 *
 * @package Schedule_Terms
 * @see https://github.com/JJJ/wp-term-meta-ui/blob/master/class-wp-term-meta-ui.php
 */

namespace HAMWORKS\WP\Schedule_Terms\Term;

use WP_Taxonomy;
use WP_Term;

/**
 * Term UI
 */
abstract class UI {

	/**
	 * Metadata key
	 *
	 * @var string
	 */
	protected $meta_key = '';

	/**
	 * No value
	 *
	 * @var string
	 */
	protected $no_value = '&#8212;';

	/**
	 *  Which taxonomies are being targeted?
	 *
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 *  Whether to show a column
	 *
	 * @var bool
	 */
	public $has_column = true;

	/**
	 *  Whether to show fields
	 *
	 * @var bool
	 */
	public $has_fields = true;

	/**
	 *  Whether to support quick edit
	 *
	 * @var bool
	 */
	public $has_quick = true;

	/**
	 * Array of labels.
	 *
	 * @var array
	 */
	protected $labels = array(
		'singular'   => '',
		'plural'     => '',
		'descrption' => '',
	);

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'initialize' ), 999 );
	}

	/**
	 * Initialize on `init` action so taxonomies are registered
	 *
	 * @since 2.0.0
	 */
	public function initialize() {
		$this->taxonomies = $this->get_taxonomies();

		if ( empty( $this->taxonomies ) ) {
			return;
		}

		$this->add_hooks();
	}

	/**
	 * Add the hooks, on the `init` action
	 *
	 * @since 2.0.0
	 */
	public function add_hooks() {
		add_action( 'create_term', array( $this, 'save_meta' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_meta' ), 10, 3 );

		add_action( "wp_ajax_{$this->meta_key}_terms", array( $this, 'ajax_update' ) );

		foreach ( $this->taxonomies as $value ) {

			// Has column?
			if ( true === $this->has_column ) {
				add_filter( "manage_edit-{$value}_columns", array( $this, 'add_column_header' ) );
				add_filter( "manage_{$value}_custom_column", array( $this, 'add_column_value' ), 10, 3 );
			}

			// Has fields?
			if ( true === $this->has_fields ) {
				add_action( "{$value}_add_form_fields", array( $this, 'add_form_field' ) );
				add_action( "{$value}_edit_form_fields", array( $this, 'edit_form_field' ) );
			}
		}

		if ( is_blog_admin() || doing_action( 'wp_ajax_inline_save_tax' ) ) {
			add_action( 'load-edit-tags.php', array( $this, 'edit_tags' ) );
		}
	}


	/**
	 * Administration area hooks
	 *
	 * @since 2.0.0
	 */
	public function edit_tags() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'help_tabs' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );

		// Quick edit.
		if ( true === $this->has_quick ) {
			add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_meta' ), 10, 3 );
		}
	}

	/** Assets ****************************************************************/

	/**
	 * Enqueue quick-edit JS
	 *
	 * @since 2.0.0
	 */
	public function enqueue_scripts() {
	}

	/**
	 * Add help tabs for this metadata
	 *
	 * @since 2.0.0
	 */
	public function help_tabs() {
	}

	/**
	 * Add help tabs for this metadata
	 *
	 * @since 2.0.0
	 */
	public function admin_head() {
	}

	/**
	 * Quick edit ajax updating
	 *
	 * @since 2.0.0
	 */
	public function ajax_update() {
	}


	/**
	 * Return the taxonomies used by this plugin
	 *
	 * @return WP_Taxonomy[]
	 */
	private function get_taxonomies(): array {
		$tag = "wp_term_{$this->meta_key}_get_taxonomies";

		/**
		 * Allow filtering of affected taxonomies
		 */
		$r = apply_filters(
			$tag,
			array(
				'show_ui' => true,
			)
		);

		return get_taxonomies( $r );
	}

	/** Columns ***************************************************************/

	/**
	 * Add the "meta_key" column to taxonomy terms list-tables
	 *
	 * @param array $columns Array of columns.
	 *
	 * @return array
	 */
	public function add_column_header( array $columns = array() ): array {
		//phpcs:ignore
		$columns[ $this->meta_key ] = $this->labels['singular'];

		return $columns;
	}

	/**
	 * Output term value.
	 *
	 * @param mixed $value meta value.
	 *
	 * @return string
	 */
	abstract protected function format_output( $value ): string;

	/**
	 * Output the value for the custom column
	 *
	 * @param string $empty Custom column output. Default empty.
	 * @param string $custom_column Name of the column.
	 * @param int    $term_id Term ID.
	 *
	 * @return string|void
	 */
	public function add_column_value( string $empty = null, string $custom_column = null, int $term_id = 0 ) {
		if ( ! filter_input( INPUT_GET, 'taxonomy' ) && ! filter_input( INPUT_POST, 'taxonomy' ) ) {
			return $empty ?? '';
		}

		// Bail if no taxonomy passed or not on the `meta_key` column.
		if ( ( $this->meta_key !== $custom_column ) || ! empty( $empty ) ) {
			return $empty ?? '';
		}

		// Get the metadata.
		$meta   = $this->get_meta( $term_id );
		$retval = $this->no_value;

		// Output HTML element if not empty.
		if ( ! empty( $meta ) ) {
			$retval = $this->format_output( $meta );
		}

		echo wp_kses_post( $retval );
	}


	/**
	 * Add `meta_key` to term when updating
	 *
	 * @param int    $term_id term ID.
	 * @param int    $tt_id term taxonomy ID.
	 * @param string $taxonomy taqonomy slug.
	 */
	public function save_meta( int $term_id = 0, int $tt_id = 0, string $taxonomy = '' ) {

		// Bail if not a target taxonomy.
		if ( ! $this->is_taxonomy( $taxonomy ) ) {
			return;
		}

		// Get the term being posted.
		$term_key = 'term-' . $this->meta_key;

		// Bail if not updating meta_key.
		$meta = filter_input( INPUT_POST, $term_key );
		$this->set_meta( $term_id, $taxonomy, $meta );
	}

	/**
	 * Set `meta_key` of a specific term
	 *
	 * @param int    $term_id term ID.
	 * @param string $taxonomy taqonomy slug.
	 * @param mixed  $meta meta value.
	 * @param bool   $clean_cache whether to clean the cache.
	 *
	 * @since 2.0.0
	 */
	public function set_meta( int $term_id = 0, string $taxonomy = '', $meta = '', bool $clean_cache = false ) {

		// No meta_key, so delete.
		if ( empty( $meta ) ) {
			delete_term_meta( $term_id, $this->meta_key );

			// Update meta_key value.
		} else {
			update_term_meta( $term_id, $this->meta_key, $meta );
		}

		// Maybe clean the term cache.
		if ( true === $clean_cache ) {
			clean_term_cache( $term_id, $taxonomy );
		}
	}

	/**
	 * Return the `meta_key` of a term
	 *
	 * @param int $term_id term ID.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function get_meta( int $term_id = 0 ) {
		return get_term_meta( $term_id, $this->meta_key, true );
	}

	/** Markup ****************************************************************/

	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @since 2.0.0
	 */
	public function add_form_field() {
		?>

		<div class="form-field term-<?php echo esc_attr( $this->meta_key ); ?>-wrap">
			<label for="term-<?php echo esc_attr( $this->meta_key ); ?>">
				<?php echo esc_html( $this->labels['singular'] ); ?>
			</label>

			<?php $this->form_field(); ?>

			<?php if ( ! empty( $this->labels['description'] ) ) : ?>

				<p class="description">
					<?php echo esc_html( $this->labels['description'] ); ?>
				</p>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Output the form field when editing an existing term
	 *
	 * @param WP_Term $term term object.
	 */
	public function edit_form_field( $term = false ) {
		?>

		<tr class="form-field term-<?php echo esc_attr( $this->meta_key ); ?>-wrap">
			<th scope="row">
				<label for="term-<?php echo esc_attr( $this->meta_key ); ?>">
					<?php echo esc_html( $this->labels['singular'] ); ?>
				</label>
			</th>
			<td>
				<?php $this->form_field( $term ); ?>

				<?php if ( ! empty( $this->labels['description'] ) ) : ?>

					<p class="description">
						<?php echo esc_html( $this->labels['description'] ); ?>
					</p>

				<?php endif; ?>

			</td>
		</tr>

		<?php
	}

	/**
	 * Output the quick-edit field
	 *
	 * @param string $column_name column name.
	 * @param string $screen screen name.
	 * @param string $name taxonomy name.
	 *
	 * @return false|void
	 * @since 2.0.0
	 */
	public function quick_edit_meta( string $column_name = '', string $screen = '', string $name = '' ) {

		// Bail if not the meta_key column on the `edit-tags` screen for a visible taxonomy.
		if ( ( $this->meta_key !== $column_name ) || ( 'edit-tags' !== $screen ) || ! $this->is_taxonomy( $name ) ) {
			return false;
		}
		?>

		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php echo esc_html( $this->labels['singular'] ); ?></span>
					<span class="input-text-wrap">

						<?php $this->quick_edit_form_field(); ?>

					</span>
				</label>
			</div>
		</fieldset>

		<?php
	}

	/**
	 * Output the form field
	 *
	 * @param WP_Term $term term object.
	 *
	 * @since 2.0.0
	 */
	protected function form_field( WP_Term $term ) {

		// Get the meta value.
		$value = isset( $term->term_id )
			? $this->get_meta( $term->term_id )
			: '';
		?>

		<input
			type="text"
			name="term-<?php echo esc_attr( $this->meta_key ); ?>"
			id="term-<?php echo esc_attr( $this->meta_key ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
		>
		<?php
	}

	/**
	 * Output the form field
	 *
	 * @since 2.0.0
	 */
	protected function quick_edit_form_field() {
		?>
		<input type="text" class="ptitle" name="term-<?php echo esc_attr( $this->meta_key ); ?>" value="">
		<?php
	}



	/** Helpers ***************************************************************/

	/**
	 * Compare some taxonomies against the ones for this term meta.
	 *
	 * @param string[]|string $taxonomies taxonomy names.
	 */
	private function is_taxonomy( $taxonomies = array() ): bool {
		if ( empty( $taxonomies ) ) {
			return false;
		}

		$taxonomies = (array) $taxonomies;

		$intersect = array_intersect( $taxonomies, $this->taxonomies );

		return ! empty( $intersect );
	}
}
