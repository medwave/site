<?php
/**
 * Renders the [google_reviews] shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Grf_Shortcode {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'google_reviews', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	public function register_assets() {
		wp_register_style( 'grf-style', GRF_PLUGIN_URL . 'assets/css/style.css', array(), GRF_VERSION );
		wp_register_script( 'grf-filter', GRF_PLUGIN_URL . 'assets/js/filter.js', array(), GRF_VERSION, true );
	}

	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'          => 0,
				'default_filter' => 'all',
			),
			$atts,
			'google_reviews'
		);

		$default_filter = in_array( $atts['default_filter'], array( 'all', 'good', 'bad' ), true ) ? $atts['default_filter'] : 'all';

		wp_enqueue_style( 'grf-style' );
		wp_enqueue_script( 'grf-filter' );

		$data = Grf_Api::get_reviews();

		if ( empty( $data['success'] ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return '<p class="grf-error">' . esc_html( $data['error'] ) . '</p>';
			}
			return '';
		}

		$settings  = wp_parse_args( get_option( 'grf_settings', array() ), array( 'good_threshold' => 4 ) );
		$threshold = (int) $settings['good_threshold'];

		$reviews = $data['reviews'];
		$limit   = (int) $atts['limit'];
		if ( $limit > 0 ) {
			$reviews = array_slice( $reviews, 0, $limit );
		}

		$good_count = 0;
		$bad_count  = 0;
		foreach ( $reviews as $review ) {
			if ( $review['rating'] >= $threshold ) {
				++$good_count;
			} else {
				++$bad_count;
			}
		}

		ob_start();
		?>
		<div class="grf-wrapper" data-default-filter="<?php echo esc_attr( $default_filter ); ?>">
			<?php if ( ! empty( $data['rating'] ) ) : ?>
				<div class="grf-summary">
					<span class="grf-summary-rating"><?php echo esc_html( number_format_i18n( $data['rating'], 1 ) ); ?></span>
					<?php echo $this->stars_markup( round( $data['rating'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="grf-summary-total">
						<?php
						printf(
							/* translators: %d: number of Google reviews. */
							esc_html( _n( 'based on %d Google review', 'based on %d Google reviews', (int) $data['total'], 'google-reviews-filter' ) ),
							(int) $data['total']
						);
						?>
					</span>
				</div>
			<?php endif; ?>

			<div class="grf-filters" role="group" aria-label="<?php esc_attr_e( 'Filter reviews', 'google-reviews-filter' ); ?>">
				<button type="button" class="grf-filter-btn" data-filter="all"><?php esc_html_e( 'All', 'google-reviews-filter' ); ?> (<?php echo (int) count( $reviews ); ?>)</button>
				<button type="button" class="grf-filter-btn" data-filter="good"><?php esc_html_e( 'Good', 'google-reviews-filter' ); ?> (<?php echo (int) $good_count; ?>)</button>
				<button type="button" class="grf-filter-btn" data-filter="bad"><?php esc_html_e( 'Bad', 'google-reviews-filter' ); ?> (<?php echo (int) $bad_count; ?>)</button>
			</div>

			<div class="grf-reviews-list">
				<?php if ( empty( $reviews ) ) : ?>
					<p class="grf-empty"><?php esc_html_e( 'No reviews yet.', 'google-reviews-filter' ); ?></p>
				<?php endif; ?>
				<?php foreach ( $reviews as $review ) : ?>
					<?php $sentiment = $review['rating'] >= $threshold ? 'good' : 'bad'; ?>
					<div class="grf-review" data-sentiment="<?php echo esc_attr( $sentiment ); ?>" data-rating="<?php echo (int) $review['rating']; ?>">
						<div class="grf-review-header">
							<?php if ( ! empty( $review['avatar'] ) ) : ?>
								<img class="grf-avatar" src="<?php echo esc_url( $review['avatar'] ); ?>" alt="" loading="lazy" width="40" height="40" />
							<?php endif; ?>
							<div class="grf-review-meta">
								<span class="grf-author"><?php echo esc_html( $review['author'] ); ?></span>
								<div class="grf-review-stars">
									<?php echo $this->stars_markup( $review['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span class="grf-badge grf-badge-<?php echo esc_attr( $sentiment ); ?>">
										<?php echo 'good' === $sentiment ? esc_html__( 'Good', 'google-reviews-filter' ) : esc_html__( 'Bad', 'google-reviews-filter' ); ?>
									</span>
								</div>
							</div>
							<?php if ( ! empty( $review['relative'] ) ) : ?>
								<span class="grf-time"><?php echo esc_html( $review['relative'] ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $review['text'] ) ) : ?>
							<p class="grf-review-text"><?php echo esc_html( $review['text'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build a simple ★★★★☆-style star string.
	 *
	 * @param int $rating 0-5.
	 * @return string
	 */
	private function stars_markup( $rating ) {
		$rating = max( 0, min( 5, (int) $rating ) );
		$html   = '<span class="grf-stars" aria-label="' . esc_attr(
			sprintf(
				/* translators: %d: rating out of 5. */
				__( '%d out of 5 stars', 'google-reviews-filter' ),
				$rating
			)
		) . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$html .= $i <= $rating ? '★' : '☆';
		}
		$html .= '</span>';
		return $html;
	}
}
