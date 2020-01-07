<?php
// Plugin name: YouTube Video Views

defined( 'ABSPATH' ) || exit;

class YT_Video_Views {

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'ytvv_sync', array( $this, 'sync' ) );
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
		add_shortcode( 'yt_views', array( $this, 'get_views' ) );
	}

	public function add_cron_interval( $schedules ) {
		$schedules['ten_seconds'] = array(
			'interval' => 20,
			'display'  => esc_html__( 'Every 5 seconds' ),
		);
		return $schedules;
	}

	public function schd() {
		if ( ! wp_next_scheduled( 'ytvv_sync' ) ) {
			wp_schedule_event( time(), 'ten_seconds', 'ytvv_sync' );
		}
	}

	public function activate() {
		$this->sync();
		add_option( 'ytvv_option', 0, '', false );
		if ( ! wp_next_scheduled( 'ytvv_sync' ) ) {
			wp_schedule_event( time(), 'ten_seconds', 'ytvv_sync' );
		}
	}

	public function deactivate() {
		delete_option( 'ytvv_option' );
		$timestamp = wp_next_scheduled( 'ytvv_sync' );
		wp_unschedule_event( $timestamp, 'ytvv_sync' );
		wp_clear_scheduled_hook( 'ytvv_sync' );
	}

	public function sync() {
		/*
		curl \
		'https://www.googleapis.com/youtube/v3/videos?part=statistics&id=GyIrq4jJ-Ak%2CepCPaHwhW5g%2C8yA4Q_MPbZ8%2C9u0-ZoSUyKY%2C5MbSwpcPMuQ&key=[YOUR_API_KEY]' \
		--header 'Authorization: Bearer [YOUR_ACCESS_TOKEN]' \
		--header 'Accept: application/json' \
		--compressed
		*/
		
		$video_ids = 'GyIrq4jJ-Ak,epCPaHwhW5g,8yA4Q_MPbZ8,9u0-ZoSUyKY,5MbSwpcPMuQ';
		$api_key   = 'AIzaSyCLoQpzA6SQ9gUcOda_UQoYM8c17f2glgo';

		$url  = plugin_dir_url( __FILE__ ) . 'data.json'; // https://www.googleapis.com/youtube/v3/videos?part=statistics&id=' . $video_ids . '&key=' . $api_key
		$args = array(
			'sslverify' => false,
			'timeout'   => 10,
		);

		$request = wp_remote_get( $url, $args );
		$data    = json_decode( $request['body'] );
		$views   = 0;
		foreach ( $data->items as $item ) {
			$views += mt_rand(500, 2000); //absint( $item->statistics->viewCount );
		}
		update_option( 'ytvv_option', $views, false );
	}

	public function get_views() {
		$views = get_option( 'ytvv_option' );
		return number_format( $views );
	}
}

$ytvv = new YT_Video_Views();
