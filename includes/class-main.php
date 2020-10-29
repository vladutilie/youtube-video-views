<?php

namespace YTVV\Includes;

class Main {

	protected $loader;

	const VERSION = '1.0.0';

	public function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Includes the files dependencies.
	 *
	 * @since 1.0.0
	 *
	 * @see plugin_dir_path
	 * @link https://developer.wordpress.org/reference/functions/plugin_dir_path/
	 */
	private function load_dependencies() {
		$plugin_dir = plugin_dir_path( __FILE__ );
		require_once $plugin_dir . 'class-loader.php';
	}

	/**
	 * Defines the hooks.
	 *
	 * @since 1.0.0
	 *
	 * @see add_shortcode
	 * @link https://developer.wordpress.org/reference/functions/add_shortcode/
	 */
	private function define_hooks() {
		$this->loader = new Loader();
		$this->loader->add_action( 'plugins_loaded', $this, 'load_textdomain' );
		$this->loader->add_action( 'ytvv_sync', $this, 'sync' );

		add_shortcode( 'yt_views', [ $this, 'get_views' ] );
	}

	/**
	 * Loads translations.
	 *
	 * @since 1.0.0
	 *
	 * @see load_plugin_textdomain function is relied on
	 * @link https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
	 *
	 * @see plugin_basename function is relied on
	 * @link https://developer.wordpress.org/reference/functions/plugin_basename/
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'youtube-video-views', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
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
			'epCPaHwhW5g', // Împotriva fabricii de doctorate | Emilia Șercan
			'GyIrq4jJ-Ak', // Copiii dispăruți și lumea pe care nu o vedem | Camelia Cavadia
			'8yA4Q_MPbZ8', // A educa înseamnă a călăuzi | Carmen Ion
			'5MbSwpcPMuQ', // No one will ask you about Harry Potter | Elizabeth Sagan
			'9u0-ZoSUyKY', // Îndrăznește să mănânci cum îți place | Mihaela Bilic
			'zmxiGreSRCM', // Performance | Patricia Labou
			'zi8OtaiEA5Q', // Always | Adela Mureșan
		);
		$videos    = implode( ',', $video_ids );
		$api_key   = 'AIzaSyCLoQpzA6SQ9gUcOda_UQoYM8c17f2glgo';

		// $url = plugin_dir_url( __FILE__ ) . 'data.json';
		$url  = 'https://www.googleapis.com/youtube/v3/videos?part=statistics&id=' . urlencode( $videos ) . '&key=' . $api_key;
		$args = array(
			'compress' => true,
			'headers'  => array(
				'Accept' => 'application/json',
			),
		);

		$request = wp_remote_get( $url, $args );
		$data    = json_decode( $request['body'] );
		$views   = 33100 + 4800; // De la aftermovie (Highlights) și de la clipul cu Vlad Voiculescu
		foreach ( $data->items as $item ) {
			$views += absint( $item->statistics->viewCount );
		}
		update_option( 'ytvv_option', $views, false );
	}

	public function get_views() {
		$views = get_option( 'ytvv_option' );

		return number_format( $views );
	}

	/**
	 * Runs the world!
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
}