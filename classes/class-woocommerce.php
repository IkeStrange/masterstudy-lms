<?php

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository;

add_action( 'init', 'stm_lms_add_masterstudy_product_type' );

function stm_lms_add_masterstudy_product_type() {
	class WC_Product_Stm_Lms_Product extends WC_Product {
		public function __construct( $product ) {
			$this->product_type = 'stm_lms_product';
			parent::__construct( $product );
		}
	}
}

function stm_lms_init_woocommerce() {
	new STM_LMS_Woocommerce();
}

add_action( 'init', 'stm_lms_init_woocommerce' );

// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
class STM_LMS_Woocommerce {

	public static $product_meta_name = 'stm_lms_product_id';

	public function __construct() {
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array( $this, 'stm_before_create_order_api' ), 200, 1 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'stm_before_create_order' ), 200, 1 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'stm_lms_woocommerce_order_created' ) );
		add_action( 'woocommerce_order_status_pending', array( $this, 'stm_lms_woocommerce_order_cancelled' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'stm_lms_woocommerce_order_cancelled' ) );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'stm_lms_woocommerce_order_cancelled' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'stm_lms_woocommerce_order_cancelled' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'stm_lms_woocommerce_order_cancelled' ) );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'stm_lms_woocommerce_order_cancelled' ) );

		add_filter( 'product_type_selector', array( $this, 'add_lms_product_type' ) );
		add_filter( 'woocommerce_product_class', array( $this, 'product_class' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'variable_bulk_admin_custom_js' ) );

		add_action( 'template_redirect', array( $this, 'redirect' ) );
		add_action( 'save_post', array( $this, 'save_course' ), 999999, 2 );

		add_action(
			'stm_lms_single_course_start',
			function ( $course_id ) {
				self::create_product( $course_id );
			}
		);

		add_action( 'before_delete_post', array( $this, 'delete_course' ), 99, 2 );
		add_action( 'stm_lms_before_button_mixed', array( $this, 'out_of_stock' ), 10, 1 );
		add_filter( 'stm_lms_before_button_stop', array( $this, 'is_out_of_stock' ), 100, 2 );

		new STM_LMS_Woocommerce_Courses_Admin(
			'lms_products',
			esc_html__( 'LMS Products', 'masterstudy-lms-learning-management-system-pro' ),
			'stm_lms_product_id',
			array(
				'meta_query' => array(
					'key'     => 'lms_products',
					'compare' => 'NOT EXISTS',
				),
			)
		);

		if ( ! empty( $_GET['delete_pr_lms'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action(
				'admin_init',
				function () {
					wp_delete_post( intval( $_GET['delete_pr_lms'] ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			);
		}

		add_action(
			'woocommerce_cart_is_empty',
			function () {
				STM_LMS_Templates::show_lms_template( 'global/all_courses_link' );
			}
		);

		add_action( 'template_redirect', array( $this, 'masterstudy_thankyou_page' ), 100 );

	}

	public function stm_before_create_order_api( $order ) {
		$order_id = $order->get_id();
		$this->stm_before_create_order( $order_id );
	}

	public function stm_before_create_order( $order_id ) {
		$cart = WC()->cart->get_cart();
		$ids  = array();

		foreach ( $cart as $cart_item ) {
			$course_id = get_post_meta( $cart_item['product_id'], self::$product_meta_name, true );

			if ( empty( $course_id ) && class_exists( 'STM_LMS_Enterprise_Courses' ) ) {
				$course_id = get_post_meta( $cart_item['product_id'], STM_LMS_Enterprise_Courses::$enteprise_meta_key, true );
			}

			if ( empty( $course_id ) ) {
				continue;
			}

			$ids[] = apply_filters(
				'stm_lms_before_create_order',
				array(
					'item_id'  => $course_id,
					'price'    => $cart_item['line_total'],
					'quantity' => $cart_item['quantity'],
				),
				$cart_item
			);
		}

		update_post_meta( $order_id, 'stm_lms_courses', $ids );
	}

	public function stm_lms_woocommerce_order_created( $order_id ) {
		$order   = new WC_Order( $order_id );
		$user_id = $order->get_user_id();
		$courses = get_post_meta( $order_id, 'stm_lms_courses', true );

		foreach ( $courses as $course ) {
			if ( get_post_type( $course['item_id'] ) === 'stm-courses' ) {
				if ( empty( $course['enterprise_id'] ) ) {
					STM_LMS_Course::add_user_course( $course['item_id'], $user_id, 0, 0 );
					STM_LMS_Course::add_student( $course['item_id'] );
				}
			}

			do_action( 'stm_lms_woocommerce_order_approved', $course, $user_id );
		}
	}

	public function stm_lms_woocommerce_order_cancelled( $order_id ) {
		$order   = new WC_Order( $order_id );
		$user_id = $order->get_user_id();
		$courses = get_post_meta( $order_id, 'stm_lms_courses', true );

		foreach ( $courses as $course ) {
			$enterpise_key = ! empty( $course['enterprise_id'] ) && class_exists( 'STM_LMS_Enterprise_Courses' ) ? STM_LMS_Enterprise_Courses::$enteprise_meta_key : '';
			if ( ! self::has_course_been_purchased( $user_id, $course['item_id'], $enterpise_key ) ) {
				stm_lms_get_delete_user_course( $user_id, $course['item_id'] );
				STM_LMS_Course::remove_student( $course['item_id'] );
			}
			do_action( 'stm_lms_woocommerce_order_cancelled', $course, $user_id );
		}
	}

	public function add_lms_product_type( $types ) {
		$types['stm_lms_product'] = __( 'MasterStudy LMS product', 'masterstudy-lms-learning-management-system-pro' );

		return $types;
	}

	public function product_class( $classname, $product_type ) {
		if ( 'stm_lms_product' === $product_type ) {
			$classname = 'WC_Product_Stm_Lms_Product';
		}

		return $classname;
	}

	public function variable_bulk_admin_custom_js() {
		if ( 'product' !== get_post_type() ) {
			return;
		}

		?>
		<script type='text/javascript'>
			jQuery(document).ready(function () {
				var $ = jQuery;

				var $product_type = $('#product-type');
				var $metaboxes = $('#product_stm_woo_product_expert,#product_stm_woo_product_status,#product_stm_woo_product_button_link,#product_page_options');

				showLMSElements();
				$('.show_if_simple').addClass('show_if_stm_lms_product');

				showLMS();

				$product_type.on('change', function () {
					showLMS();
				});

				function showLMS() {
					if ($product_type.val() === 'stm_lms_product') {
						$('.general_options').click();
						addNotice();
						showLMSElements();
						hideLMSBoxes();

						$('.linked_product_options, .attribute_options, #linked_product_data').hide();
						$('#general_product_data').show();
					} else {
						hideNotice();
						showLMSBoxes();

						$('.linked_product_options, .attribute_options').show();
					}
				}

				function hideLMSBoxes() {
					$metaboxes.hide();
				}

				function showLMSBoxes() {
					$metaboxes.show();
				}

				function showLMSElements() {
					$('.show_if_stm_lms_product').show();
					$('#general_product_data .pricing').show();
					$('.product_data_tabs .general_tab').show();
				}

				function addNotice() {
					<?php
					$product_id = get_the_ID();
					$course_id  = get_post_meta( $product_id, 'stm_lms_product_id', true );
					if ( empty( $course_id ) ) {
						$course_id = get_post_meta( $product_id, 'stm_lms_enterprise_id', true );
					}

					$notice = sprintf(
						/* translators: %%1$s Course Link, %2$s Course Title */
						__(
							'This is LMS Product for MasterStudy LMS Course. <span>Do NOT change</span> Title, Price, Sale price and Sale price dates. If you need to change these data, please edit the related course - <a href="%1$s" target="_blank">%2$s </a>',
							'masterstudy-lms-learning-management-system-pro'
						),
						get_edit_post_link( $course_id ),
						get_the_title( $course_id )
					);
					?>
					$('#woocommerce-product-data .hndle').append('<label for="lms_product"><?php echo wp_kses_post( $notice ); ?></label>');
				}

				function hideNotice() {
					$('body').find('label[for="lms_product"]').remove();
				}

			});
		</script>
		<?php
	}

	public static function has_course_been_purchased( $user_id, $course_id, $enterprise_key = '' ) {
		global $wpdb;
		$product_key = ! empty( $enterprise_key ) ? $enterprise_key : self::$product_meta_name;
		$product_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT postmeta.meta_value AS stm_lms_product_id
				FROM {$wpdb->prefix}wc_orders AS orders
				INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS lookup ON orders.id = lookup.order_id
				LEFT JOIN {$wpdb->prefix}postmeta AS postmeta ON lookup.product_id = postmeta.post_id
				WHERE orders.status = 'wc-completed' AND orders.customer_id = %d AND postmeta.meta_key = %s
				",
				$user_id,
				$product_key
			)
		);

		return in_array( intval( $course_id ), array_map( 'intval', $product_ids ?? array() ), true );
	}

	public static function add_to_cart( $item_id ) {
		$product_id = self::create_product( $item_id );

		// Load cart functions which are loaded only on the front-end.
		include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		include_once WC_ABSPATH . 'includes/class-wc-cart.php';

		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}

		return WC()->cart->add_to_cart( $product_id );
	}

	public static function create_product( $id ) {
		$product_id = self::has_product( $id );

		$title                  = get_the_title( $id );
		$price                  = get_post_meta( $id, 'price', true );
		$sale_price             = get_post_meta( $id, 'sale_price', true );
		$sale_price_dates_start = get_post_meta( $id, 'sale_price_dates_start', true );
		$sale_price_dates_end   = get_post_meta( $id, 'sale_price_dates_end', true );
		$thumbnail_id           = get_post_thumbnail_id( $id );
		$now                    = time() * 1000;
		$bundle_price           = class_exists( '\MasterStudy\Lms\Pro\addons\CourseBundle\Repository\CourseBundleRepository' ) ? CourseBundleRepository::get_bundle_price( $id ) : null;
		$bundle_price           = ( $bundle_price <= 0 ) ? null : $bundle_price;

		$product = array(
			'post_title'  => $title,
			'post_type'   => 'product',
			'post_status' => 'publish',
		);

		if ( $product_id ) {
			$product['ID'] = $product_id;
		}

		$product_id = wp_insert_post( $product );

		wp_set_object_terms(
			$product_id,
			array( 'exclude-from-catalog', 'exclude-from-search' ),
			'product_visibility'
		);

		if ( isset( $sale_price_dates_start ) && isset( $sale_price_dates_end ) ) {
			if ( empty( $sale_price_dates_start ) || 'NaN' === $sale_price_dates_start ) {
				$price = ( ! empty( $sale_price ) ) ? $sale_price : $price;

				delete_post_meta( $product_id, '_sale_price_dates_from' );
				delete_post_meta( $product_id, '_sale_price_dates_to' );
			} else {
				$price = ( $now > $sale_price_dates_start && $now < $sale_price_dates_end ) ? $sale_price : $price;

				update_post_meta(
					$product_id,
					'_sale_price_dates_from',
					gmdate( 'Y-m-d', ( $sale_price_dates_start / 1000 ) + 24 * 60 * 60 )
				);
				update_post_meta(
					$product_id,
					'_sale_price_dates_to',
					gmdate( 'Y-m-d', ( $sale_price_dates_end / 1000 ) + 24 * 60 * 60 )
				);
			}
		}

		if ( isset( $price ) ) {
			update_post_meta( $product_id, '_regular_price', $price );
		}

		if ( isset( $sale_price ) ) {
			update_post_meta( $product_id, '_sale_price', $sale_price );
		}

		if ( isset( $price ) ) {
			update_post_meta( $product_id, '_price', $price );
		}

		if ( isset( $bundle_price ) ) {
			update_post_meta( $product_id, 'stm_lms_bundle_price', $bundle_price );
			update_post_meta( $product_id, '_regular_price', $bundle_price );
			update_post_meta( $product_id, '_price', $bundle_price );
		}

		if ( isset( $thumbnail_id ) ) {
			set_post_thumbnail( $product_id, $thumbnail_id );
		}

		wp_set_object_terms( $product_id, 'stm_lms_product', 'product_type' );

		update_post_meta( $id, self::$product_meta_name, $product_id );
		update_post_meta( $product_id, self::$product_meta_name, $id );
		update_post_meta( $product_id, '_sold_individually', 1 );
		update_post_meta( $product_id, '_virtual', 1 );
		update_post_meta( $product_id, '_downloadable', 1 );

		return $product_id;
	}

	public static function has_product( $id ) {
		$product_id = get_post_meta( $id, self::$product_meta_name, true );

		if ( empty( $product_id ) ) {
			return false;
		}

		if ( empty( get_post_status( $product_id ) ) ) {
			return false;
		}

		return $product_id;
	}

	public function redirect() {
		if ( ! is_product() ) {
			return false;
		}

		$product_id = get_queried_object_id();
		if ( get_post_type( $product_id ) !== 'product' ) {
			return false;
		}

		$types = get_the_terms( $product_id, 'product_type' );
		if ( empty( $types ) || empty( $types[0] ) ) {
			return false;
		}

		$type = $types[0];
		if ( 'stm_lms_product' !== $type->name ) {
			return false;
		}

		$course_id = get_post_meta( $product_id, self::$product_meta_name, true );
		wp_safe_redirect( get_the_permalink( $course_id ) );
	}

	public function save_course( $course_id, $post ) {
		if ( 'stm-courses' !== $post->post_type ) {
			return;
		}

		self::create_product( $course_id );
	}

	public function delete_course( $course_id, $post ) {
		$allowed_types = array(
			PostType::COURSE,
			CourseBundleRepository::POST_TYPE,
		);

		if ( ! in_array( $post->post_type, $allowed_types ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return;
		}

		/*Delete Woocommerce Product*/
		$product_id = get_post_meta( $course_id, self::$product_meta_name, true );

		if ( ! empty( $product_id ) ) {
			wp_delete_post( $product_id, true );
		}
	}


	public function is_out_of_stock( $stop, $course_id ) {
		//Check if we have binded WooCommerce product
		$product_id = self::has_product( $course_id );

		if ( empty( $product_id ) ) {
			return $stop;
		}

		$product = new WC_Product( $product_id );

		if ( ! $product->managing_stock() ) {
			return $stop;
		}

		return ! $product->is_in_stock();
	}

	public function out_of_stock( $course_id ) {
		if ( self::is_out_of_stock( false, $course_id ) && ! STM_LMS_User::has_course_access( $course_id, '', false ) ) {
			STM_LMS_Templates::show_lms_template( 'global/out_of_stock', compact( 'course_id' ) );
		}
	}

	/**
	 * Clear default WooCommerce content on the Thank You page.
	 */
	public function masterstudy_thankyou_page() {
		if ( is_wc_endpoint_url( 'order-received' ) ) {
			remove_all_actions( 'woocommerce_thankyou' );
			add_action( 'woocommerce_thankyou', array( $this, 'masterstudy_create_template_thankyou_message' ), 10 );
		}
	}

	/**
	 * Custom Thank You page template for WooCommerce orders.
	 *
	 * @param int $order_id The ID of the order.
	 */
	public function masterstudy_create_template_thankyou_message( $order_id ) {
		// Fetch the order object using the order ID
		$order = wc_get_order( $order_id );
		include STM_LMS_PRO_PATH . '/stm-lms-templates/checkout/woocommerce-thankyou.php';
	}

}
