<?php

new STM_LMS_Prerequisites();

class STM_LMS_Prerequisites {

	public function __construct() {
		add_filter( 'stm_lms_pro_show_button', array( $this, 'is_prerequisite' ), 100, 2 );

		// TODO: This old masterstudy_prerequisite_button function will need to be removed, as the new masterstudy_prerequisite_button is placed above it.
		add_action( 'stm_lms_pro_instead_buttons', array( $this, 'instead_buy_buttons' ) );

		add_action( 'masterstudy_prerequisite_button', array( $this, 'masterstudy_prerequisite_button' ), 100, 2 );

		add_filter(
			'stm_wpcfto_autocomplete_prerequisites_output',
			array(
				$this,
				'add_image_to_autocomplete',
			),
			10,
			1
		);
	}

	public static function get_prereq_courses( $course_id ) {
		return get_post_meta( $course_id, 'prerequisites', true );
	}

	public static function is_prerequisite( $show, $course_id ) {
		$prerequisite = self::get_prereq_courses( $course_id );

		if ( empty( $prerequisite ) ) {
			return true;
		}

		$prerequisite  = explode( ',', $prerequisite );
		$passing_value = get_post_meta( $course_id, 'prerequisite_passing_level', true );
		$passing_value = ! empty( $passing_value ) ? $passing_value : 0;
		$user_id       = get_current_user_id();

		foreach ( $prerequisite as $course ) {
			$user_course = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $user_id, $course, array( 'progress_percent' ) ) );

			if ( 'publish' !== get_post_status( $course ) ) {
				return true;
			}

			/*Student do not have this course*/
			if ( empty( $user_course ) ) {
				return false;
			}

			$progress = ( ! empty( $user_course['progress_percent'] ) ) ? $user_course['progress_percent'] : 0;
			if ( $progress < $passing_value ) {
				return false;
			}
		}

		return true;
	}

	public function masterstudy_prerequisite_button( $course_id, $prerequisite_preview ) {
		if ( ! $this->is_prerequisite( true, $course_id ) ) {
			$prereq  = explode( ',', $this->get_prereq_courses( $course_id ) );
			$user    = STM_LMS_User::get_current_user();
			$user_id = ( ! empty( $user['id'] ) ) ? $user['id'] : 0;

			$user_courses = array();

			foreach ( $prereq as $course ) {
				if ( 'publish' === get_post_status( intval( $course ) ) ) {
					$user_course    = STM_LMS_Helpers::simplify_db_array(
						stm_lms_get_user_course(
							$user_id,
							$course,
							array(
								'course_id',
								'progress_percent',
							)
						)
					);
					$user_courses[] = ( ! empty( $user_course ) ) ? $user_course : array(
						'course_id'        => $course,
						'progress_percent' => 0,
					);
				}
			}

			if ( $prerequisite_preview ) {
				STM_LMS_Templates::show_lms_template( 'components/buy-button/paid-courses/prerequisite/prerequisite-info', array( 'courses' => $user_courses ) );
			} else {
				STM_LMS_Templates::show_lms_template( 'components/buy-button/paid-courses/prerequisite/prerequisite-button', array( 'courses' => $user_courses ) );
			}
		}
	}

	public function add_image_to_autocomplete( $posts ) {
		return array_map(
			function ( $post ) {

				if ( ! has_post_thumbnail( $post['id'] ) ) {
					return $post;
				}

				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post['id'] ), 'thumbnail' );
				if ( ! empty( $image[0] ) ) {
					$post['image'] = $image[0];
				}
				return $post;
			},
			$posts
		);
	}
}
