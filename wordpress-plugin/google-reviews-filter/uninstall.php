<?php
/**
 * Fired when the plugin is deleted via the WordPress admin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'grf_settings' );
delete_transient( 'grf_reviews_cache' );
