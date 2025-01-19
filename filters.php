<?php

// Add Grade field to User Assignments table
function masterstudy_lms_student_assignments_grades_column( $columns ) {
	return array_slice( $columns, 0, 5, true ) +
		array( 'lms_grade' => esc_html__( 'Grade', 'masterstudy-lms-learning-management-system-pro' ) ) +
		array_slice( $columns, 5, count( $columns ) - 1, true );
}
add_filter( 'manage_stm-user-assignment_posts_columns', 'masterstudy_lms_student_assignments_grades_column', 99 );

function masterstudy_lms_grades_menu_item( $menus ) {
	if ( STM_LMS_Instructor::is_instructor() ) {
		$menus[] = array(
			'order'        => 160,
			'id'           => 'grades',
			'slug'         => 'grades',
			'lms_template' => 'grades/instructor',
			'menu_title'   => esc_html__( 'Grades', 'masterstudy-lms-learning-management-system-pro' ),
			'menu_icon'    => 'masterstudy-grades-icon',
			'menu_url'     => ms_plugin_user_account_url( 'grades' ),
			'menu_place'   => 'main',
		);
	}

	$menus[] = array(
		'order'        => 180,
		'id'           => 'my-grades',
		'slug'         => 'my-grades',
		'lms_template' => 'grades/student',
		'menu_title'   => esc_html__( 'My Grades', 'masterstudy-lms-learning-management-system-pro' ),
		'menu_icon'    => 'masterstudy-my-grades-icon',
		'menu_url'     => ms_plugin_user_account_url( 'my-grades' ),
		'menu_place'   => 'learning',
	);

	return $menus;
}
add_filter( 'stm_lms_menu_items', 'masterstudy_lms_grades_menu_item' );

function masterstudy_show_grades_link( $post, $id ) {
	$post['grades_link'] = STM_LMS_User::login_page_url() . 'grades/?course=' . $id;

	return $post;
}
add_filter( 'masterstudy_add_grades_link', 'masterstudy_show_grades_link', 10, 2 );
