<?php
$is_user_account = ! is_admin();

if ( $is_user_account ) {
	get_header();
}

wp_enqueue_style( 'masterstudy-grades-student' );
wp_enqueue_style( 'masterstudy-analytics-components' );
wp_enqueue_script( 'masterstudy-grades-student' );

$grades_columns = array(
	array(
		'title'     => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
		'data'      => 'course',
		'orderable' => false,
	),
	array(
		'title'     => esc_html__( 'Quizzes Completed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'      => 'quiz',
		'orderable' => false,
	),
	array(
		'title'     => esc_html__( 'Assignments Completed', 'masterstudy-lms-learning-management-system-pro' ),
		'data'      => 'assignment',
		'orderable' => false,
	),
	array(
		'title' => esc_html__( 'Final Grade', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'final_grade',
	),
	array(
		'title' => esc_html__( 'Enrolled', 'masterstudy-lms-learning-management-system-pro' ),
		'data'  => 'start_time',
	),
);

wp_localize_script(
	'masterstudy-grades-student',
	'grades_student_data',
	array(
		'columns'         => $grades_columns,
		'current_user'    => get_current_user_id(),
		'attempts'        => esc_html__( 'attempts', 'masterstudy-lms-learning-management-system-pro' ),
		'grade_separator' => esc_js( STM_LMS_Options::get_option( 'grades_scores_separator', '/' ) ),
		'not_started'     => esc_html__( 'Not finished', 'masterstudy-lms-learning-management-system-pro' ),
	)
);

$dates = array(
	'this_month' => esc_html__( 'This month', 'masterstudy-lms-learning-management-system-pro' ),
	'today'      => esc_html__( 'Today', 'masterstudy-lms-learning-management-system-pro' ),
	'yesterday'  => esc_html__( 'Yesterday', 'masterstudy-lms-learning-management-system-pro' ),
	'this_week'  => esc_html__( 'This week', 'masterstudy-lms-learning-management-system-pro' ),
	'last_week'  => esc_html__( 'Last week', 'masterstudy-lms-learning-management-system-pro' ),
	'last_month' => esc_html__( 'Last month', 'masterstudy-lms-learning-management-system-pro' ),
	'this_year'  => esc_html__( 'This year', 'masterstudy-lms-learning-management-system-pro' ),
	'last_year'  => esc_html__( 'Last year', 'masterstudy-lms-learning-management-system-pro' ),
	'all_time'   => esc_html__( 'All time', 'masterstudy-lms-learning-management-system-pro' ),
);

STM_LMS_Templates::show_lms_template( 'components/grade-details' );

if ( $is_user_account ) {
	do_action( 'stm_lms_template_main' );
	?>
	<div class="stm-lms-wrapper user-account-page">
		<div class="container">
			<?php
			do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() );
}
?>
			<div class="masterstudy-grades-student">
				<div class="masterstudy-grades-student__header">
					<h1 class="masterstudy-grades-student__title">
						<?php echo esc_html__( 'My Grades', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</h1>
					<div class="masterstudy-grades-student__sorting">
						<select name="date"
								class="masterstudy-grades-student__select"
								id="date-select">
							<?php foreach ( $dates as $name => $item ) { ?>
								<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $item ); ?></option>
							<?php } ?>
						</select>
						<div class="masterstudy-grades-student__search-wrapper">
							<input type="text" id="courses-filter" class="masterstudy-grades-student__search grades-search" data-id="<?php echo esc_attr( $_GET['course'] ?? '' ); ?>" data-column="course_id"
								placeholder="<?php echo esc_html__( 'Search by course', 'masterstudy-lms-learning-management-system-pro' ); ?>">
							<span class="masterstudy-grades-student__search-label"></span>
							<div class="masterstudy-grades-student__search-dropdown"></div>
						</div>
					</div>
				</div>
				<div class="masterstudy-grades-student-table" data-chart-id="grades-table">
					<?php STM_LMS_Templates::show_lms_template( 'components/analytics/loader', array( 'loader_type' => 'table-loader' ) ); ?>
					<div class="masterstudy-grades-student-table__wrapper">
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/analytics/datatable',
							array(
								'id'      => 'grades',
								'columns' => $grades_columns,
							)
						);
						?>
					</div>
				</div>
			</div>
<?php
if ( $is_user_account ) {
	?>
		</div>
	</div>
	<?php
	get_footer();
}
