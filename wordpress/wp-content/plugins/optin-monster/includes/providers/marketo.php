<?php

/**
 * Marketo provider class.
 *
 * @since   2.1.0
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton
 */
class Optin_Monster_Provider_Marketo extends Optin_Monster_Provider {

	/**
	 * Path to the file.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Slug of the provider.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $provider = 'marketo';

	/**
	 * Holds the Marketo API instance.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $api = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		// Construct via the parent object.
		parent::__construct();

	}

	/**
	 * Authentication method for providers.
	 *
	 * @since 2.1.0
	 *
	 * @param array $args     Args to be passed for authentication.
	 * @param int   $optin_id The optin ID to target.
	 *
	 * @return string
	 */
	public function authenticate( $args = array(), $optin_id ) {

		$this->api['client_id']     = $args['om-client-id'];
		$this->api['client_secret'] = $args['om-client-secret'];
		$this->api['subdomain']     = $args['om-subdomain'];

		// Get an access token from Marketo
		$uniqid = uniqid();
		try {
			$auth = $this->get_access_token( $uniqid, $this->api, false );
			$this->api['access'] = trim( $auth->access_token );
			$this->api['expires'] = time() + $auth->access_token;
		} catch ( Exception $e ) {
			return $this->error( 'api-error', $e->getMessage() );
		}

		// Save the info
		$providers                                                = Optin_Monster_Common::get_instance()->get_email_providers( true );
		$providers[ $this->provider ][ $uniqid ]['subdomain']     = trim( $this->api['subdomain'] );
		$providers[ $this->provider ][ $uniqid ]['client_id']     = trim( $this->api['client_id'] );
		$providers[ $this->provider ][ $uniqid ]['client_secret'] = trim( $this->api['client_secret'] );
		$providers[ $this->provider ][ $uniqid ]['access']        = $this->api['access'];
		$providers[ $this->provider ][ $uniqid ]['expires']       = $this->api['expires'];
		$providers[ $this->provider ][ $uniqid ]['label']         = trim( strip_tags( $args['om-account-label'] ) );
		update_option( 'optin_monster_providers', $providers );

		// Store the account reference in the optin data.
		$this->save_account( $optin_id, $this->provider, $uniqid );

		// Get the lists
		return $this->get_lists();

	}

	/**
	 * Retrieval method for getting lists.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $args    Args to be passed for list retrieval.
	 * @param string $list_id The list ID to check for selection.
	 * @param string $uniqid
	 * @param string $optin_id
	 *
	 * @return string
	 */
	public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

		if ( ! $this->api ) {
			$this->api['subdomain']     = $args['subdomain'];
			$this->api['access']        = $args['access'];
			$this->api['expires']       = $args['expires'];
			$this->api['client_id']     = $args['client_id'];
			$this->api['client_secret'] = $args['client_secret'];
		}

		// If needed, get a new access token
		if ( time() > $this->api['expires'] ) {
			$auth                 = $this->get_access_token( $uniqid, $this->api );
			$this->api['access']  = $auth->access_token;
			$this->api['expires'] = time() + $auth->expires_in;
		}

		// Now get the lists from Marketo
		$url = "https://{$this->api['subdomain']}.mktorest.com/rest/v1/lists.json?access_token={$this->api['access']}";

		// Make the request
		$request = wp_remote_get( $url );

		// Get the response body
		$body = json_decode( wp_remote_retrieve_body( $request ), true );

		// Return an error if something went wrong
		if ( array_key_exists( 'error', $body ) ) {
			return $this->error( 'api-error', 'There was an error with the API request' );
		}

