<?php
/**
 * The Term Meta class.
 */
namespace HAMWORKS\WP\Schedule_Terms;

/**
 *  Term Meta class.
 */
class Term_Meta {

	/**
	 * The single instance of the class.
	 */
	public function __construct( $meta_key ) {
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
