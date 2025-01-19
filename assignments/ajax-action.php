<?php

use MasterStudy\Lms\Pro\addons\assignments\Repositories\AssignmentStudentRepository;

/**
 * Adds assignments attachments.
 *
 * @return void
 */
function stm_lms_add_assignment_attachment() {
	check_ajax_referer( 'wp_rest', 'nonce' );

	$attachment = $_POST['attachment'];
	$post_id    = intval( $_POST['post_id'] );
	$course_id  = intval( $_POST['course_id'] );
	$is_created = rest_sanitize_boolean( $_POST['is_created'] );
	$is_review  = rest_sanitize_boolean( $_POST['is_review'] );

	if ( isset( $attachment['id'] ) && $post_id ) {
		$is_attached      = false;
		$attachment_id    = intval( $attachment['id'] );
		$attached_post_id = get_post_field( 'post_parent', $attachment_id );

		if ( $attached_post_id > 0 && ! $is_created ) {
			global $wp_filesystem;
			if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
			}

			$file_path    = get_attached_file( $attachment_id );
			$file_dir     = str_replace( basename( $file_path ), '', $file_path );
			$file_name    = wp_unique_filename( $file_dir, $attachment['filename'] );
			$updated_path = trailingslashit( $file_dir ) . $file_name;

			if ( $wp_filesystem->copy( $file_path, $updated_path, true ) ) {
				$wp_filetype = wp_check_filetype( basename( $attachment['url'] ), null );
				$insert_args = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_parent'    => $post_id,
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				$attachment_id = wp_insert_attachment( $insert_args, $updated_path, $post_id );

				if ( ! is_wp_error( $attachment_id ) ) {
					require_once ABSPATH . 'wp-admin/includes/image.php';

					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $updated_path );
					wp_update_attachment_metadata( $attachment_id, $attachment_data );
					$is_attached = true;
				}
			}
		} else {
			$updated_attachment = wp_update_post(
				array(
					'ID'          => $attachment_id,
					'post_parent' => $post_id,
				)
			);

			$is_attached = ! is_wp_error( $updated_attachment );
		}

		if ( $is_attached ) {
			$attachment_type = $is_review ? 'instructor_attachments' : 'student_attachments';

			$attachments   = get_post_meta( $post_id, $attachment_type, true );
			$attachments   = ! empty( $attachments ) ? $attachments : array();
			$attachments[] = $attachment_id;

			if ( ! $is_review ) {
				update_post_meta( $post_id, 'course_id', $course_id );
			}

			update_post_meta( $post_id, $attachment_type, array_unique( $attachments ) );

			wp_send_json_success(
				array(
					'files_formats' => ms_plugin_files_formats(),
					'icon_url'      => STM_LMS_URL . '/assets/icons/files/new/',
				)
			);
		}
	}
	wp_send_json_error();
}
add_action( 'wp_ajax_stm_lms_add_assignment_attachment', 'stm_lms_add_assignment_attachment' );

/**
 * Deletes/deataches assignments attachments.
 *
 * @return void
 */
function stm_lms_delete_assignment_attachment() {
	check_ajax_referer( 'wp_rest', 'nonce' );

	$attachment_id   = intval( $_POST['attachment_id'] );
	$post_id         = intval( $_POST['post_id'] );
	$attachment_type = rest_sanitize_boolean( $_POST['is_review'] ) ? 'instructor_attachments' : 'student_attachments';

	if ( wp_delete_attachment( $attachment_id, true ) ) {
		$attachments = get_post_meta( $post_id, $attachment_type, true );

		if ( ! empty( $attachments ) ) {
			$key = array_search( $attachment_id, $attachments, true );

			if ( false !== $key ) {
				unset( $attachments[ $key ] );
				update_post_meta( $post_id, $attachment_type, $attachments );
			}
		}

		wp_send_json_success();
	}

	wp_send_json_error();
}
add_action( 'wp_ajax_stm_lms_delete_assignment_attachment', 'stm_lms_delete_assignment_attachment' );

function stm_lms_assignment_student_answer() {
	check_ajax_referer( 'wp_rest', 'nonce' );

	$status        = sanitize_text_field( $_POST['status'] ?? '' );
	$review        = wp_kses_post( $_POST['review'] );
	$assignment_id = intval( $_POST['assignment_id'] );

	$student_id = get_post_meta( $assignment_id, 'student_id', true );
	$course_id  = get_post_meta( $assignment_id, 'course_id', true );

	wp_update_post(
		array(
			'ID'          => $assignment_id,
			'post_status' => 'publish',
		)
	);

	update_post_meta( $assignment_id, 'editor_comment', $review );
	update_post_meta( $assignment_id, 'who_view', 0 );

	// Update Assignment Grade
	if ( is_ms_lms_addon_enabled( 'grades' ) ) {
		$status = masterstudy_lms_update_user_assignment_grade( $assignment_id, $_POST, $status );
	}

	// Update Assignment Status
	( new AssignmentStudentRepository() )->update_status( $assignment_id, $status );

	STM_LMS_Course::update_course_progress( $student_id, $course_id );

	if ( STM_LMS_Instructor::is_instructor() ) {
		$student = STM_LMS_User::get_current_user( $student_id );

		$message = esc_html__( 'Your assignment has been checked', 'masterstudy-lms-learning-management-system-pro' );
		STM_LMS_Helpers::send_email(
			$student['email'],
			esc_html__( 'Assignment status change.', 'masterstudy-lms-learning-management-system-pro' ),
			$message,
			'stm_lms_assignment_checked',
			compact( 'message' )
		);
	}

	do_action( 'stm_lms_assignment_' . $status, $student_id, $assignment_id, $course_id );

	wp_send_json_success();
}
add_action( 'wp_ajax_stm_lms_assignment_student_answer', 'stm_lms_assignment_student_answer' );