		// Else return the list
		return $this->build_list_html( $body['result'], $list_id, $optin_id );

	}

	/**
	 * Method for building out the list selection HTML.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $lists Lists for the email provider.
	 * @param string $list_id
	 * @param string $optin_id
	 *
	 * @return string $html HTML string for selecting lists.
	 */
	protected function build_list_html( $lists, $list_id = '', $optin_id = '' ) {

		$output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
		$output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-list">' . __( 'Email provider list', 'optin-monster' ) . '</label><br />';
		$output .= '<select id="optin-monster-provider-list" name="optin_monster[provider_list]">';
		foreach ( $lists as $list ) {
			$output .= '<option value="' . $list['id'] . '"' . selected( $list_id, $list['id'], false ) . '>' . $list['name'] . '</option>';
		}
		$output .= '</select>';
		$output .= '</p>';
		$output .= '</div>';

		return $output;

	}

	/**
	 * Method for opting into the email service provider.
	 *
	 * @since    2.1.0
	 *
	 * @param array $account
	 * @param       $list_id
	 * @param       $lead
	 *
	 * @internal param array $args Args to be passed when opting in.
	 */
	public function optin( $account = array(), $list_id, $lead ) {

		$this->api['subdomain'] = $account['subdomain'];
		$this->api['access'] = $account['access'];

		// If needed, get a new access token
		if ( time() > $account['expires'] ) {
			$auth                 = $this->get_access_token( $account['account_id'], $account );
			$this->api['access']  = $auth->access_token;
			$this->api['expires'] = time() + $auth->expires_in;
		}

		// Setup the data to be passed
		$data = array(
			'email' => $lead['lead_email'],
		);

		// Add name if set
		if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
			$names = explode( ' ', $lead['lead_name'] );
			if ( isset( $names[0] ) ) {
				$data['firstName'] = $names[0];
			}
			if ( isset( $names[1] ) ) {
				$data['lastName'] = $names[1];
			}
		}

		$data = apply_filters( 'optin_monster_pre_optin_marketo', $data, $lead, $list_id, $this->api );

		// Create or update the lead in Marketo
		$leadUrl = "https://{$this->api['subdomain']}.mktorest.com/rest/v1/leads.json?access_token={$this->api['access']}";

		// Create the array to send to Marketo
		$body = array(
			'lookupField' => 'email',
			'input' => array( $data ),
		);

		$args = array(
			'headers' => array(
				'content-type' => 'application/json',
			),
			'body' => json_encode( $body ),
		);

		// Send the request
		$leadRequest = wp_remote_post( $leadUrl, $args );
		$leadResponse = json_decode( wp_remote_retrieve_body( $leadRequest ), true );

		// Return an error if something went wrong
		if ( array_key_exists( 'errors', $leadResponse ) ) {
			return $this->error(
				'lead-error',
				__( 'There was an error saving the information. ', 'optin-monster' ) . $leadResponse['errors'][0]['message']
			);
		}

		// Now save the lead to the correct list
		$listUrl = "https://{$this->api['subdomain']}.mktorest.com/rest/v1/lists/{$list_id}/leads.json?id={$leadResponse['result'][0]['id']}&access_token={$this->api['access']}";

		$args = array(
			'headers' => array(
				'content-type' => 'application/json',
			),
		);

		$listRequest = wp_remote_post( $listUrl, $args );
		$listResponse = json_decode( wp_remote_retrieve_body( $listRequest ), true );

		// Return an error if something went wrong
		if ( array_key_exists( 'errors', $leadResponse ) ) {
			return $this->error(
				'list-error',
				__( 'There was an error saving the information. ', 'optin-monster' ) . $leadResponse['errors'][0]['message']
			);
		}

		return true;

	}

	private function get_access_token( $uniqid, $args, $refresh = true ) {

		// Build the request URL
		$url = "https://{$args['subdomain']}.mktorest.com/identity/oauth/token?grant_type=client_credentials&client_id={$args['client_id']}&client_secret={$args['client_secret']}";

		// Send the auth request
		$request = wp_remote_get( $url );

		$body = json_decode( wp_remote_retrieve_body( $request ) );

		if ( array_key_exists( 'error', $body ) ) {
			throw new Exception( 'The API credentials you provided are not correct.' );
		}

		// Save the new account info if this is a refresh
		if ( $refresh ) {
			$providers                                           = Optin_Monster_Common::get_instance()->get_email_providers( true );
			$providers[ $this->provider ][ $uniqid ]['access']   = $body->access_token;
			$providers[ $this->provider ][ $uniqid ]['expires']  = time() + $body->expires_in;

			update_option( 'optin_monster_providers', $providers );
		}

		// Return the new access token for immediate use.
		return $body;

	}
}