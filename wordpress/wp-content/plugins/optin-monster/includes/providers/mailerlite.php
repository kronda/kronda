<?php

class Optin_Monster_Provider_MailerLite extends Optin_Monster_Provider {

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
	public $provider = 'mailerlite';

	/**
	 * Holds the MailerLite Lists API instance.
	 *
	 * @since 2.1.0
	 *
	 * @var ML_Lists
	 */
	public $list_api = false;

	/**
	 * Holds the MailerLite Subscriber API instance
	 *
	 * @since 2.1.0
	 *
	 * @var ML_Subscribers
	 */
	public $subscriber_api = false;

	/**
	 * Holds the MailerLite lists
	 *
	 * @since 2.1.0
	 *
	 * @var array The MailerLite lists
	 */
	protected $lists;

	/**
	 * Primary class constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		// Construct via the parent object.
		parent::__construct();

		// Load the MailerLite API.
		if ( ! class_exists( 'ML_Lists' ) ) {
			require plugin_dir_path( $this->base->file ) . 'includes/vendor/mailerlite/ML_Lists.php';
		}
		if ( ! class_exists( 'ML_Subscribers' ) ) {
			require plugin_dir_path( $this->base->file ) . 'includes/vendor/mailerlite/ML_Subscribers.php';
		}

	}

	/**
	 * Authentication method for providers.
	 *
	 * @since 2.1.0
	 *
	 * @param array $args     Args to be passed for authentication.
	 * @param int   $optin_id The optin ID to target.
	 */
	public function authenticate( $args = array(), $optin_id ) {

		$this->list_api = new ML_Lists( $args['om-api-key'] );

		// Let's make sure we can successfully connect
		$response_body = json_decode( $this->list_api->getAll() );
		$response_info = $this->list_api->getResponseInfo();

		// Return an error if something went wrong
		if ( 200 != $response_info['http_code'] ) {
			return $this->error(
				'api-error',
				__( 'There was an issue connecting to the MailerLite API. Please check your credentials', 'optin-monster' )
			);
		}
		$this->lists = $response_body->Results;

		// Save the account data for future reference.
		$providers                                          = Optin_Monster_Common::get_instance()->get_email_providers( true );
		$uniqid                                             = uniqid();
		$providers[ $this->provider ][ $uniqid ]['api-key'] = trim( $args['om-api-key'] );
		$providers[ $this->provider ][ $uniqid ]['label']   = trim( strip_tags( $args['om-account-label'] ) );
		update_option( 'optin_monster_providers', $providers );

		// Store the account reference in the optin data.
		$this->save_account( $optin_id, $this->provider, $uniqid );

		// Return the lists
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
	 * @return object|string
	 */
	public function get_lists( $args = array(), $list_id = '', $uniqid = '', $optin_id = '' ) {

		// Instantiate the list API if needed
		if ( ! $this->list_api ) {
			$this->list_api = new ML_Lists( $args['api-key'] );
		}

		// Get lists if they aren't available
		if ( ! $this->lists ) {
			$response_body = json_decode( $this->list_api->getAll() );
			$response_info = $this->list_api->getResponseInfo();
			if ( 200 != $response_info['http_code'] ) {
				return $this->error(
					'api-error',
					__( 'There was an issue connecting to the MailerLite API. Please check your credentials', 'optin-monster' )
				);
			}
			$this->lists = $response_body->Results;
		}

		// Now return the list HTML
		return $this->build_list_html( $this->lists, $list_id, $optin_id );

	}

	/**
	 * Method for building out the list selection HTML.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $lists    Lists for the email provider.
	 * @param string $list_id  The currently selected list ID
	 * @param string $optin_id The current optin ID
	 *
	 * @return string $html HTML string for selecting lists.
	 */
	protected function build_list_html( $lists, $list_id = '', $optin_id = '' ) {

		$output = '<div class="optin-monster-field-box optin-monster-provider-lists optin-monster-clear">';
			$output .= '<p class="optin-monster-field-wrap"><label for="optin-monster-provider-list">' . __( 'Email provider list', 'optin-monster' ) . '</label><br />';
				$output .= '<select id="optin-monster-provider-list" name="optin_monster[provider_list]">';
					foreach ( $lists as $list ) {
						$output .= '<option value="' . $list->id . '"' . selected( $list_id, $list->id, false ) . '>' . $list->name . '</option>';
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
	 * @param array $account The API account information
	 * @param int   $list_id The currently selected list ID
	 * @param array $lead    The lead info
	 *
	 * @return bool|object
	 */
	public function optin( $account = array(), $list_id, $lead ) {

		// Instantiate the Subscriber API
		$this->subscriber_api = new ML_Subscribers( $account['api-key'] );

		// Create the initial data array
		$data = array(
			'email'       => $lead['lead_email'],
			'resubscribe' => 1,
		);

		// Setup name if set
		if ( isset( $lead['lead_name'] ) && 'false' != $lead['lead_name'] ) {
			$names = explode( ' ', $lead['lead_name'] );
			if ( isset( $names[0] ) ) {
				$data['name'] = $names[0];
			}
			if ( isset( $names[1] ) ) {
				$data['last_name'] = $names[1];
			}
		}

		$data = apply_filters( 'optin_monster_pre_optin_mailerlite', $data, $lead, $list_id, $this->subscriber_api );

		// Save resubscribe value as it's own variable
		$resubscribe = $data['resubscribe'];
		unset( $data['resubscribe'] );

		// Now send the subscriber data to MailerLite
		try {
			$subscriber    = $this->subscriber_api->setId( $list_id )->add( $data, $resubscribe );
			$response_info = $this->subscriber_api->getResponseInfo();
		} catch ( Exception $e ) {
			return $this->error(
				'list-error',
				__( 'There was an error saving your details.', 'optin-monster' )
			);
		}

		// Return an error if something went wrong while sending the request
		if ( 200 != $response_info['http_code'] ) {
			return $this->error(
				'list-error',
				__( 'There was an issue connecting to the MailerLite API. Please try again.', 'optin-monster' )
			);
		}

		return true;

	}
}