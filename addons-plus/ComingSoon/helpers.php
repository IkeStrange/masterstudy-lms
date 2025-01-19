<?php

function masterstudy_lms_coming_soon_start_time( $course_id ) {
	$start_date = get_post_meta( $course_id, 'coming_soon_date', true );
	$start_time = get_post_meta( $course_id, 'coming_soon_time', true );

	if ( empty( $start_date ) ) {
		return '';
	}

	$offset = ( get_option( 'gmt_offset' ) * 60 * 60 );
	$result = strtotime( 'today', ( $start_date / 1000 ) ) - $offset;

	if ( ! empty( $start_time ) ) {
		$time = explode( ':', $start_time );
		if ( is_array( $time ) && count( $time ) === 2 ) {
			$result = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $result );
		}
	}

	return $result;
}

function masterstudy_lms_coming_soon_notify_students_by_course_id( $course_id, $key ) {
	$coming_soon_emails = get_post_meta( $course_id, 'coming_soon_student_emails', true );
	$email_manager      = \MasterStudy\Lms\Pro\addons\email_manager\EmailManagerSettings::get_all();
	if ( ! empty( $email_manager ) && ! empty( $coming_soon_emails ) ) {
		$availability = $email_manager['masterstudy_lms_coming_soon_availability_status'];
		$preordering  = $email_manager['masterstudy_lms_coming_soon_pre_sale_status'];
		$start_date   = $email_manager['masterstudy_lms_coming_soon_start_date_status'];

		if ( $availability && 'notify' === $key ) {
			$subject = $email_manager['masterstudy_lms_coming_soon_availability_subject'];
			$message = $email_manager['masterstudy_lms_coming_soon_availability_message'];
		} elseif ( $preordering && 'preordering' === $key ) {
			$subject = $email_manager['masterstudy_lms_coming_soon_pre_sale_subject'];
			$message = $email_manager['masterstudy_lms_coming_soon_pre_sale_message'];
		} elseif ( $start_date && 'date_changed' === $key ) {
			$subject = $email_manager['masterstudy_lms_coming_soon_start_date_subject'];
			$message = $email_manager['masterstudy_lms_coming_soon_start_date_message'];
		}

		if ( isset( $message ) ) {
			$message = str_replace( '{{course_title}}', get_the_title( $course_id ), $message );

			foreach ( $coming_soon_emails as $email ) {
				STM_LMS_Helpers::send_email(
					$email['email'],
					$subject,
					$message,
					'stm_lms_filter_email_data',
					array(
						'course_title' => get_the_title( $course_id ),
					)
				);
			}
		}
	}
}
