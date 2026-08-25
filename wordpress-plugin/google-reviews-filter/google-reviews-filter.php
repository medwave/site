<?php
/**
 * Plugin Name:       Google Reviews Filter
 * Plugin URI:        https://medwave.io
 * Description:       Showcase your Google Business reviews and let visitors filter them by Good or Bad. Reviews are added manually — no Google API key or billing account required. Use the [google_reviews] shortcode anywhere.
 * Version:           2.0.0
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

define( 'GRF_VERSION', '2.0.0' );
define( 'GRF_PLUGIN_FILE', __FILE__ );
define( 'GRF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GRF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GRF_PLUGIN_DIR . 'includes/class-grf-reviews.php';
require_once GRF_PLUGIN_DIR . 'includes/class-grf-settings.php';
require_once GRF_PLUGIN_DIR . 'includes/class-grf-shortcode.php';

/**
 * Boot the plugin.
 */
function grf_init() {
	Grf_Reviews::instance();
	Grf_Settings::instance();
	Grf_Shortcode::instance();
}
add_action( 'plugins_loaded', 'grf_init' );

/**
 * Default options and post type registration on activation.
 */
function grf_activate() {
	$defaults = array(
		'good_threshold' => 4,
	);
	if ( false === get_option( 'grf_settings' ) ) {
		add_option( 'grf_settings', $defaults );
	}

	Grf_Reviews::instance();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'grf_activate' );

/**
 * Flush rewrite rules on deactivation.
 */
function grf_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'grf_deactivate' );
