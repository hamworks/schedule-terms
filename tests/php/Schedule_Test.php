<?php
/**
 * Test for Schedule class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms\Tests;

use HAMWORKS\WP\Schedule_Terms\Schedule;
use WP_UnitTestCase;

/**
 * Test Class.
 */
class Schedule_Test extends WP_UnitTestCase {

	/**
	 * Test for Schedule::is_expired().
	 */
	public function test_is_expired() {
		$schedule = new Schedule(
			array(
				'type'     => 'attach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2012-07-05T22:09:28+09:00',
			)
		);
		$this->assertTrue( $schedule->is_expired( strtotime( '2020-07-05T22:09:28+09:00' ) ) );
		$this->assertFalse( $schedule->is_expired( strtotime( '2000-07-05T22:09:28+09:00' ) ) );
	}

	/**
	 * Test for Schedule::get_timestamp().
	 */
	public function test_get_timestamp() {
		$schedule = new Schedule(
			array(
				'type'     => 'attach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2012-07-05T22:09:28+09:00',
			)
		);

		$this->assertEquals(
			strtotime( '2012-07-05T22:09:28+09:00' ),
			$schedule->get_timestamp()
		);
	}

	/**
	 * Test for Schedule::get_wp_term().
	 */
	public function test_get_wp_term() {
		$schedule = new Schedule(
			array(
				'type'     => 'attach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2012-07-05T22:09:28+09:00',
			)
		);

		$this->assertNull( $schedule->get_wp_term() );

		$this->factory()->category->create(
			array(
				'slug' => 'category-1',
			)
		);

		$this->assertInstanceOf(
			'WP_Term',
			$schedule->get_wp_term()
		);
	}
}
