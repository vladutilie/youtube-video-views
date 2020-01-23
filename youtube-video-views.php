<?php
/**
 * Plugin Name: Youtube Vide Views
 * Plugin URI:  N/A
 * Description: Shows youtube videos views on your website using a shortcode.
 * Version:     1.0.0
 * Author:      Vlăduț Ilie
 * Author URI:  https://vladilie.ro/
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: youtube-video-views
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

class YT_Video_Views {

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'ytvv_sync', array( $this, 'sync' ) );
		add_shortcode( 'yt_views', array( $this, 'get_views' ) );
	}

	public function activate() {
		$this->sync();
		add_option( 'ytvv_option', 0, '', false );
		if ( ! wp_next_scheduled( 'ytvv_sync' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'ytvv_sync' );
		}
	}

	public function deactivate() {
		delete_option( 'ytvv_option' );
		$timestamp = wp_next_scheduled( 'ytvv_sync' );
		wp_unschedule_event( $timestamp, 'ytvv_sync' );
		wp_clear_scheduled_hook( 'ytvv_sync' );
	}

	public function sync() {
		$video_ids = array(
			'epCPaHwhW5g', // Împotriva fabricii de doctorate | Emilia Șercan.
			'GyIrq4jJ-Ak', // Copiii dispăruți și lumea pe care nu o vedem | Camelia Cavadia.
			'8yA4Q_MPbZ8', // A educa înseamnă a călăuzi | Carmen Ion.
			'5MbSwpcPMuQ', // No one will ask you about Harry Potter | Elizabeth Sagan.
			'9u0-ZoSUyKY', // Îndrăznește să mănânci cum îți place | Mihaela Bilic.
			'zmxiGreSRCM', // Performance | Patricia Labou.
			'zi8OtaiEA5Q', // Always | Adela Mureșan.
		);
		$videos    = implode( ',', $video_ids );
		$api_key   = 'AIzaSyCLoQpzA6SQ9gUcOda_UQoYM8c17f2glgo';

		// $url = plugin_dir_url( __FILE__ ) . 'data.json';
		$url  = 'https://www.googleapis.com/youtube/v3/videos?part=statistics&id=' . urlencode( $videos ) . '&key=' . $api_key;
		$args = array(
			'compress' => true,
			'headers' => array(
				'Accept' => 'application/json',
			),
		);

		$request = wp_remote_get( $url, $args );
		$data    = json_decode( $request['body'] );
		$views   = 0;
		foreach ( $data->items as $item ) {
			$views += absint( $item->statistics->viewCount );
		}
		update_option( 'ytvv_option', $views, false );
	}

	public function get_views() {
		$views = get_option( 'ytvv_option' );
		return number_format( $views );
	}
}

new YT_Video_Views();
