<?php
/**
 * Test for Term_Manager class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms\Tests;

use HAMWORKS\WP\Schedule_Terms\Term_Manager;
use WP_UnitTestCase;

/**
 * Test Class.
 */
class Term_Manager_Test extends WP_UnitTestCase {

	/**
	 * Test for Term_Manager::update_post_term_relations().
	 */
	public function test_update_post_term_relations() {
		$term_manager = new Term_Manager( 'schedule_terms', 'schedule_terms_active', strtotime( '2022-05-29T08:05:41+00:00' ) );

		$category_id = $this->factory()->category->create( array( 'name' => 'category-1' ) );
		update_term_meta( $category_id, 'schedule_terms_active', true );

		$post_id = $this->factory()->post->create();
		add_post_meta(
			$post_id,
			'schedule_terms',
			array(
				'type'     => 'attach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2020-07-05T22:09:28+09:00',
			),
			false
		);

		add_post_meta(
			$post_id,
			'schedule_terms',
			array(
				'type'     => 'detach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2022-07-05T22:09:28+09:00',
			),
			false
		);

		$term_manager->update_post_term_relations( $post_id );
		$this->assertTrue( has_category( 'category-1', $post_id ) );

		$reflection = new \ReflectionClass( $term_manager );
		$time       = $reflection->getProperty( 'time' );
		$time->setAccessible( true );
		$time->setValue( $term_manager, strtotime( '2023-05-29T08:05:41+00:00' ) );
		$term_manager->update_post_term_relations( $post_id );
		$this->assertFalse( has_category( 'category-1', $post_id ) );
	}

	/**
	 * Test for Term_Manager::update_schedule().
	 */
	public function test_update_schedule() {
		$term_manager = new Term_Manager( 'schedule_terms', 'schedule_terms_active', strtotime( '2022-05-29T08:05:41+00:00' ) );

		foreach ( range( 1, 2 ) as $index ) {
			$category_id = $this->factory()->category->create( array( 'name' => 'category-' . $index ) );
			update_term_meta( $category_id, 'schedule_terms_active', true );
		}

		$post_id = $this->factory()->post->create();
		add_post_meta(
			$post_id,
			'schedule_terms',
			array(
				'type'     => 'attach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2022-05-29T08:05:40+00:00',
			),
			false
		);
		add_post_meta(
			$post_id,
			'schedule_terms',
			array(
				'type'     => 'detach',
				'taxonomy' => 'category',
				'term'     => 'category-1',
				'datetime' => '2022-05-29T08:05:42+00:00',
			),
			false
		);
		add_post_meta(
			$post_id,
			'schedule_terms',
			array(
				'type'     => 'attach',
				'taxonomy' => 'category',
				'term'     => 'category-2',
				'datetime' => '2022-05-29T08:05:42+00:00',
			),
			false
		);
		add_post_meta(
			$post_id,
			'schedule_terms',
			array(
				'type'     => 'detach',
				'taxonomy' => 'category',
				'term'     => 'category-2',
				'datetime' => '2022-05-29T08:05:43+00:00',
			),
			false
		);
		$term_manager->update_schedule( $post_id );

		$events = array_filter(
			self::get_events(),
			function ( $event ) {
				return Term_Manager::SCHEDULED_HOOK_NAME === $event->hook;
			}
		);

		$this->assertEquals( 3, count( $events ) );
	}

	/**
	 * Get events.
	 *
	 * @return array
	 */
	public static function get_events(): array {
		$crons  = _get_cron_array();
		$events = array();
		foreach ( $crons as $time => $hooks ) {
			foreach ( $hooks as $hook => $hook_events ) {
				foreach ( $hook_events as $sig => $data ) {
					$events[] = (object) array(
						'hook'     => $hook,
						'time'     => $time,
						'sig'      => $sig,
						'args'     => $data['args'],
						'schedule' => $data['schedule'],
					);
				}
			}
		}

		return $events;
	}

}
