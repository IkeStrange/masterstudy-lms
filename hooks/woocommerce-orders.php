<?php

add_filter( 'stm_lms_user_orders', 'stm_lms_user_orders_pro', 10, 4 );

function stm_lms_user_orders_pro( $response, $user_id, $pp, $offset ) {
	$posts     = array();
	$args      = array(
		'post_type'      => wc_get_order_types(),
		'posts_per_page' => $pp,
		'post_status'    => array_keys( wc_get_order_statuses() ),
		'offset'         => $offset,
		'customer_id'    => $user_id,
		'return'         => 'ids',
	);
	$order_ids = wc_get_orders( $args );
	$total     = count( $order_ids );

	if ( ! empty( $order_ids ) ) {
		foreach ( $order_ids as $order_id ) {
			$posts[] = STM_LMS_Order::get_order_info( $order_id );
		}
		wp_reset_postdata();
	}

	$args_count   = array(
		'post_type'      => wc_get_order_types(),
		'posts_per_page' => -1,
		'post_status'    => array_keys( wc_get_order_statuses() ),
		'customer_id'    => $user_id,
		'fields'         => 'ids',
	);
	$all_orders   = wc_get_orders( $args_count );
	$total_orders = count( $all_orders );

	return array(
		'total' => $total,
		'posts' => $posts,
		'pages' => $total_orders,
	);
}

add_filter( 'stm_lms_order_details', 'stm_lms_order_details_pro', 10, 2 );

function stm_lms_order_details_pro( $order, $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order_id || ! wc_get_order( $order_id ) ) {
		return array();
	}

	$order   = new WC_Order( $order_id );
	$user_id = $order->get_user_id();
	$items   = array();

	foreach ( $order->get_items() as $item ) {
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			$product_id = $item->get_product_id();

			$downloads      = $item->get_item_downloads();
			$downloads_data = array();

			if ( ! empty( $downloads ) ) {
				foreach ( $downloads as $download ) {
					$downloads_data[] = array(
						'name'                => $download['name'],
						'url'                 => $download['download_url'],
						'downloads_remaining' => $download['downloads_remaining'],
						'access_expires'      => $download['access_expires'],
					);
				}
			}

			$items[] = array(
				'item_id'   => $product_id,
				'price'     => $item->get_total(),
				'downloads' => $downloads_data,
			);
		}
	}

	$billing_address = array(
		'first_name'  => $order->get_billing_first_name(),
		'last_name'   => $order->get_billing_last_name(),
		'company'     => $order->get_billing_company(),
		'address_1'   => $order->get_billing_address_1(),
		'address_2'   => $order->get_billing_address_2(),
		'city'        => $order->get_billing_city(),
		'postcode'    => $order->get_billing_postcode(),
		'country'     => $order->get_billing_country(),
		'state'       => $order->get_billing_state(),
		'email'       => $order->get_billing_email(),
		'phone'       => $order->get_billing_phone(),
		'transaction' => $order->get_transaction_id(),
	);

	return array(
		'user_id'      => $user_id,
		'status'       => $order->get_status(),
		'status_name'  => wc_get_order_status_name( $order->get_status() ),
		'items'        => $items,
		'date'         => strtotime( $order->get_date_created() ),
		'order_key'    => "#{$order_id}",
		'payment_code' => $order->get_payment_method_title(),
		'billing'      => $billing_address,
	);
}
