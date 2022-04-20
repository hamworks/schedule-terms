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
	 * The single instance of the class.
	 *
	 * @param string $meta_key The meta key.
	 */
	public function __construct( string $meta_key ) {
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
