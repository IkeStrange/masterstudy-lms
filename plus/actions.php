<?php
/** @var \MasterStudy\Lms\Plugin $plugin */

// Load LMS Plugin Files
function masterstudy_lms_plus_load_lms_files( $plugin ) {
	// Load Pro Plus Routes
	$plugin->get_router()->load_routes( STM_LMS_PRO_PATH . '/rest-api/Routes/Analytics.php' );
}
add_action( 'masterstudy_lms_plugin_loaded', 'masterstudy_lms_plus_load_lms_files' );

function masterstudy_lms_pro_update_course_video_preview( $post_id, $course ) {
	foreach ( masterstudy_lms_course_preview_get_fields_meta_map() as $property => $meta_key ) {
		if ( isset( $course[ $property ] ) && ! empty( $course[ $property ] ) ) {
			update_post_meta( $post_id, $meta_key, $course[ $property ] );
		}
		if ( 'ext_link' === $course[ $property ] || 'html' === $course[ $property ] ) {
			update_post_meta( $post_id, 'video_poster', $course['video_poster'] );
			update_post_meta( $post_id, 'video', $course['video'] );
		}
	}
}
add_action( 'masterstudy_lms_course_video_saved', 'masterstudy_lms_pro_update_course_video_preview', 10, 2 );

function masterstudy_show_user_account_analytics_templates( $current_user ) {
	STM_LMS_Templates::show_lms_template( 'analytics/partials/short-report', array( 'current_user' => $current_user ) );
}
add_action( 'masterstudy_show_analytics_templates', 'masterstudy_show_user_account_analytics_templates' );
