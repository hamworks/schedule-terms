<?php
/**
 * Term Manager.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

/**
 * Term attach or detach from post.
 */
class Term_Manager {

	/**
	 * Post meta name for save term info.
	 *
	 * @var string
	 */
	private $post_meta_key;

	/**
	 * Constructor.
	 *
	 * @param string $post_meta_key Post meta key.
	 */
	public function __construct( string $post_meta_key ) {
		$this->post_meta_key = $post_meta_key;

		add_action( 'wp_after_insert_post', array( $this, 'update_schedule' ), 100, 1 );
		add_action( "${post_meta_key}_update_terms", array( $this, 'update_post_term_relations' ), 10, 4 );
	}

	/**
	 * Get term schedules.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	private function get_schedules( int $post_id ): array {
		$meta_values = get_post_meta( $post_id, $this->post_meta_key, false );
		if ( empty( $meta_values ) ) {
			return array();
		}

		$attach_terms = array_filter(
			$meta_values,
			function ( $value ) {
				return 'attach' === $value['type'];
			}
		);

		$detach_terms = array_filter(
			$meta_values,
			function ( $value ) {
				return 'detach' === $value['type'];
			}
		);

		return array_merge( $attach_terms, $detach_terms );
	}

	/**
	 * Get unixtime.
	 *
	 * @param string $iso_datetime ISO 8601 formatted datetime.
	 *
	 * @return int
	 */
	private function get_timestamp( string $iso_datetime ): int {
		try {
			$date_time = new \DateTime( $iso_datetime );
		} catch ( \Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		return $date_time->getTimestamp();
	}

	/**
	 * Scheduled update.
	 *
	 * @param int $post_id post ID.
	 */
	public function update_schedule( int $post_id ) {
		$post_meta_key = $this->post_meta_key;
		wp_clear_scheduled_hook( "${post_meta_key}_update_terms", array( $post_id ) );

		$this->update_post_term_relations( $post_id );

		foreach ( $this->get_schedules( $post_id ) as $meta_value ) {
			if ( $meta_value ) {
				$time = $this->get_timestamp( $meta_value['datetime'] );
				if ( time() < $time ) {
					wp_schedule_single_event( $time, "${post_meta_key}_update_terms", array( $post_id ) );
				}
			}
		}
	}

	/**
	 * Update post terms relation.
	 *
	 * @param int $post_id post ID.
	 *
	 * @return void
	 */
	public function update_post_term_relations( int $post_id ) {
		foreach ( $this->get_schedules( $post_id ) as $meta_value ) {
			if ( $meta_value ) {
				$time = $this->get_timestamp( $meta_value['datetime'] );
				$term = get_term_by( 'slug', $meta_value['term'], $meta_value['taxonomy'] );
				if ( time() >= $time ) {
					if ( 'attach' === $meta_value['type'] ) {
						wp_set_post_terms( $post_id, array( $term->term_id ), $meta_value['taxonomy'], true );
					} else {
						wp_remove_object_terms( $post_id, $term->term_id, $meta_value['taxonomy'] );
					}
				}
			}
		}
	}
}
