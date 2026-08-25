<?php
/**
 * Handles fetching and caching reviews from the Google Places API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Grf_Api {

	const TRANSIENT_KEY = 'grf_reviews_cache';

	/**
	 * Get reviews, either from cache or freshly fetched from Google.
	 *
	 * @param bool $force_refresh Bypass the cache.
	 * @return array {
	 *     @type bool   $success
	 *     @type string $error
	 *     @type array  $reviews
	 *     @type float  $rating
	 *     @type int    $total
	 * }
	 */
	public static function get_reviews( $force_refresh = false ) {
		if ( ! $force_refresh ) {
			$cached = get_transient( self::TRANSIENT_KEY );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$result = self::fetch_from_google();

		$settings    = get_option( 'grf_settings', array() );
		$cache_hours = isset( $settings['cache_hours'] ) ? (float) $settings['cache_hours'] : 12;
		$cache_hours = $cache_hours > 0 ? $cache_hours : 12;

		// Cache successful results for the configured duration; cache failures
		// briefly so a bad request doesn't hammer the Google API on every pageview.
		$ttl = $result['success'] ? $cache_hours * HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS;
		set_transient( self::TRANSIENT_KEY, $result, $ttl );

		return $result;
	}

	/**
	 * Call the Google Places API "Place Details" endpoint.
	 *
	 * @return array
	 */
	private static function fetch_from_google() {
		$settings = get_option( 'grf_settings', array() );
		$api_key  = isset( $settings['api_key'] ) ? trim( $settings['api_key'] ) : '';
		$place_id = isset( $settings['place_id'] ) ? trim( $settings['place_id'] ) : '';

		if ( empty( $api_key ) || empty( $place_id ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Google Reviews Filter is not configured yet. Add your API key and Place ID under Settings → Google Reviews.', 'google-reviews-filter' ),
				'reviews' => array(),
				'rating'  => 0,
				'total'   => 0,
			);
		}

		$url = add_query_arg(
			array(
				'place_id' => rawurlencode( $place_id ),
				'fields'   => 'name,rating,user_ratings_total,reviews',
				'reviews_sort' => 'newest',
				'key'      => rawurlencode( $api_key ),
			),
			'https://maps.googleapis.com/maps/api/place/details/json'
		);

		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error'   => $response->get_error_message(),
				'reviews' => array(),
				'rating'  => 0,
				'total'   => 0,
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== (int) $code || empty( $body ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Could not reach the Google Places API.', 'google-reviews-filter' ),
				'reviews' => array(),
				'rating'  => 0,
				'total'   => 0,
			);
		}

		if ( 'OK' !== $body['status'] ) {
			$message = isset( $body['error_message'] ) ? $body['error_message'] : $body['status'];
			return array(
				'success' => false,
				/* translators: %s: Google API status/error message. */
				'error'   => sprintf( __( 'Google API error: %s', 'google-reviews-filter' ), $message ),
				'reviews' => array(),
				'rating'  => 0,
				'total'   => 0,
			);
		}

		$result  = isset( $body['result'] ) ? $body['result'] : array();
		$reviews = isset( $result['reviews'] ) ? $result['reviews'] : array();

		$normalized = array();
		foreach ( $reviews as $review ) {
			$normalized[] = array(
				'author'      => isset( $review['author_name'] ) ? sanitize_text_field( $review['author_name'] ) : __( 'Anonymous', 'google-reviews-filter' ),
				'avatar'      => isset( $review['profile_photo_url'] ) ? esc_url_raw( $review['profile_photo_url'] ) : '',
				'rating'      => isset( $review['rating'] ) ? (int) $review['rating'] : 0,
				'text'        => isset( $review['text'] ) ? sanitize_textarea_field( $review['text'] ) : '',
				'time'        => isset( $review['time'] ) ? (int) $review['time'] : 0,
				'relative'    => isset( $review['relative_time_description'] ) ? sanitize_text_field( $review['relative_time_description'] ) : '',
				'author_url'  => isset( $review['author_url'] ) ? esc_url_raw( $review['author_url'] ) : '',
			);
		}

		// Newest first.
		usort(
			$normalized,
			function ( $a, $b ) {
				return $b['time'] - $a['time'];
			}
		);

		return array(
			'success' => true,
			'error'   => '',
			'reviews' => $normalized,
			'rating'  => isset( $result['rating'] ) ? (float) $result['rating'] : 0,
			'total'   => isset( $result['user_ratings_total'] ) ? (int) $result['user_ratings_total'] : count( $normalized ),
		);
	}
}
