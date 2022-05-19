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
	 * Post meta key.
	 *
	 * @var string
	 */
	private $meta_key;

	/**
	 * Constructor.
	 *
	 * @param string $meta_key post meta key.
	 */
	public function __construct( string $meta_key ) {
		$this->meta_key = $meta_key;
		add_action( 'init', array( $this, 'init' ), 100 );
	}

	/**
	 * Init.
	 */
	public function init() {
		foreach ( get_post_types() as $post_type ) {
			$schema = array(
				'type'       => 'object',
				'properties' => array(
					'term'     => array(
						'type' => 'string',
					),
					'taxonomy' => array(
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
				$this->meta_key,
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
