<?php
/**
 * Fired when the plugin is deleted via the WordPress admin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'grf_settings' );

// Intentionally leave grf_review posts in place — they're the site owner's
// content (transcribed reviews), not plugin configuration.
