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
		add_filter( 'plugin_action_links_' . plugin_basename( GRF_PLUGIN_FILE ), array( $this, 'add_settings_link' ) );
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

	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=google-reviews-filter' ) ) . '">' . esc_html__( 'Settings', 'google-reviews-filter' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function register_settings() {
		register_setting( 'grf_settings_group', 'grf_settings', array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'grf_main_section',
			__( 'Display Settings', 'google-reviews-filter' ),
			'__return_false',
			'google-reviews-filter'
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

		$threshold                = isset( $input['good_threshold'] ) ? (int) $input['good_threshold'] : 4;
		$output['good_threshold'] = min( 5, max( 1, $threshold ) );

		return $output;
	}

	private function get_settings() {
		return wp_parse_args(
			get_option( 'grf_settings', array() ),
			array(
				'good_threshold' => 4,
			)
		);
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

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$reviews_url = admin_url( 'edit.php?post_type=' . Grf_Reviews::POST_TYPE );
		$add_new_url = admin_url( 'post-new.php?post_type=' . Grf_Reviews::POST_TYPE );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Google Reviews Filter', 'google-reviews-filter' ); ?></h1>

			<h2><?php esc_html_e( 'Adding Reviews', 'google-reviews-filter' ); ?></h2>
			<p>
				<?php esc_html_e( 'This plugin does not call Google\'s API (which requires a paid Google Cloud billing account), so reviews are added manually — copy each one from your Google Business Profile once, and it stays on your site.', 'google-reviews-filter' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $add_new_url ); ?>"><?php esc_html_e( 'Add New Review', 'google-reviews-filter' ); ?></a>
				<a class="button" href="<?php echo esc_url( $reviews_url ); ?>"><?php esc_html_e( 'Manage Reviews', 'google-reviews-filter' ); ?></a>
			</p>
			<p>
				<?php esc_html_e( 'When adding a review: set the Title to the reviewer\'s name, fill in the Star Rating and Review Text fields, optionally set a Featured Image as their photo, and set the Published date (in the Publish box) to match the actual review date.', 'google-reviews-filter' ); ?>
			</p>

			<hr />

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
				<li><code>limit="10"</code> — <?php esc_html_e( 'maximum number of reviews to show.', 'google-reviews-filter' ); ?></li>
				<li><code>default_filter="good"</code> — <?php esc_html_e( 'one of all, good, bad. Sets which filter is active on page load. Defaults to good.', 'google-reviews-filter' ); ?></li>
			</ul>
		</div>
		<?php
	}
}
