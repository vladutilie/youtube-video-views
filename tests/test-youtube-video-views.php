<?php

namespace YTVVTest;

use YTVV\Includes\Main;
use phpmock\phpunit\PHPMock;

class YT_Video_ViewsTest extends \PHPUnit_Framework_TestCase {

	use PHPMock;

	const CONFIG = [
		'cron_event'  => 'ytvv_sync',
		'shortcode'   => 'ytvv',
		'option_name' => 'youtube_video_views',
	];

	protected $class_instance;

	public function setUp() {
		$this->class_instance = new Main();
	}

	public function test_instantiation() {
		$this->assertEquals( $this->class_instance::VERSION, '1.0.0' );

		$config = $this->class_instance::CONFIG;
		$this->assertIsArray( $config );
		$this->assertCount( 3, $config );
		$this->assertArrayHasKey( 'cron_event', $config );
		$this->assertArrayHasKey( 'shortcode', $config );
		$this->assertArrayHasKey( 'option_name', $config );

		$this->assertEquals( self::CONFIG, $config );
	}

	public function test_hooked_methods() {
		$this->assertTrue( has_action( 'plugins_loaded' ) );
		$this->assertTrue( has_action( 'ytvv_sync' ) );
		$this->assertTrue( shortcode_exists( 'ytvv' ) );
	}

	public function test_sync() {
		$expected['body'] = new \stdClass();
		$items            = [];

		for ( $i = 1; $i <= 7; $i ++ ) {
			$item                        = new \stdClass();
			$item->statistics            = new \stdClass();
			$item->statistics->viewCount = $i;
			$items[]                     = $item;
		}

		$expected['body'] = json_encode( [ 'items' => $items ] );
		$args             = [
			'compress' => true,
			'headers'  => [
				'Accept' => 'application/json',
			],
		];

		$wp_remote_get = $this->getFunctionMock( 'YTVV\Includes', 'wp_remote_get' )
		                      ->expects( $this->once() )
		                      ->with( 'https://www.googleapis.com/youtube/v3/videos?part=statistics&id=epCPaHwhW5g%2CGyIrq4jJ-Ak%2C8yA4Q_MPbZ8%2C5MbSwpcPMuQ%2C9u0-ZoSUyKY%2CzmxiGreSRCM%2Czi8OtaiEA5Q&key=AIzaSyCLoQpzA6SQ9gUcOda_UQoYM8c17f2glgo', $args )
		                      ->willReturn( $expected );

		$absint = $this->getFunctionMock( 'YTVV\Includes', 'absint' )
		               ->expects( $this->exactly( 7 ) )
		               ->with( $this->greaterThan( 0 ) )
		               ->will( $this->returnValueMap( [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ] ) );

		$update_option = $this->getFunctionMock( 'YTVV\Includes', 'update_option' )
		                      ->expects( $this->once() )
		                      ->with( self::CONFIG['option_name'], 37900, false )
		                      ->willReturn( 37900 );

		$this->class_instance->sync();
	}

	public function test_get_views() {
		$get_option = $this->getFunctionMock( 'YTVV\Includes', 'get_option' );
		$get_option->expects( $this->once() )
		           ->with( $this->equalTo( self::CONFIG['option_name'] ) )
		           ->willReturn( 12345 );

		$result = $this->class_instance->get_views();
		$this->assertEquals( '12,345', $result );
	}

	public function tearDown() {
		$this->class_instance->deactivate();
	}
}