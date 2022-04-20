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
		add_action( "${post_meta_key}_update_terms", array( $this, 'update_terms' ), 10, 4 );
	}

	/**
	 * Scheduled update.
	 *
	 * @param int $post_id post ID.
	 */
	public function update_schedule( int $post_id ) {
		$post_meta_key = $this->post_meta_key;
		$meta_values   = get_post_meta( $post_id, $post_meta_key, false );
		wp_clear_scheduled_hook( "${post_meta_key}_update_terms", array( $post_id ) );
		foreach ( $meta_values as $meta_value ) {
			if ( $meta_value ) {
				try {
					$date_time = new \DateTime( $meta_value['datetime'] );
				} catch ( \Exception $e ) {
					wp_die( esc_html( $e->getMessage() ) );
				}
				$time = $date_time->getTimestamp();
				$term = get_term_by( 'slug', $meta_value['term'], $meta_value['taxonomy'] );

				if ( time() > $time ) {
					if ( 'attach' === $meta_value['type'] ) {
						wp_set_post_terms( $post_id, array( $term->term_id ), $meta_value['taxonomy'], true );
					} else {
						$term = get_term_by( 'slug', $meta_value['term'], $meta_value['taxonomy'] );
						wp_remove_object_terms( $post_id, $term->term_id, $meta_value['taxonomy'] );
					}
				} else {
					wp_schedule_single_event( $time, "${post_meta_key}_update_terms", array( $post_id ) );
				}
			}
		}
	}

	/**
	 * @param int $post_id post ID.
	 *
	 * @return void
	 */
	public function update_terms( int $post_id ) {
		$post_meta_key = $this->post_meta_key;
		$meta_values   = get_post_meta( $post_id, $post_meta_key, false );
		// TODO: attach から処理するように.
		foreach ( $meta_values as $meta_value ) {
			if ( $meta_value ) {
				try {
					$date_time = new \DateTime( $meta_value['datetime'] );
				} catch ( \Exception $e ) {
					wp_die( esc_html( $e->getMessage() ) );
				}
				$time = $date_time->getTimestamp();
				$term = get_term_by( 'slug', $meta_value['term'], $meta_value['taxonomy'] );

				if ( time() > $time ) {
					if ( 'attach' === $meta_value['type'] ) {
						wp_set_post_terms( $post_id, array( $term->term_id ), $meta_value['taxonomy'], true );
					} else {
						$term = get_term_by( 'slug', $meta_value['term'], $meta_value['taxonomy'] );
						wp_remove_object_terms( $post_id, $term->term_id, $meta_value['taxonomy'] );
					}
				}
			}
		}
	}
}
