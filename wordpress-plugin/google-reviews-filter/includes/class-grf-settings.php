<?php
/**
 * Admin settings screen under Settings → Google Reviews.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Grf_Settings {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_grf_clear_cache', array( $this, 'handle_clear_cache' ) );
	}

	public function add_settings_page() {
		add_options_page(
			__( 'Google Reviews Filter', 'google-reviews-filter' ),
			__( 'Google Reviews', 'google-reviews-filter' ),
			'manage_options',
			'google-reviews-filter',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( 'grf_settings_group', 'grf_settings', array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'grf_main_section',
			__( 'Google Places API Connection', 'google-reviews-filter' ),
			function () {
				echo '<p>' . esc_html__( 'Create an API key in Google Cloud Console with the "Places API" enabled, then find your Place ID using Google\'s Place ID Finder tool.', 'google-reviews-filter' ) . '</p>';
			},
			'google-reviews-filter'
		);

		add_settings_field(
			'api_key',
			__( 'Google Places API Key', 'google-reviews-filter' ),
			array( $this, 'field_api_key' ),
			'google-reviews-filter',
			'grf_main_section'
		);

		add_settings_field(
			'place_id',
			__( 'Google Place ID', 'google-reviews-filter' ),
			array( $this, 'field_place_id' ),
			'google-reviews-filter',
			'grf_main_section'
		);

		add_settings_field(
			'cache_hours',
			__( 'Cache Duration (hours)', 'google-reviews-filter' ),
			array( $this, 'field_cache_hours' ),
			'google-reviews-filter',
			'grf_main_section'
		);

		add_settings_field(
			'good_threshold',
			__( '"Good" Rating Threshold', 'google-reviews-filter' ),
			array( $this, 'field_good_threshold' ),
			'google-reviews-filter',
			'grf_main_section'
		);
	}

	public function sanitize_settings( $input ) {
		$output = get_option( 'grf_settings', array() );

		$output['api_key']  = isset( $input['api_key'] ) ? sanitize_text_field( trim( $input['api_key'] ) ) : '';
		$output['place_id'] = isset( $input['place_id'] ) ? sanitize_text_field( trim( $input['place_id'] ) ) : '';

		$cache_hours           = isset( $input['cache_hours'] ) ? (float) $input['cache_hours'] : 12;
		$output['cache_hours'] = $cache_hours > 0 ? $cache_hours : 12;

		$threshold                = isset( $input['good_threshold'] ) ? (int) $input['good_threshold'] : 4;
		$output['good_threshold'] = min( 5, max( 1, $threshold ) );

		delete_transient( Grf_Api::TRANSIENT_KEY );

		return $output;
	}

	private function get_settings() {
		return wp_parse_args(
			get_option( 'grf_settings', array() ),
			array(
				'api_key'        => '',
				'place_id'       => '',
				'cache_hours'    => 12,
				'good_threshold' => 4,
			)
		);
	}

	public function field_api_key() {
		$settings = $this->get_settings();
		printf(
			'<input type="text" class="regular-text" name="grf_settings[api_key]" value="%s" autocomplete="off" />',
			esc_attr( $settings['api_key'] )
		);
	}

	public function field_place_id() {
		$settings = $this->get_settings();
		printf(
			'<input type="text" class="regular-text" name="grf_settings[place_id]" value="%s" placeholder="ChIJ..." />',
			esc_attr( $settings['place_id'] )
		);
		echo '<p class="description"><a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Find your Place ID', 'google-reviews-filter' ) . '</a></p>';
	}

	public function field_cache_hours() {
		$settings = $this->get_settings();
		printf(
			'<input type="number" min="1" step="1" name="grf_settings[cache_hours]" value="%s" /> %s',
			esc_attr( $settings['cache_hours'] ),
			esc_html__( 'hours', 'google-reviews-filter' )
		);
		echo '<p class="description">' . esc_html__( 'How long to cache reviews before checking Google again. Reviews are also refreshed automatically once this expires.', 'google-reviews-filter' ) . '</p>';
	}

	public function field_good_threshold() {
		$settings = $this->get_settings();
		echo '<select name="grf_settings[good_threshold]">';
		for ( $i = 5; $i >= 1; $i-- ) {
			printf(
				'<option value="%1$d" %2$s>%1$d+ %3$s</option>',
				(int) $i,
				selected( (int) $settings['good_threshold'], $i, false ),
				esc_html__( 'stars', 'google-reviews-filter' )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Reviews at or above this rating are labeled "Good". Everything below is labeled "Bad".', 'google-reviews-filter' ) . '</p>';
	}

	public function handle_clear_cache() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'grf_clear_cache' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'google-reviews-filter' ) );
		}
		delete_transient( Grf_Api::TRANSIENT_KEY );
		wp_safe_redirect( add_query_arg( array( 'page' => 'google-reviews-filter', 'grf_cache_cleared' => '1' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Google Reviews Filter', 'google-reviews-filter' ); ?></h1>

			<?php if ( isset( $_GET['grf_cache_cleared'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Review cache cleared. Latest reviews will be fetched on next page load.', 'google-reviews-filter' ); ?></p></div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'grf_settings_group' );
				do_settings_sections( 'google-reviews-filter' );
				submit_button( __( 'Save Settings', 'google-reviews-filter' ) );
				?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Usage', 'google-reviews-filter' ); ?></h2>
			<p>
				<?php esc_html_e( 'Add the shortcode below to any post, page, or widget to display your reviews with Good/Bad filter buttons:', 'google-reviews-filter' ); ?>
			</p>
			<p><code>[google_reviews]</code></p>
			<p>
				<?php esc_html_e( 'Optional attributes:', 'google-reviews-filter' ); ?>
			</p>
			<ul style="list-style: disc; margin-left: 20px;">
				<li><code>limit="10"</code> — <?php esc_html_e( 'maximum number of reviews to show (Google returns up to 5 per API call).', 'google-reviews-filter' ); ?></li>
				<li><code>default_filter="good"</code> — <?php esc_html_e( 'one of all, good, bad. Sets which filter is active on page load.', 'google-reviews-filter' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'Cache', 'google-reviews-filter' ); ?></h2>
			<p>
				<?php esc_html_e( 'Reviews are cached to avoid unnecessary API calls and stay within Google\'s usage limits.', 'google-reviews-filter' ); ?>
			</p>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="grf_clear_cache" />
				<?php wp_nonce_field( 'grf_clear_cache' ); ?>
				<?php submit_button( __( 'Clear Cached Reviews Now', 'google-reviews-filter' ), 'secondary', 'submit', false ); ?>
			</form>

			<h2><?php esc_html_e( 'Note on Google\'s API', 'google-reviews-filter' ); ?></h2>
			<p>
				<?php esc_html_e( 'The Google Places API only returns up to 5 reviews per business, chosen by Google (not necessarily the newest or the highest/lowest rated). This is a limitation of Google\'s API, not this plugin.', 'google-reviews-filter' ); ?>
			</p>
		</div>
		<?php
	}
}
