<?php
get_header();

wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_style( 'masterstudy-analytics-sales-page' );
wp_enqueue_script( 'masterstudy-analytics-sales-page' );

$order_columns = array(
	array(
		'title' => esc_html__( '№', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'order_id',
	),
	array(
		'title' => esc_html__( 'Date', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'date',
	),
	array(
		'title' => esc_html__( 'User', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'user_info',
	),
	array(
		'title' => esc_html__( 'Order items', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'total_items',
	),
	array(
		'title' => esc_html__( 'Payment method', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'payment_code',
	),
	array(
		'title' => esc_html__( 'Status', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'status_name',
	),
	array(
		'title' => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'total_price',
	),
	array(
		'title' => '',
		'data'  => 'order_id',
	),
);

wp_localize_script(
	'masterstudy-analytics-sales-page',
	'users_page_data',
	array(
		'instructor-orders' => $order_columns,
		'titles'            => array(
			'instructors_chart' => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
			'users_chart'       => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
		),
	)
);

do_action( 'stm_lms_template_main' );
?>
	<div class="stm-lms-wrapper user-account-page">
		<div class="container">
			<?php do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() ); ?>
			<div class="masterstudy-analytics-sales-page__header">
				<h1 class="masterstudy-analytics-sales-page__title">
					<?php echo esc_html__( 'My Sales', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</h1>
				<?php STM_LMS_Templates::show_lms_template( 'components/analytics/date-field' ); ?>
			</div>
			<div class="masterstudy-analytics-sales-page__separator">
				<span class="masterstudy-analytics-sales-page__separator-short"></span>
				<span class="masterstudy-analytics-sales-page__separator-long"></span>
			</div>
			<?php
				STM_LMS_Templates::show_lms_template(
					'components/analytics/datepicker-modal',
					array(
						'id' => 'instructor-orders',
					)
				);
				?>
			<div class="masterstudy-analytics-sales-page-table" data-chart-id="instructor-orders-table">
				<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
				<div class="masterstudy-analytics-sales-page-table__wrapper">
					<div class="masterstudy-analytics-sales-page-table__header">
						<div class="masterstudy-analytics-sales-page-table__title">
							<?php echo esc_html__( 'Sales', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</div>
						<div class="masterstudy-analytics-sales-page-table__search-wrapper">
							<input type="text" id="table-sales-search" class="masterstudy-analytics-sales-page-table__search" placeholder="<?php echo esc_html__( 'Search by email, student, order id', 'masterstudy-lms-learning-management-system-pro' ); ?>">
							<span class="masterstudy-analytics-sales-page-table__search-icon"></span>
						</div>
					</div>
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/analytics/datatable',
						array(
							'id'      => 'instructor-orders',
							'columns' => $order_columns,
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
<?php
	get_footer();
