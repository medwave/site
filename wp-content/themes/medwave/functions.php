<?php
/**
 * Medwave theme setup.
 *
 * @package Medwave
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the small stylesheet on the front end and in the editor.
 */
function medwave_setup() {
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'medwave_setup' );

function medwave_enqueue_styles() {
	wp_enqueue_style(
		'medwave-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'medwave_enqueue_styles' );

/**
 * Group the theme's sections under one "Medwave" heading in the pattern inserter.
 */
function medwave_register_pattern_category() {
	register_block_pattern_category(
		'medwave',
		array( 'label' => __( 'Medwave', 'medwave' ) )
	);
}
add_action( 'init', 'medwave_register_pattern_category' );

/**
 * "Card" style for Group blocks: white box, light border, rounded corners.
 */
function medwave_register_block_styles() {
	register_block_style(
		'core/group',
		array(
			'name'  => 'medwave-card',
			'label' => __( 'Card', 'medwave' ),
		)
	);
}
add_action( 'init', 'medwave_register_block_styles' );
