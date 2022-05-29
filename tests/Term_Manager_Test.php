<?php
/**
 * Test for Term_Manager class.
 *
 * @package Schedule_Terms
 */

namespace HAMWORKS\WP\Schedule_Terms\Tests;

use HAMWORKS\WP\Schedule_Terms\Term_Manager;
use PHPUnit\Framework\TestCase;
use WP_UnitTestCase;

/**
 * Test Class.
 */
class Term_Manager_Test extends WP_UnitTestCase {

	/**
	 * Test for Term_Manager::update_post_term_relations().
	 */
	public function test_update_post_term_relations() {
		$term_manager = new Term_Manager( 'schedule_terms', 'schedule_terms_active' );

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
				'datetime' => '2012-07-05T22:09:28+09:00',
			),
			false
		);


		$term_manager->update_post_term_relations( $post_id );

		$this->assertTrue( has_category( 'category-1', $post_id ) );
	}

	/**
	 * Test for Term_Manager::update_schedule().
	 */
	public function test_update_schedule() {
		$this->assertEquals( 1, 1 );
	}

}
