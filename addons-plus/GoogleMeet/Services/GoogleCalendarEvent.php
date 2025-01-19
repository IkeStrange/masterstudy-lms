<?php

namespace MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use MasterStudy\Lms\Plugin\PostType;
use STM_LMS_User;

class GoogleCalendarEvent {
	public static function get_google_client( $authot_id = null ) {
		$user_id = get_current_user_id();
		if ( $authot_id ) {
			$user_id = $authot_id;
		}
		$google_api_token  = get_user_meta( $user_id, GoogleOpenAuth::TOKEN_NAME, true );
		$google_api_config = get_user_meta( $user_id, GoogleOpenAuth::CONFIG_NAME, true );

		$client = new Google_Client();
		$client->setApplicationName( 'Google Meet API' );
		$client->setScopes( array( Google_Service_Calendar::CALENDAR_EVENTS ) );
		$client->setClientId( $google_api_config['web']['client_id'] ?? '' );
		$client->setClientSecret( $google_api_config['web']['client_secret'] ?? '' );
		$client->setAccessType( 'offline' );

		// Check if access token is expired and refresh it if necessary
		if ( $client->isAccessTokenExpired() ) {
			$client->refreshToken( $google_api_token['refresh_token'] ?? '' );

			$new_access_token               = $client->getAccessToken();
			$new_access_token['expires_in'] = 3600;

			update_user_meta( $user_id, GoogleOpenAuth::TOKEN_NAME, $new_access_token );
		} else {
			$client->setAccessToken( $google_api_token['access_token'] ?? '' );
		}

		return new Google_Service_Calendar( $client );
	}

	public static function add_users_to_event( $attendees, $post_id ) {
		$meet_id   = get_post_meta( $post_id, 'google_meet_id' );
		$author_id = get_post_field( 'post_author', $post_id );

		$google_client      = self::get_google_client( $author_id );
		$event              = $google_client->events->get( 'primary', $meet_id[0] );
		$existing_attendees = $event->getAttendees();

		// Merge the existing attendees with the new attendees
		$attendees = array_merge( $existing_attendees, $attendees );

		// Set the updated attendees
		$event->setAttendees( $attendees );

		$google_client->events->update( 'primary', $event->getId(), $event );
	}

	public static function save_google_event( $post_id, $meeting ) {
		$meet_id       = get_post_meta( $post_id, 'google_meet_id' );
		$google_client = self::get_google_client();
		$event         = new Google_Service_Calendar_Event(
			self::generate_meeting_data( $meeting )
		);

		if ( ! empty( $meet_id ) ) {
			$event = $google_client->events->update( 'primary', $meet_id, $event, array( 'conferenceDataVersion' => 1 ) );
			update_post_meta( $post_id, 'stm_gma_timezone', $meeting['timezone'] );
			update_post_meta( $post_id, 'stm_gma_summary', $meeting['meeting_summary'] );
		} else {
			$event = $google_client->events->insert( 'primary', $event, array( 'conferenceDataVersion' => 1 ) );
		}

		update_post_meta( $post_id, 'google_meet_id', $event['id'] );
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		update_post_meta( $post_id, 'google_meet_link', $event->hangoutLink );
	}

	private static function generate_meeting_data( $meeting ) {
		$frontend_settings = get_user_meta( get_current_user_id(), 'frontend_instructor_google_meet_settings', true );
		if ( empty( $frontend_settings ) && current_user_can( 'administrator', get_current_user_id() ) ) {
			$meet_admin_settings = get_option( 'stm_lms_google_meet_settings', true );
			$send_updates        = ! empty( $meet_admin_settings['stm_gm_send_updates'] ) ? $meet_admin_settings['stm_gm_send_updates'] : 'all';
			$reminder            = ! empty( $meet_admin_settings['stm_gm_minute_reminder'] ) ? $meet_admin_settings['stm_gm_minute_reminder'] : 30;
		} else {
			$reminder     = ! empty( $frontend_settings['reminder'] ) ? $frontend_settings['reminder'] : 30;
			$send_updates = ! empty( $frontend_settings['send_updates'] ) ? $frontend_settings['send_updates'] : 'all';
		}

		return array(
			'summary'        => $meeting['meeting_name'],
			'description'    => $meeting['meeting_summary'],
			'start'          => array(
				'dateTime' => $meeting['start_date_time'],
				'timeZone' => $meeting['timezone'],
			),
			'end'            => array(
				'dateTime' => $meeting['end_date_time'],
				'timeZone' => $meeting['timezone'],
			),
			'sendUpdates'    => $send_updates,
			'reminders'      => array(
				'useDefault' => false,
				'overrides'  => array(
					array(
						'method'  => 'email',
						'minutes' => $reminder,
					),
					array(
						'method'  => 'popup',
						'minutes' => $reminder,
					),
				),
			),
			'conferenceData' => array(
				'createRequest' => array(
					'conferenceSolutionKey' => array(
						'type' => 'hangoutsMeet',
					),
					'requestId'             => uniqid(),
				),
			),
			'visibility'     => $meeting['visibility'] ?? 'public',
		);
	}

	private static function date_from_request( $date_field ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		$front_date_time = sanitize_text_field( $_POST[ "front_{$date_field}_date_time" ] ?? '' );

		if ( ! empty( $front_date_time ) ) {
			return $front_date_time . ':00';
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$post_date = sanitize_text_field( $_POST[ "stm_gma_{$date_field}_date" ] ?? '' );
		// phpcs:ignore WordPress.Security.NonceVerification
		$post_time = sanitize_text_field( $_POST[ "stm_gma_{$date_field}_time" ] ?? '' );

		if ( 'start' === $date_field ) {
			$post_date = masterstudy_lms_validate_google_meet_start_date( $post_date );
		}

		$date = gmdate( 'Y-m-d', (int) $post_date / 1000 );

		return "{$date}T{$post_time}:00";
	}

	public static function save_google_meeting( $post_id, $post, $update ) {
		if ( 'stm-google-meets' !== $post->post_type ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $post;
		}

		if ( 'publish' === $post->post_status ) {
			if ( ! empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$meeting = array(
					'meeting_name'    => $post->post_title,
					'meeting_summary' => sanitize_text_field( $_POST['stm_gma_summary'] ?? '' ), // phpcs:ignore WordPress.Security.NonceVerification
					'start_date_time' => self::date_from_request( 'start' ),
					'end_date_time'   => self::date_from_request( 'end' ),
					'timezone'        => sanitize_text_field( $_POST['stm_gma_timezone'] ?? '' ), // phpcs:ignore WordPress.Security.NonceVerification
					'visibility'      => sanitize_text_field( $_POST['stm_gma_visibility'] ?? 'public' ), // phpcs:ignore WordPress.Security.NonceVerification
				);

				self::save_google_event( $post_id, $meeting );
			}
		} elseif ( 'trash' === $post->post_status ) {
			try {
				$google_client = self::get_google_client();
				$meet_id       = get_post_meta( $post_id, 'google_meet_id' );

				$google_client->events->delete( 'primary', $meet_id );
			} catch ( \Google_Service_Exception $e ) {
				return $e->getMessage();
			}

			delete_post_meta( $post_id, 'google_meet_id' );
			delete_post_meta( $post_id, 'google_meet_link' );
		}

		return $post;
	}
}
