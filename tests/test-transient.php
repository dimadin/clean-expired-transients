<?php
/**
 * Class Tests_CET_Transient
 *
 * @package Clean_Expired_Transients
 * @subpackage Test
 */

/**
 * Test case for transients.
 *
 * @since 1.3
 */
class Tests_CET_Transient extends WP_UnitTestCase {
	/**
	 * Test default behaviour.
	 *
	 * @since 1.3
	 * @access public
	 */
	public function test_without_cleaning() {
		// Setting transient should be true
		$this->assertTrue( set_transient( 'key1', 'value1', 5 ) );

		// Direct retrieval of transient value should return setted value
		$this->assertEquals( get_option( '_transient_key1' ), 'value1' );

		// Direct retrieval of transient timeout should be integer and less than current time`
		$raw_key1_timeout_before_sleep = get_option( '_transient_timeout_key1' );
		$this->assertTrue( is_int( $raw_key1_timeout_before_sleep ) );
		$this->assertGreaterThan( time(), $raw_key1_timeout_before_sleep );

		// Getting of transient should return setted value
		$this->assertEquals( get_transient( 'key1' ), 'value1' );

		// Sleep for two minutes
		sleep( 2 * MINUTE_IN_SECONDS );

		// Direct retrieval of transient value should return setted value
		$this->assertEquals( get_option( '_transient_key1' ), 'value1' );

		// Direct retrieval of transient timeout should be integer and less than current time`
		$raw_key1_timeout_after_sleep = get_option( '_transient_timeout_key1' );
		$this->assertTrue( is_int( $raw_key1_timeout_after_sleep ) );
		$this->assertLessThan( time(), $raw_key1_timeout_after_sleep );

		// Getting of expired transient should be false
		$this->assertFalse( get_transient( 'key1' ) );

		// Direct retrieval of expired transient value should be false
		$this->assertFalse( get_option( '_transient_key1' ) );

		// Direct retrieval of expired transient timeout should be false
		$this->assertFalse( get_option( '_transient_timeout_key1' ) );
	}

	/**
	 * Test behaviour when there is cleaning.
	 *
	 * @since 1.3
	 * @access public
	 */
	public function test_with_cleaning() {
		// Setting transient should be true
		$this->assertTrue( set_transient( 'key2', 'value2', 5 ) );

		// Direct retrieval of transient value should return setted value
		$this->assertEquals( get_option( '_transient_key2' ), 'value2' );

		// Direct retrieval of transient timeout should be integer and less than current time`
		$raw_key2_timeout_before_sleep = get_option( '_transient_timeout_key2' );
		$this->assertTrue( is_int( $raw_key2_timeout_before_sleep ) );
		$this->assertGreaterThan( time(), $raw_key2_timeout_before_sleep );

		// Getting of transient should return setted value
		$this->assertEquals( get_transient( 'key2' ), 'value2' );

		// Sleep for two minutes
		sleep( 2 * MINUTE_IN_SECONDS );

		// Do cleaning
		Clean_Expired_Transients::clean();

		// Direct retrieval of expired and cleaned transient value should be false
		$this->assertFalse( get_option( '_transient_key2' ) );

		// Direct retrieval of expired and cleaned transient timeout should be false
		$this->assertFalse( get_option( '_transient_timeout_key2' ) );

		// Getting of expired transient and cleaned should be false
		$this->assertFalse( get_transient( 'key2' ) );

		// Direct retrieval of expired and cleaned transient value should be false
		$this->assertFalse( get_option( '_transient_key2' ) );

		// Direct retrieval of expired and cleaned transient timeout should be false
		$this->assertFalse( get_option( '_transient_timeout_key2' ) );
	}
}
