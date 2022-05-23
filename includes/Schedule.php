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
	 * Meta value.
	 *
	 * @var array{ type: string, taxonomy: string, term: string, datetime: string }
	 */
	private $values;

	/**
	 * Constructor.
	 *
	 * @param array{ type: string, taxonomy: string, term: string, datetime: string } $values values.
	 */
	public function __construct( array $values ) {
		$this->values   = $values;
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
	 * @return ?WP_Term
	 */
	public function get_term(): ?WP_Term {
		$term = get_term_by( 'slug', $this->term, $this->taxonomy );
		if ( is_wp_error( $term ) ) {
			return null;
		}

		return $term;
	}

	/**
	 * Get meta value.
	 *
	 * @return array
	 */
	public function get_values(): array {
		return $this->values;
	}
}
