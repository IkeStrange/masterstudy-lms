<?php
namespace MasterStudy\Lms\Pro\AddonsPlus\SocialLogin\Providers;

use MasterStudy\Lms\Pro\AddonsPlus\SocialLogin\Provider;

class Google extends Provider {
	public const CONFIG = array(
		'is_enabled'      => array(
			'setting' => 'social_login_google_enabled',
			'default' => false,
		),
		'provider_id'     => array(
			'setting' => 'social_login_google_client_id',
			'default' => '',
		),
		'provider_secret' => array(
			'setting' => 'social_login_google_client_secret',
			'default' => '',
		),
	);

	public function set_client() {
		$this->set_provider_configs();

		$this->client = new \Google_Client();

		if ( $this->is_provider_enabled() && $this->is_provider_setup() ) {
			$this->client->setClientId( $this->settings['provider_id'] );
			$this->client->setClientSecret( $this->settings['provider_secret'] );
			$this->client->setRedirectUri( site_url( '/?addon=social_login&provider=google' ) );
			$this->client->addScope( 'email' );
			$this->client->addScope( 'profile' );
			$this->client->setAccessType( 'offline' );
			$this->client->setApprovalPrompt( 'force' );
			$this->client->setIncludeGrantedScopes( true );
		}
	}

	public function set_token_exchange_code( string $code ) {
		try {
			if ( $this->client->isAccessTokenExpired() ) {
				$this->token = $this->client->fetchAccessTokenWithAuthCode( $code );
			} else {
				$this->token = $this->client->getAccessToken();
				$this->client->setAccessToken( $this->token );
			}

			$user_data = $this->get_user_data( 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $this->token['access_token'] );
			$this->register_user( $user_data );

			return $this->token;
		} catch ( \Exception $e ) {
			return null;
		}
	}

	public function get_auth_url() {
		if ( $this->is_provider_enabled() && $this->is_provider_setup() ) {
			return $this->client->createAuthUrl();
		}

		return null;
	}
}
