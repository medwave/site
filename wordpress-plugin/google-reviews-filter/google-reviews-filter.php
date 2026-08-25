<?php
/**
 * Plugin Name:       Google Reviews Filter
 * Plugin URI:        https://medwave.io
 * Description:       Pulls in your Google Business reviews and lets visitors filter them by Good or Bad. Use the [google_reviews] shortcode anywhere.
 * Version:           1.0.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Medwave
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       google-reviews-filter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'GRF_VERSION', '1.0.1' );
define( 'GRF_PLUGIN_FILE', __FILE__ );
define( 'GRF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GRF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GRF_PLUGIN_DIR . 'includes/class-grf-api.php';
require_once GRF_PLUGIN_DIR . 'includes/class-grf-settings.php';
require_once GRF_PLUGIN_DIR . 'includes/class-grf-shortcode.php';

/**
 * Boot the plugin.
 */
function grf_init() {
	Grf_Settings::instance();
	Grf_Shortcode::instance();
}
add_action( 'plugins_loaded', 'grf_init' );

/**
 * Default options on activation.
 */
function grf_activate() {
	$defaults = array(
		'api_key'        => '',
		'place_id'       => '',
		'cache_hours'    => 12,
		'good_threshold' => 4,
	);
	if ( false === get_option( 'grf_settings' ) ) {
		add_option( 'grf_settings', $defaults );
	}
}
register_activation_hook( __FILE__, 'grf_activate' );

/**
 * Clear cached reviews on deactivation.
 */
function grf_deactivate() {
	delete_transient( 'grf_reviews_cache' );
}
register_deactivation_hook( __FILE__, 'grf_deactivate' );
