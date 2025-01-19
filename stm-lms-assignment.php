<?php
/**
 * @var $assignment_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

do_action( 'stm_lms_template_main' );
?>

<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

	<div class="stm-lms-wrapper stm-lms-wrapper--assignments user-account-page">

		<div class="container">

			<?php do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() ); ?>

			<div>
				<?php
				STM_LMS_Templates::show_lms_template(
					'account/private/instructor_parts/assignments/single/main',
					compact( 'assignment_id' )
				);
				?>
			</div>

		</div>

	</div>

<?php get_footer(); ?>
