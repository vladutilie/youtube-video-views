<?php

class YT_Video_ViewsTest extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->class_instance = new YT_Video_Views();
        $this->class_instance->activate();
    }

    public function test_event_is_scheduled_after_activation()
    {
        $time = wp_next_scheduled('ytvv_sync');
        $this->assertIsInt($time);

        $option = get_option('ytvv_option');
        $this->assertIsInt($option);
    }

    public function test_option_does_not_exist_after_deactivation()
    {
        $this->class_instance->deactivate();
        $time = wp_next_scheduled('ytvv_sync');
        $this->assertFalse($time);

        $option = get_option('ytvv_option');
        $this->assertFalse($option);
    }

    public function test_views()
    {
        $expected_views = get_option('ytvv_option');
        $actual_views = $this->class_instance->get_views();
        $this->assertEquals(number_format($expected_views), $actual_views);
    }

    public function tearDown() {
        $this->class_instance->deactivate();
    }
}