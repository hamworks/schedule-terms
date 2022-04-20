<?php
/**
 * Post_Meta Class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

/**
 * Register post meta.
 */
class Post_Meta {

	/**
	 * Constructor.
	 *
	 * @param string $meta_key post meta key.
	 */
	public function __construct( string $meta_key ) {
		foreach ( get_post_types() as $post_type ) {
			$schema = array(
				'type'       => 'object',
				'properties' => array(
					'term'     => array(
						'type' => 'string',
					),
					'type'     => array(
						'type' => 'string',
						'enum' => array( 'attach', 'detach' ),
					),
					'datetime' => array(
						'type'   => 'string',
						'format' => 'datetime',
					),
				),
			);

			register_post_meta(
				$post_type,
				$meta_key,
				array(

					'single'       => false,
					'type'         => 'object',
					'show_in_rest' => array(
						'schema' => $schema,
					),
				)
			);
		}
	}
}
