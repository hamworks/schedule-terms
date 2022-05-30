<?php
/**
 * Term Manager.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

use WP_Term;

/**
 * Term attach or detach from post.
 */
class Term_Manager {

	public const SCHEDULED_HOOK_NAME = 'schedule_terms_update_post_term_relations';

	/**
	 * Post meta key for save term info.
	 *
	 * @var string
	 */
	private $post_meta_key;


	/**
	 * Term meta key.
	 *
	 * @var string
	 */
	private $term_meta_key;

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
	 * @param string   $term_meta_key Term meta key.
	 * @param int|null $time timestamp.
	 */
	public function __construct( string $post_meta_key, string $term_meta_key, int $time = null ) {
		$this->post_meta_key = $post_meta_key;
		$this->term_meta_key = $term_meta_key;
		$this->time          = $time ?? time();
		add_action( 'wp_after_insert_post', array( $this, 'update_post_term_relations' ), 100, 1 );
		add_action( 'wp_after_insert_post', array( $this, 'update_schedule' ), 100, 1 );
		add_action( self::SCHEDULED_HOOK_NAME, array( $this, 'update_post_term_relations' ), 10, 4 );
	}

	/**
	 * Check term scheduling activated.
	 *
	 * @param WP_Term | null $term WP_Term.
	 *
	 * @return bool
	 */
	private function is_active_term( ?WP_Term $term ): bool {
		if ( ! $term ) {
			return false;
		}

		return ! ! get_term_meta( $term->term_id, $this->term_meta_key, true );
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
	 * Filter schedules.
	 *
	 * @param Schedule[] $schedules Schedules.
	 * @param string     $type Schedule type.
	 *
	 * @return Schedule[]
	 */
	private function filter_schedules( array $schedules, string $type ): array {
		return array_filter(
			$schedules,
			function ( $schedule ) use ( $type ) {
				return $type === $schedule->get_type();
			}
		);
	}

	/**
	 * Update post term relations.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return void
	 */
	public function update_post_term_relations( int $post_id ) {
		$schedules = $this->get_schedules( $post_id );

		// Attach.
		foreach ( $this->filter_schedules( $schedules, Schedule::ATTACH ) as $schedule ) {
			if ( $schedule->is_expired( $this->time ) && $this->is_active_term( $schedule->get_wp_term() ) ) {
				wp_set_post_terms( $post_id, array( $schedule->get_wp_term()->term_id ), $schedule->get_taxonomy(), true );
			}
		}

		// Detach.
		foreach ( $this->filter_schedules( $schedules, Schedule::DETACH ) as $schedule ) {
			if ( $schedule->is_expired( $this->time ) && $this->is_active_term( $schedule->get_wp_term() ) ) {
				wp_remove_object_terms( $post_id, $schedule->get_wp_term()->term_id, $schedule->get_taxonomy() );
			}
		}
	}

	/**
	 * Scheduled update.
	 *
	 * @param int $post_id post ID.
	 */
	public function update_schedule( int $post_id ) {
		foreach ( $this->get_schedules( $post_id ) as $schedule ) {
			if ( ! $schedule->is_expired( $this->time ) ) {
				$time   = $schedule->get_timestamp();
				$params = array( $post_id, array( $schedule->get_type() ), $schedule->get_taxonomy(), $schedule->get_term() );
				wp_clear_scheduled_hook( self::SCHEDULED_HOOK_NAME, $params );
				wp_schedule_single_event( $time, self::SCHEDULED_HOOK_NAME, $params );
			}
		}
	}
}
