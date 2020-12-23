<?php
/**
 * YouTube Video Views main class.
 *
 * @class YTVV
 * @package YTVV\Includes
 */

namespace YTVV\Includes;

/**
 * Class Main
 *
 * @since 1.0.0
 * @package YTVV\Includes
 */
class Main {

	/**
	 * The loader that add actions and filters.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected $loader;

	/**
	 * The version of the plugin.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Configuration data.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	const CONFIG = [
		'cron_event'  => 'ytvv_sync',
		'shortcode'   => 'ytvv',
		'option_name' => 'youtube_video_views',
	];

	/**
	 * Main constructor.
	 *
	 * @since 1.0.0
	 */
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
		$this->loader->add_action( self::CONFIG['cron_event'], $this, 'sync' );

		add_shortcode( self::CONFIG['shortcode'], [ $this, 'get_views' ] );
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

	/**
	 * Activation hook. Adds options and initializes the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @see add_option
	 * @link https://developer.wordpress.org/reference/functions/add_option/
	 *
	 * @see wp_next_scheduled
	 * @link https://developer.wordpress.org/reference/functions/wp_next_scheduled/
	 *
	 * @see wp_schedule_event
	 * @link https://developer.wordpress.org/reference/functions/wp_schedule_event/
	 */
	public function activate() {
		$this->sync();
		add_option( self::CONFIG['option_name'], 0, '', false );
		if ( ! wp_next_scheduled( self::CONFIG['cron_event'] ) ) {
			wp_schedule_event( time(), 'twicedaily', self::CONFIG['cron_event'] );
		}
	}

	/**
	 * Deactivation hook. Removes the options of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @see delete_option
	 * @link https://developer.wordpress.org/reference/functions/delete_option/
	 *
	 * @see wp_next_scheduled
	 * @link https://developer.wordpress.org/reference/functions/wp_next_scheduled/
	 *
	 * @see wp_unschedule_event
	 * @link https://developer.wordpress.org/reference/functions/wp_unschedule_event/
	 *
	 * @see wp_clear_scheduled_hook
	 * @link https://developer.wordpress.org/reference/functions/wp_clear_scheduled_hook/
	 */
	public function deactivate() {
		delete_option( self::CONFIG['option_name'] );
		$timestamp = wp_next_scheduled( self::CONFIG['cron_event'] );
		wp_unschedule_event( $timestamp, self::CONFIG['cron_event'] );
		wp_clear_scheduled_hook( self::CONFIG['cron_event'] );
	}

	/**
	 * Get views from videos and processes them.
	 *
	 * @since 1.0.0
	 *
	 * @see wp_remote_get
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_get/
	 *
	 * @see absint
	 * @link https://developer.wordpress.org/reference/functions/absint/
	 *
	 * @see update_option
	 * @link https://developer.wordpress.org/reference/functions/update_option/
	 */
	public function sync() {
		$video_ids = [
			'epCPaHwhW5g', // Împotriva fabricii de doctorate | Emilia Șercan
			'GyIrq4jJ-Ak', // Copiii dispăruți și lumea pe care nu o vedem | Camelia Cavadia
			'8yA4Q_MPbZ8', // A educa înseamnă a călăuzi | Carmen Ion
			'5MbSwpcPMuQ', // No one will ask you about Harry Potter | Elizabeth Sagan
			'9u0-ZoSUyKY', // Îndrăznește să mănânci cum îți place | Mihaela Bilic
			'zmxiGreSRCM', // Performance | Patricia Labou
			'zi8OtaiEA5Q', // Always | Adela Mureșan
		];
		$videos    = implode( ',', $video_ids );
		$api_key   = 'AIzaSyCLoQpzA6SQ9gUcOda_UQoYM8c17f2glgo';

		// $url = plugin_dir_url( __FILE__ ) . 'data.json';
		$url  = 'https://www.googleapis.com/youtube/v3/videos?part=statistics&id=' . urlencode( $videos ) . '&key=' . $api_key;
		$args = [
			'compress' => true,
			'headers'  => [
				'Accept' => 'application/json',
			],
		];

		$request = wp_remote_get( $url, $args );
		$data    = json_decode( $request['body'] );
		$views   = 33100 + 4800; // De la aftermovie (Highlights) și de la clipul cu Vlad Voiculescu
		foreach ( $data->items as $item ) {
			$views += absint( $item->statistics->viewCount );
		}
		update_option( self::CONFIG['option_name'], $views, false );
	}

	/**
	 * Shortcode callback. Gets the views from database.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @see get_option
	 * @link https://developer.wordpress.org/reference/functions/get_option/
	 */
	public function get_views() {
		$views = get_option( self::CONFIG['option_name'] );

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