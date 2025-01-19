<?php
get_header();
do_action( 'stm_lms_template_main' );

stm_lms_register_style( 'user_info_top' );
stm_lms_register_style( 'user-orders' );
wp_enqueue_style( 'masterstudy-woocommerce-orders' );
wp_enqueue_script( 'masterstudy-woocommerce-orders' );
wp_localize_script(
	'masterstudy-woocommerce-orders',
	'masterstudy_woocommerce_orders',
	array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ms_lms_nonce' ),
	)
);

$order_info = STM_LMS_Order::get_order_info( $order_id );

STM_LMS_Templates::show_lms_template( 'modals/preloader' );
?>
<div class="stm-lms-wrapper user-account-page">
	<div class="container">
		<?php do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() ); ?>
		<div class="masterstudy-orders">
			<?php
			$settings = get_option( 'stm_lms_settings' );
			$logo_url = $settings['print_page_logo'] ?? null;

			if ( $logo_url ) {
				echo '<img src="' . esc_url( wp_get_attachment_url( $logo_url ) ) . '" style="display: none;" width="180" height="40" class="masterstudy-orders__site-logo">';
			} else {
				echo '<img src="' . esc_url( STM_LMS_PRO_URL . '/assets/img/ms-logo.png' ) . '" style="display: none;" width="180" height="40" class="masterstudy-orders__site-logo">';
			}
			?>
			<div class="masterstudy-orders-details">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/button',
					array(
						'title'         => '',
						'link'          => ms_plugin_user_account_url( 'my-orders' ),
						'style'         => 'secondary',
						'size'          => 'sm',
						'icon_position' => 'left',
						'icon_name'     => 'arrow-left',
					)
				);
				?>
				<div class="masterstudy-orders-details__id">
					<span><?php echo esc_html__( 'Order:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
					<?php echo esc_html( $order_info['id'] ); ?>
				</div>
				<div class="masterstudy-orders-details__date">
					<span><?php echo esc_html__( 'Date:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
					<?php echo esc_html( $order_info['date_formatted'] ); ?>
				</div>
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/button',
					array(
						'title'         => esc_html__( 'Print', 'masterstudy-lms-learning-management-system-pro' ),
						'link'          => '#',
						'style'         => 'secondary',
						'size'          => 'sm',
						'id'            => 'print-button',
						'icon_position' => 'left',
						'icon_name'     => 'print',
					)
				);
				?>
			</div>
			<div class="multiseparator"></div>
			<?php
			$wire_transfer           = get_option( 'woocommerce_bacs_accounts' );
			$woocommerce_enable_bacs = get_option( 'woocommerce_bacs_settings' );
			if ( ! empty( $wire_transfer ) && STM_LMS_Cart::woocommerce_checkout_enabled() && 'yes' === $woocommerce_enable_bacs['enabled'] ) :
				?>
				<div class="masterstudy-payment-methods">
					<div class="masterstudy-payment-methods__title">
						<?php echo esc_html__( 'Bank details', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</div>
					<div class="masterstudy-payment-methods__table-woocommerce">
					<?php foreach ( $wire_transfer as $key => $account ) : ?>
						<div class="masterstudy-payment-methods__table">
							<div class="masterstudy-payment-methods__sub-title">
								<?php echo esc_html__( 'Method', 'masterstudy-lms-learning-management-system-pro' ) . ' ' . esc_html( $key + 1 ); ?>
							</div>
							<div class="masterstudy-payment-methods__table-column">
								<div class="masterstudy-payment-methods__name"><?php echo esc_html__( 'Bank', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
								<div class="masterstudy-payment-methods__value"><?php echo esc_html( $account['bank_name'] ); ?></div>
							</div>
							<div class="masterstudy-payment-methods__table-column">
								<div class="masterstudy-payment-methods__name"><?php echo esc_html__( 'Recipient', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
								<div class="masterstudy-payment-methods__value"><?php echo esc_html( $account['account_name'] ); ?></div>
							</div>
							<div class="masterstudy-payment-methods__table-column">
								<div class="masterstudy-payment-methods__name"><?php echo esc_html__( 'Account Number', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
								<div class="masterstudy-payment-methods__value"><?php echo esc_html( $account['account_number'] ); ?></div>
							</div>
							<div class="masterstudy-payment-methods__table-column">
								<div class="masterstudy-payment-methods__name"><?php echo esc_html__( 'Amount to be paid', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
								<div class="masterstudy-payment-methods__value"><?php echo esc_attr( $order_info['total'] ); ?></div>
							</div>
						</div>
					<?php endforeach; ?>
					</div>
				</div>
				<?php
			endif;

			if ( isset( $order_info['items'] ) && is_array( $order_info['items'] ) ) :
				?>
				<div class="masterstudy-orders-container">
					<div id="masterstudy-order-template">
						<div class="masterstudy-orders-table masterstudy-orders-table__details">
							<div class="masterstudy-orders-table__header">
								<div class="masterstudy-orders-course-info">
									<div class="masterstudy-orders-course-info__id"><?php echo esc_html__( 'Order details', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
								</div>
							</div>
							<div class="masterstudy-orders-table__body">
								<?php
									$items_info = array();

								foreach ( $order_info['items'] as $item ) {
									$items_info[ $item['item_id'] ] = array(
										'bundle_id'     => $item['bundle_id'] ?? null,
										'enterprise_id' => $item['enterprise_id'] ?? null,
									);

									if ( ! empty( $item['downloads'] ) ) {
										foreach ( $item['downloads'] as $download ) {
											$downloads_info[ $item['item_id'] ][] = array(
												'name' => $download['name'] ?? '',
												'url' => $download['url'] ?? '',
												'downloads_remaining' => $download['downloads_remaining'] ? $download['downloads_remaining'] : '∞',
												'access_expires' => $download['access_expires'] ?? __( 'Never', 'masterstudy-lms-learning-management-system-pro' ),
											);
										}
									}
								}

								foreach ( $order_info['cart_items'] as $item_id => $order ) :
									$bundle_id     = $items_info[ $item_id ]['bundle_id'] ?? null;
									$enterprise_id = $items_info[ $item_id ]['enterprise_id'] ?? null;
									?>
								<div class="masterstudy-orders-table__body-row">
									<div class="masterstudy-orders-course-info">
										<div class="masterstudy-orders-course-info__image">
											<a href="<?php echo esc_url( $order['link'] ); ?>">
												<img width="300" height="225" src="<?php echo esc_url( wp_get_attachment_url( $order['thumbnail_id'] ) ); ?>" class="attachment-img-300-225 size-img-300-225 wp-post-image" alt="<?php echo esc_attr( $order['title'] ); ?>" decoding="async" loading="lazy">
											</a>
										</div>
										<div class="masterstudy-orders-course-info__common">
											<div class="masterstudy-orders-course-info__title">
												<a href="<?php echo esc_url( $order['link'] ); ?>"><?php echo esc_html( $order['title'] ); ?></a>
												<?php if ( ! empty( $bundle_id ) ) : ?>
												<span class="order-status"><?php echo esc_html__( 'bundle', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
												<?php endif; ?>
												<?php if ( ! empty( $enterprise_id ) ) : ?>
												<span class="order-status"><?php echo esc_html__( 'enterprise', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
												<?php endif; ?>
											</div>
											<div class="masterstudy-orders-course-info__category">
											<?php
											if ( ! empty( $order['terms'] ) ) {
												echo esc_html( implode( ' ', $order['terms'] ) );
											}

											if ( ! empty( $order['bundle_courses_count'] ) ) {
												echo esc_html( $order['bundle_courses_count'] . ' ' . $order_info['i18n']['bundle'] );
											}
											?>
											</div>
										</div>
										<?php
										if ( ! empty( $downloads_info[ $item_id ] ) ) {
											foreach ( $downloads_info[ $item_id ] as $download ) {
												?>
												<div class="masterstudy-orders-course-info__download-label">
													<span><?php echo esc_html__( 'Downloads remaining', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
													<?php echo esc_html( $download['downloads_remaining'] ); ?>
												</div>
												<div class="masterstudy-orders-course-info__download-label">
													<span><?php echo esc_html__( 'Expires', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
													<?php echo esc_html( $download['access_expires'] ); ?>
												</div>
												<?php
											}
										}
										?>
										<div class="masterstudy-orders-course-info__price">
											<?php echo esc_html( $order['price_formatted'] ); ?>
										</div>
										<?php
										if ( ! empty( $downloads_info[ $item_id ] ) ) {
											foreach ( $downloads_info[ $item_id ] as $download ) {
												STM_LMS_Templates::show_lms_template(
													'components/button',
													array(
														'title' => esc_html( $download['name'] ),
														'link' => esc_url( $download['url'] ),
														'style' => 'secondary masterstudy-orders-course-info__button',
														'size' => 'sm',
													)
												);
											}
										} else {
											STM_LMS_Templates::show_lms_template(
												'components/button',
												array(
													'title' => esc_html__( 'Go to course', 'masterstudy-lms-learning-management-system-pro' ),
													'link'  => esc_url( $order['link'] ),
													'style' => 'secondary masterstudy-orders-course-info__button',
													'size'  => 'sm',
												)
											);
										}
										?>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
							<div class="masterstudy-orders-table__footer">
								<div class="masterstudy-orders-course-info">
									<div class="masterstudy-orders-course-info__label"><?php echo esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ); ?>:</div>
									<div class="masterstudy-orders-course-info__price"><?php echo esc_html( $order_info['total'] ); ?></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$template = STM_LMS_Cart::woocommerce_checkout_enabled()
					? 'account/private/parts/orders-details/customer-info-woocommerce'
					: 'account/private/parts/orders-details/customer-info-lms';

				STM_LMS_Templates::show_lms_template(
					$template,
					array(
						'order_id' => $order_id,
					)
				);
			endif;
			?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
