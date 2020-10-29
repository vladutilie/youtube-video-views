<?php

use YTVV\Includes\Main;

/**
 * Plugin Name: YouTube Video Views
 * Plugin URI:  N/A
 * Description: Shows YouTube video views on your website using a shortcode.
 * Version:     1.0.0
 * Author:      Vlăduț Ilie
 * Author URI:  https://vladilie.ro/
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: youtube-video-views
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

require_once 'includes/class-main.php';

$instance = new Main();
register_activation_hook( __FILE__, [ $instance, 'activate' ] );
register_deactivation_hook( __FILE__, [ $instance, 'deactivate' ] );
$instance->run();
