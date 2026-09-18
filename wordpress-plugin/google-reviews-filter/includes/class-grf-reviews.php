<?php
/**
 * Registers the "Review" custom post type and reads reviews for the shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Grf_Reviews {

	const POST_TYPE = 'grf_review';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_rating_column' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_rating_column' ), 10, 2 );
	}

	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'               => __( 'Reviews', 'google-reviews-filter' ),
					'singular_name'      => __( 'Review', 'google-reviews-filter' ),
					'add_new_item'       => __( 'Add New Review', 'google-reviews-filter' ),
					'edit_item'          => __( 'Edit Review', 'google-reviews-filter' ),
					'all_items'          => __( 'Reviews', 'google-reviews-filter' ),
					'search_items'       => __( 'Search Reviews', 'google-reviews-filter' ),
					'not_found'          => __( 'No reviews yet. Click "Add New Review" to add your first one.', 'google-reviews-filter' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-star-filled',
				'menu_position' => 25,
				'supports'     => array( 'title', 'thumbnail' ),
				'capability_type' => 'post',
			)
		);
	}

	public function add_meta_box() {
		add_meta_box(
			'grf_review_details',
			__( 'Review Details', 'google-reviews-filter' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'grf_save_review', 'grf_review_nonce' );

		$rating = (int) get_post_meta( $post->ID, '_grf_rating', true );
		$rating = $rating ? $rating : 5;
		$text   = get_post_meta( $post->ID, '_grf_text', true );
		?>
		<p>
			<label for="grf_rating"><strong><?php esc_html_e( 'Star Rating', 'google-reviews-filter' ); ?></strong></label><br />
			<select name="grf_rating" id="grf_rating">
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<option value="<?php echo (int) $i; ?>" <?php selected( $rating, $i ); ?>>
						<?php
						/* translators: %d: star rating 1-5. */
						printf( esc_html__( '%d stars', 'google-reviews-filter' ), (int) $i );
						?>
					</option>
				<?php endfor; ?>
			</select>
		</p>
		<p>
			<label for="grf_text"><strong><?php esc_html_e( 'Review Text', 'google-reviews-filter' ); ?></strong></label><br />
			<textarea name="grf_text" id="grf_text" rows="5" style="width: 100%; max-width: 600px;"><?php echo esc_textarea( $text ); ?></textarea>
		</p>
		<p class="description">
			<?php esc_html_e( 'Set the "Reviewer Name" as the post title above, the review date as the Published date (in the Publish box), and an optional photo as the Featured Image.', 'google-reviews-filter' ); ?>
		</p>
		<?php
	}

	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['grf_review_nonce'] ) || ! wp_verify_nonce( $_POST['grf_review_nonce'], 'grf_save_review' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['grf_rating'] ) ) {
			$rating = min( 5, max( 1, (int) $_POST['grf_rating'] ) );
			update_post_meta( $post_id, '_grf_rating', $rating );
		}

		if ( isset( $_POST['grf_text'] ) ) {
			update_post_meta( $post_id, '_grf_text', sanitize_textarea_field( wp_unslash( $_POST['grf_text'] ) ) );
		}
	}

	public function add_rating_column( $columns ) {
		$columns['grf_rating'] = __( 'Rating', 'google-reviews-filter' );
		return $columns;
	}

	public function render_rating_column( $column, $post_id ) {
		if ( 'grf_rating' === $column ) {
			$rating = (int) get_post_meta( $post_id, '_grf_rating', true );
			echo str_repeat( '★', max( 0, min( 5, $rating ) ) ) . str_repeat( '☆', 5 - max( 0, min( 5, $rating ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Fetch all published reviews for display.
	 *
	 * @return array {
	 *     @type bool   $success
	 *     @type string $error
	 *     @type array  $reviews
	 *     @type float  $rating
	 *     @type int    $total
	 * }
	 */
	public static function get_reviews() {
		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$reviews = array();
		$sum     = 0;

		foreach ( $posts as $post ) {
			$rating = (int) get_post_meta( $post->ID, '_grf_rating', true );
			$sum   += $rating;

			$reviews[] = array(
				'author'   => get_the_title( $post ),
				'avatar'   => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
				'rating'   => $rating,
				'text'     => get_post_meta( $post->ID, '_grf_text', true ),
				'time'     => get_the_date( 'U', $post ),
				'relative' => sprintf(
					/* translators: %s: human-readable time difference, e.g. "3 days". */
					__( '%s ago', 'google-reviews-filter' ),
					human_time_diff( get_the_date( 'U', $post ), current_time( 'timestamp' ) )
				),
			);
		}

		$total = count( $reviews );

		return array(
			'success' => true,
			'error'   => '',
			'reviews' => $reviews,
			'rating'  => $total > 0 ? $sum / $total : 0,
			'total'   => $total,
		);
	}
}
