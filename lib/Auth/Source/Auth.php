<?php

/**
 * Mobile ID authentication module, see http://swisscom.com/mobileid
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_mobileid_Auth_Source_Auth extends SimpleSAML_Auth_Source {

    /* The string used to identify our states. */
	const STAGEID = 'sspmod_mobileid_Auth_Source_Auth.state';
    /* The key of the AuthId field in the state. */
	const AUTHID = 'sspmod_mobileid_Auth_Source_Auth.AuthId';

	/* The mobile id related stuff. */
	private $uid;
    private $msisdn;
    private $language = 'en';
    private $message = 'Login with Mobile ID?';
    private $ap_id;
    private $ap_pwd = "disabled";
    private $cert_file;
    private $cert_key;
    private $mid_ca;
    private $mid_ocsp;
    private $mid_timeout_ws;
    private $mid_timeout_mid;

	/**
	 * Constructor for this authentication source.
	 *
	 * @param array $info  Information about this authentication source.
	 * @param array $config  Configuration.
	 */
	public function __construct($info, $config) {
		assert('is_array($info)');
		assert('is_array($config)');

		/* Call the parent constructor first, as required by the interface. */
		parent::__construct($info, $config);

        /* Mandatory options */
        if (!is_string($config['ap_id']))
			throw new Exception('MobileID: Missing or invalid ap_id option in config.');
		$this->ap_id = $config['ap_id'];
        
        if (!is_string($config['ap_pwd']))
			throw new Exception('MobileID: Missing or invalid ap_pwd option in config.');
		$this->ap_pwd = $config['ap_pwd'];
        
        if (!is_string($config['cert_file']))
			throw new Exception('MobileID: Missing or invalid cert_file option in config.');
		$this->cert_file = $config['cert_file'];
        
        if (!is_string($config['cert_key']))
			throw new Exception('MobileID: Missing or invalid cert_key option in config.');
		$this->cert_key = $config['cert_key'];
        
        if (!is_string($config['mid_ca']))
			throw new Exception('MobileID: Missing or invalid mid_ca option in config.');
		$this->mid_ca = $config['mid_ca'];
        
        if (!is_string($config['mid_ocsp']))
			throw new Exception('MobileID: Missing or invalid mid_ocsp option in config.');
		$this->mid_ocsp = $config['mid_ocsp'];
                
        /* Optional options */
        if (is_string($config['default_lang']))
            $this->language = $config['default_lang'];
        
        if (is_int($config['timeout_ws']))
            $this->mid_timeout_ws = $config['timeout_ws'];
        
        if (is_int($config['timeout_mid']))
            $this->mid_timeout_mid = $config['timeout_mid'];
	}

	/**
	 * Initialize login.
	 *
	 * This function saves the information about the login, and redirects to a login page.
	 *
	 * @param array &$state  Information about the current authentication.
	 */
	public function authenticate(&$state) {
		assert('is_array($state)');

		/* We are going to need the authId in order to retrieve this authentication source later. */
		$state[self::AUTHID] = $this->authId;
		$id = SimpleSAML_Auth_State::saveState($state, self::STAGEID);

		$url = SimpleSAML_Module::getModuleURL('mobileid/mobileidlogin.php');
		SimpleSAML_Utilities::redirect($url, array('AuthState' => $id));
	}
	
	/**
	 * Handle login request.
	 *
	 * This function is used by the login form when the users a Mobile ID number.
	 * On success, it will not return. On Mobile ID failure, it will return the error code.
     * Other failures will throw an exception.
	 *
	 * @param string $authStateId  The identifier of the authentication state.
	 * @param string $msisdn  The Mobile ID entered.
     * @param string $language  The language of the communication.
     * @param string $message  The message to be communicated.
	 * @return string  Error code in the case of an error.
	 */
	public static function handleLogin($authStateId, $msisdn, $language, $message) {
		assert('is_string($authStateId)');
		assert('is_string($msisdn)');
        assert('is_string($language)');
        assert('is_string($message)');

		/* Retrieve the authentication state. */
		$state = SimpleSAML_Auth_State::loadState($authStateId, self::STAGEID);

		/* Find authentication source. */
		assert('array_key_exists(self::AUTHID, $state)');
		$source = SimpleSAML_Auth_Source::getById($state[self::AUTHID]);
		if ($source === NULL) {
			throw new Exception('Could not find authentication source with id ' . $state[self::AUTHID]);
		}
        
		/* $source now contains the authentication source on which authenticate() was called
		 * We should call login() on the same authentication source.
		 */
		try {
			/* Attempt to log in. */
            $self->language = $language;
            $self->message = $message;
			$attributes = $source->login($msisdn);
		} catch (SimpleSAML_Error_Error $e) {
            /* Login failed. Return the error code to the login form */
			return $e->getErrorCode();
		}
        
		/* Save the attributes we received from the login-function in the $state-array. */
		assert('is_array($attributes)');
		$state['Attributes'] = $attributes;
        
        /* Set the AuthnContext */
        $state['saml:AuthnContextClassRef'] = 'urn:oasis:names:tc:SAML:2.0:ac:classes:MobileTwoFactorContract';
        
        /* Return control to simpleSAMLphp after successful authentication. */
		SimpleSAML_Auth_Source::completeAuth($state);
	}
    
    /* A helper function for setting the right user id.
     *
     * Ensures international format with specified prefix (+ or 00) and no spaces
     */
    private function getMSISDNfrom($uid, $prefix = '00') {
        $uid = preg_replace('/\s+/', '', $uid);         // Remove all whitespaces
        $uid = str_replace('+', '00', $uid);            // Replace all + with 00
        $uid = preg_replace('/\D/', '', $uid);          // Remove all non-digits
        if (strlen($uid) > 5) {                         // Still something here */
            if ($uid[0] == '0' && $uid[1] != '0')           // Add implicit 41 if starting with one 0
                $uid = '41' . substr($uid, 1);
            $uid = ltrim($uid, '0');                        // Remove all leading 0
        }
        $uid = $prefix . $uid;                           // Add the defined prefix
        
        return $uid;
    }
    
    /* A helper function for generating a SuisseID number.
     *
     * Based on MSISDN like 0041792080350 we generate a SuisseID conform number
     * 1100-9xxy-yyyy-yyyy where xx is International Prefix and yyy the number itself
     */
    private function getSuisseIDfrom($msisdn) {
        /* Ensure clean format */
        $suisseid = $this->getMSISDNfrom($msisdn, '00');
        
        /* Return empty if not valid US / World number */
        if (strlen($suisseid) != 12 && strlen($suisseid) != 13) return '';
        
        /* Set prefix for non american / american numbers */
        if (substr($suisseid, 0, 2) == '00')            // Non american number
            $suisseid = '1100-9' . substr($suisseid, 2);
        else                                            // -> american number needs one 0 more
            $suisseid = '1100-90' . substr($suisseid, 1);
        
        /* Add - */
        $suisseid = substr($suisseid, 0, 9) . '-' . substr($suisseid, 9, 4) . '-' . substr($suisseid, 13, 4);
        
        return $suisseid;
    }
    
    /* The login function.
     *
	 * @param string $msisdn  The Mobile ID entered.
	 * @return string  Attributes.
     */
	protected function login($username) {
		assert('is_string($username)');
        
		require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/libextinc/mobileid.php';
		$attributes = array();

		/* uid and msisdn defaults to username. */
		$this->uid    = $username;
        $this->msisdn = $this->getMSISDNfrom($username, '+');
        SimpleSAML_Logger::info('MobileID: Login of ' . var_export($this->uid, TRUE) . ' as ' . var_export($this->msisdn, TRUE));
        SimpleSAML_Logger::info('MobileID:   Message ' . var_export($this->message, TRUE) . ' in ' . var_export($this->language, TRUE));
        
        /* New instance of the Mobile ID class */
        $mobileIdRequest = new mobileid($this->ap_id, $this->ap_pwd);
        $mobileIdRequest->cert_file = $this->cert_file;
        $mobileIdRequest->cert_key  = $this->cert_key;
        $mobileIdRequest->cert_ca   = $this->mid_ca;
        $mobileIdRequest->ocsp_cert = $this->mid_ocsp;
        if ($this->mid_timeout_mid) $mobileIdRequest->TimeOutMIDRequest = (int)$this->mid_timeout_mid;
        if ($this->mid_timeout_ws)	$mobileIdRequest->TimeOutWSRequest = (int)$this->mid_timeout_ws;
        
        /* Call Mobile ID */
        $mobileIdRequest->sendRequest($this->msisdn, $this->language, $this->message);
        if ($mobileIdRequest->response_error) {
            SimpleSAML_Logger::warning('MobileID: error in service call ' . var_export($mobileIdRequest->response_status_message, TRUE));
            /* Define the error and filter the valid ones for dictionnaries */
            $erroris = $mobileIdRequest->response_status_message;
            switch($erroris) {
                case 'WRONG_PARAM';
                case 'MISSING_PARAM';
                case 'WRONG_DATA_LENGTH';
                case 'UNAUTHORIZED_ACCESS';
                case 'UNKNOWN_CLIENT';
                case 'INAPPROPRIATE_DATA';
                case 'INCOMPATIBLE_INTERFACE';
                case 'UNSUPPORTED_PROFILE';
                case 'EXPIRED_TRANSACTION';
                case 'OTA_ERROR';
                case 'USER_CANCEL';
                case 'PIN_NR_BLOCKED';
                case 'CARD_BLOCKED';
                case 'NO_KEY_FOUND';
                case 'PB_SIGNATURE_PROCESS';
                case 'NO_CERT_FOUND';
                case 'CRL_PB';
                case 'CRL_EXPIRED';
                case 'REVOKED_CERTIFICATE';
                case 'INVALID_SIGNATURE';
                case 'INTERNAL_ERROR';
                    break;
                // All other errors are mapped to INTERNAL_ERROR
                default:
                    $erroris = 'INTERNAL_ERROR';
                    break;
            }
            /* Special handling for timeout */
            if ($mobileIdRequest->response_soap_fault_subcode === '208')
                $erroris = 'EXPIRED_TRANSACTION';
            
            throw new SimpleSAML_Error_Error($erroris);
        }

        /* Create the attribute array of the user. */
        $attributes = array(
            'uid' => array($this->uid),
            'mobile' => array($this->getMSISDNfrom($this->msisdn, '00')),
            'preferredLanguage' => array($this->language),
            'noredupersonnin' => array($this->getSuisseIDfrom($this->msisdn)),
        );
        
        /* Return the attributes. */
        return $attributes;
	}    
}

?>
