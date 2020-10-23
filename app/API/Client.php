<?php

namespace LevelLevel\VoorDeMensen\General;

use LevelLevel\VoorDeMensen\Admin\Settings\General\Fields\ClientName as ClientNameSetting;
use WP_Error;

/**
 * API client for VoordeMensen
 *
 * To test the integration, use 'demo' as client name
 */
class Client {
	protected const BASE_API_URL = 'https://api.voordemensen.nl/v1/';

	/**
	 * Get array of API event response objects
	 *
	 * @return object[]
	 */
	public function get_events(): array {
		$url = $this->get_events_url();
		if ( ! $url ) {
			return array();
		}

		$response = $this->request( $url );
		if ( ! isset( $response['success'] ) || ! $response['success'] || ! is_array( $response['data'] ) ) {
			return array();
		}

		// Filter out object errors
		return array_filter(
			$response['data'], function( $api_event ) {
				return isset( $api_event->event_id );
			}
		);
	}

	/**
	 * Get API event response object
	 *
	 * @param int $vdm_id
	 * @return object|null
	 */
	public function get_event( int $vdm_id ) {
		$url = $this->get_events_url( $vdm_id );
		if ( ! $url ) {
			return null;
		}

		$response = $this->request( $url );
		if ( ! isset( $response['success'] ) || ! $response['success'] || ! is_array( $response['data'] ) || empty( $response['data'] ) ) {
			return null;
		}

		// Filter out object errors
		if ( ! is_object( $response['data'][0] ) || ! isset( $response['data'][0]->event_id ) ) {
			return null;
		}

		return $response['data'][0];
	}

	/**
	 * Perform an API request
	 */
	public function request( string $url, string $method = 'GET', array $request_args = array() ): array {
		$default_args     = array(
			'method'  => $method,
			'timeout' => 5,
		);
		$request_args     = wp_parse_args( $request_args, $default_args );
		$response         = wp_remote_request( $url, $request_args );
		$status_code      = wp_remote_retrieve_response_code( $response );
		$json_string_body = wp_remote_retrieve_body( $response );
		$data             = json_decode( $json_string_body );

		$response = array(
			'success'      => ! $response instanceof WP_Error,
			'url'          => $url,
			'request_args' => $request_args,
			'status_code'  => $status_code,
			'wp_error'     => $response instanceof WP_Error ? $response : null,
			'data'         => $data,
		);

		return $response;
	}

	/**
	 * Get url for the events endpoint
	 */
	protected function get_events_url( int $vdm_id = null ): ?string {
		$client_name = ( new ClientNameSetting() )->get_value();
		if ( empty( $client_name ) ) {
			return null;
		}

		$url = self::BASE_API_URL . rawurldecode( $client_name ) . '/events/';
		if ( $vdm_id ) {
			$url .= $vdm_id;
		}

		return $url;
	}
}
