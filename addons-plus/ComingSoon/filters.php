<?php

/**
 * Upcoming Course Status Nuxy Settings
 */
function masterstudy_lms_coming_soon_settings( $setups ) {
	$fields = array(
		'credentials' => array(
			'name'   => esc_html__( 'Settings', 'masterstudy-lms-learning-management-system-pro' ),
			'label'  => esc_html__( 'Settings', 'masterstudy-lms-learning-management-system-pro' ),
			'fields' => array(
				'lms_coming_soon_instructor_allow_status' => array(
					'type'        => 'checkbox',
					'label'       => esc_html__( 'Allow instructors to enable Upcoming course status', 'masterstudy-lms-learning-management-system-pro' ),
					'description' => esc_html__( 'Instructors can create a course that it will be available in the future', 'masterstudy-lms-learning-management-system-pro' ),
				),
				'lms_coming_soon_pre_ordering_status'     => array(
					'type'        => 'checkbox',
					'label'       => esc_html__( 'Course preordering', 'masterstudy-lms-learning-management-system-pro' ),
					'description' => esc_html__( 'Your students can buy the course but can’t start until the specified course launch date', 'masterstudy-lms-learning-management-system-pro' ),
				),
				'lms_coming_soon_course_bundle_status'    => array(
					'type'        => 'checkbox',
					'label'       => esc_html__( 'Allow upcoming courses to be added to the Course bundles', 'masterstudy-lms-learning-management-system-pro' ),
					'description' => esc_html__( 'Enable this option to allow upcoming courses to be included in course bundles', 'masterstudy-lms-learning-management-system-pro' ),
				),
			),
		),
	);

	$setups[] = array(
		'page'        => array(
			'parent_slug' => 'stm-lms-settings',
			'page_title'  => esc_html__( 'Upcoming Course Status', 'masterstudy-lms-learning-management-system-pro' ),
			'menu_title'  => esc_html__( 'Upcoming Course Status', 'masterstudy-lms-learning-management-system-pro' ),
			'menu_slug'   => 'upcoming-course-status',
		),
		'fields'      => $fields,
		'option_name' => 'masterstudy_lms_coming_soon_settings',
	);

	return $setups;
}
add_filter( 'wpcfto_options_page_setup', 'masterstudy_lms_coming_soon_settings' );

/**
 * Upcoming Course Status Email Manager Settings
 */
function masterstudy_lms_coming_soon_emails( $settings ) {
	$email_textarea = 'hint_textarea';
	if ( defined( 'STM_WPCFTO_VERSION' ) && STM_LMS_Helpers::is_pro_plus() ) {
		$email_textarea = 'trumbowyg';
	}

	$email_branding = array(
		'upcoming-course-status' => array(
			'name'   => 'Upcoming',
			'fields' => array(
				'masterstudy_lms_coming_soon_availability_status'  => array(
					'type'  => 'checkbox',
					'group' => 'started',
					'label' => esc_html__( 'The course has become available for completion (to subscribers)', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => true,
				),
				'masterstudy_lms_coming_soon_availability_subject' => array(
					'type'  => 'text',
					'label' => esc_html__( 'Subject', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => esc_html__( 'The course is now available for you to take ', 'masterstudy-lms-learning-management-system-pro' ),
				),
				'masterstudy_lms_coming_soon_availability_message' => array(
					'type'  => $email_textarea,
					'label' => esc_html__( 'Message', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => 'The {{course_title}} is now available for you to take ',
					'hints' => array(
						'course_title' => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
				'masterstudy_lms_coming_soon_availability_hidden'   => array(
					'type'  => 'send_email',
					'group' => 'ended',
					'label' => esc_html__( 'Hidden', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => 'masterstudy_lms_coming_soon_availability',
				),
				'masterstudy_lms_coming_soon_pre_sale_status'      => array(
					'type'  => 'checkbox',
					'group' => 'started',
					'label' => esc_html__( 'The course is now available for pre-sale (to subscribers)', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => true,
				),
				'masterstudy_lms_coming_soon_pre_sale_subject'     => array(
					'type'  => 'text',
					'label' => esc_html__( 'Subject', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => esc_html__( 'The course is now available for pre-sale', 'masterstudy-lms-learning-management-system-pro' ),
				),
				'masterstudy_lms_coming_soon_pre_sale_message'     => array(
					'type'  => $email_textarea,
					'label' => esc_html__( 'Message', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => 'The {{course_title}} is now available for pre-sale',
					'hints' => array(
						'course_title' => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
				'masterstudy_lms_coming_soon_pre_sale_hidden'   => array(
					'type'  => 'send_email',
					'group' => 'ended',
					'label' => esc_html__( 'Hidden', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => 'masterstudy_lms_coming_soon_pre_sale',
				),
				'masterstudy_lms_coming_soon_start_date_status'    => array(
					'type'  => 'checkbox',
					'group' => 'started',
					'label' => esc_html__( 'Course start date has been changed (to subscribers)', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => true,
				),
				'masterstudy_lms_coming_soon_start_date_subject'   => array(
					'type'  => 'text',
					'label' => esc_html__( 'Subject', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => esc_html__( 'Course start date has been changed', 'masterstudy-lms-learning-management-system-pro' ),
				),
				'masterstudy_lms_coming_soon_start_date_message'   => array(
					'type'  => $email_textarea,
					'label' => esc_html__( 'Message', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => '{{course_title}} start date has been changed',
					'hints' => array(
						'course_title' => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
				'masterstudy_lms_coming_soon_start_date_hidden'   => array(
					'type'  => 'send_email',
					'group' => 'ended',
					'label' => esc_html__( 'Hidden', 'masterstudy-lms-learning-management-system-pro' ),
					'value' => 'masterstudy_lms_coming_soon_start_date',
				),
			),
		),
	);

	return array_merge( $settings, $email_branding );
}
add_filter( 'stm_lms_email_manager_settings', 'masterstudy_lms_coming_soon_emails', 999, 1 );
