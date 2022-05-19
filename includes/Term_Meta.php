<?php
/**
 * The Term Meta class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

/**
 *  Term Meta class.
 */
class Term_Meta {
	/**
	 * Term meta key.
	 *
	 * @var string
	 */
	private $meta_key;

	/**
	 * The single instance of the class.
	 *
	 * @param string $meta_key The meta key.
	 */
	public function __construct( string $meta_key ) {
		$this->meta_key = $meta_key;
		add_action( 'init', array( $this, 'init' ), 100 );
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->register_term_meta( $this->meta_key );
	}

	/**
	 * Register Term meta.s
	 *
	 * @param string $meta_key The meta key.
	 */
	public function register_term_meta( string $meta_key ) {
		foreach ( get_taxonomies() as $taxonomy ) {
			register_term_meta(
				$taxonomy,
				$meta_key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'boolean',
					'sanitize_callback' => function ( $value ) {
						return (bool) $value;
					},
				)
			);
		}
	}
}
