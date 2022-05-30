<?php
/**
 * Schedule Class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms;

use WP_Term;

/**
 * Schedule Object
 */
class Schedule {

	public const ATTACH = 'attach';
	public const DETACH = 'detach';

	/**
	 * Schedule tyoe.
	 *
	 * @var 'attach'|'detach'
	 */
	private $type;

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	private $taxonomy;

	/**
	 * Term slug.
	 *
	 * @var string
	 */
	private $term;

	/**
	 * ISO 8601 formatted datetime.
	 *
	 * @var string
	 */
	private $datetime;

	/**
	 * WP_Term object cache.
	 *
	 * @var WP_Term
	 */
	private $wp_term;

	/**
	 * Constructor.
	 *
	 * @param array{ type: string, taxonomy: string, term: string, datetime: string } $values values.
	 */
	public function __construct( array $values ) {
		$this->type     = $values['type'];
		$this->taxonomy = $values['taxonomy'];
		$this->term     = $values['term'];
		$this->datetime = $values['datetime'];
	}

	/**
	 * Get unixtime.
	 *
	 * @return int
	 */
	public function get_timestamp(): int {
		try {
			$date_time = new \DateTime( $this->datetime );
		} catch ( \Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		return $date_time->getTimestamp();
	}

	/**
	 * Check if schedule is expired.
	 *
	 * @param int $timestamp Unix timestamp.
	 *
	 * @return bool
	 */
	public function is_expired( int $timestamp ): bool {
		$this->get_timestamp();

		return $timestamp >= $this->get_timestamp();
	}

	/**
	 * Get action type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get taxonomy slug.
	 *
	 * @return string
	 */
	public function get_taxonomy(): string {
		return $this->taxonomy;
	}

	/**
	 * Get term slug.
	 *
	 * @return string
	 */
	public function get_term(): string {
		return $this->term;
	}

	/**
	 * Get WP_Term.
	 *
	 * @return ?WP_Term
	 */
	public function get_wp_term(): ?WP_Term {
		if ( $this->wp_term ) {
			return $this->wp_term;
		}

		$term = get_term_by( 'slug', $this->term, $this->taxonomy );

		if ( is_wp_error( $term ) ) {
			return null;
		}

		if ( ! $term ) {
			return null;
		}

		$this->wp_term = $term;

		return $this->wp_term;
	}
}
