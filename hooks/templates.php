<?php
add_filter( 'stm_lms_template_file', 'stm_lms_template_file_pro', 10, 2 );

function stm_lms_template_file_pro( $path, $template ) {
	return file_exists( STM_LMS_PRO_PATH . $template ) ? STM_LMS_PRO_PATH : $path;
}

// Register endpoint for WooCommerce orders
add_action( 'init', 'register_instructor_order_details_endpoint' );

function register_instructor_order_details_endpoint() {
	$endpoints = array(
		'instructor-order-details',
		'instructor-sales-details',
	);

	foreach ( $endpoints as $endpoint ) {
		add_rewrite_endpoint( $endpoint, EP_ROOT | EP_PAGES );
	}
}

// Redirect to the order details page
add_action( 'template_redirect', 'instructor_order_details_template_redirect' );

function instructor_order_details_template_redirect() {
	global $wp_query;

	$endpoints = array(
		'instructor-order-details' => 'account/private/parts/orders-details/orders-details',
		'instructor-sales-details' => 'account/private/parts/sales-details/sales-details',
	);

	foreach ( $endpoints as $endpoint => $template ) {
		if ( isset( $wp_query->query_vars[ $endpoint ] ) ) {
			$order_id = intval( $wp_query->query_vars[ $endpoint ] );

			if ( ! empty( $order_id ) ) {
				STM_LMS_Templates::show_lms_template(
					$template,
					array(
						'order_id' => $order_id,
					)
				);
				exit;
			} else {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( '404' );
				exit;
			}
		}
	}
}
