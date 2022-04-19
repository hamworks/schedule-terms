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

		foreach ( get_post_types() as $post_type ) {
			$schema = array(
				'items' => array(
					'type'       => 'object',
					'properties' => array(
						'term'    => array(
							'type' => 'string',
						),
						'is_active' => array(
							'type' => 'boolean',
						),
						'datetime' => array(
							'type'   => 'string',
							'format' => 'datetime',
						),
					),
				),
			);
			register_post_meta(
				$post_type,
				'use_schedule_set_attach_datetime',
				array(
					'single'            => true,
					'type'         => 'array',
					'show_in_rest' => array(
						'schema' => $schema,
					),
				)
			);

			register_post_meta(
				$post_type,
				'use_schedule_set_detach_datetime',
				array(

					'single'            => true,
					'type'         => 'array',
					'show_in_rest' => array(
						'schema' => $schema,
					),
				)
			);
		}
	}

}
