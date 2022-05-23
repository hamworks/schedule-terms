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
	 * Timestamp.
	 *
	 * @var int
	 */
	private $time;

	/**
	 * Constructor.
	 *
	 * @param string   $post_meta_key Post meta key.
	 * @param int|null $time timestamp.
	 */
	public function __construct( string $post_meta_key, int $time = null ) {
		$this->post_meta_key = $post_meta_key;
		$this->time          = $time ?? time();
		add_action( 'wp_after_insert_post', array( $this, 'update_schedule' ), 100, 1 );
		add_action( 'schedule_terms_attach_post_term_relations', array( $this, 'attach_post_term_relations' ), 10, 4 );
		add_action( 'schedule_terms_detach_post_term_relations', array( $this, 'detach_post_term_relations' ), 11, 4 );
	}

	/**
	 * Get term schedules.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return Schedule[]
	 */
	private function get_schedules( int $post_id ): array {
		$meta_values = get_post_meta( $post_id, $this->post_meta_key, false );
		if ( empty( $meta_values ) ) {
			return array();
		}

		return array_map(
			function ( $value ) {
				return new Schedule( $value );
			},
			$meta_values
		);
	}

	/**
	 * Get filtered schedules.
	 *
	 * @param int    $post_id Post id.
	 * @param string $type Schedule type.
	 *
	 * @return Schedule[]
	 */
	private function get_filtered_schedules( int $post_id, string $type ): array {
		return array_filter(
			$this->get_schedules( $post_id ),
			function ( $schedule ) use ( $type ) {
				return $type === $schedule->get_type();
			}
		);
	}

	/**
	 * Scheduled update.
	 *
	 * @param int $post_id post ID.
	 */
	public function update_schedule( int $post_id ) {
		$this->attach_post_term_relations( $post_id );
		$this->detach_post_term_relations( $post_id );

		foreach ( $this->get_schedules( $post_id ) as $schedule ) {
			if ( ! $schedule->is_expired( $this->time ) ) {
				$time   = $schedule->get_timestamp();
				$params = array( $post_id, $schedule->get_taxonomy(), $schedule->get_term() );
				if ( $schedule->get_type() === 'attach' ) {
					wp_clear_scheduled_hook( 'schedule_terms_attach_post_term_relations', $params );
					wp_schedule_single_event( $time, 'schedule_terms_attach_post_term_relations', $params );
				} else {
					wp_clear_scheduled_hook( 'schedule_terms_detach_post_term_relations', $params );
					wp_schedule_single_event( $time, 'schedule_terms_detach_post_term_relations', $params );
				}
			}
		}
	}

	/**
	 * Attach post terms relation.
	 *
	 * @param int $post_id post ID.
	 *
	 * @return void
	 */
	public function attach_post_term_relations( int $post_id ) {
		foreach ( $this->get_filtered_schedules( $post_id, 'attach' ) as $schedule ) {
			if ( $schedule->is_expired( $this->time ) ) {
				$term = $schedule->get_term();
				if ( $term ) {
					wp_set_post_terms( $post_id, array( $term->term_id ), $schedule->get_taxonomy(), true );
				}
			}
		}
	}

	/**
	 * Detach post terms relation.
	 *
	 * @param int $post_id post ID.
	 *
	 * @return void
	 */
	public function detach_post_term_relations( int $post_id ) {
		foreach ( $this->get_filtered_schedules( $post_id, 'detach' ) as $schedule ) {
			if ( $schedule->is_expired( $this->time ) ) {
				$term = $schedule->get_term();
				if ( $term ) {
					wp_remove_object_terms( $post_id, $term->term_id, $schedule->get_taxonomy() );
				}
			}
		}
	}
}
