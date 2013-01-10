<?php

/*
 * This class implements Mobile ID authentication.
 *
 * @author Freddy Kaiser <kaiser.freddy@gmail.com>
 * @package simpleSAMLphp
 * @version $Id$
 */

define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__.'/mobileid.php');

class sspmod_mobileid_Auth_Source_Auth extends sspmod_core_Auth_UserPassBase {
	/* The mobile id related stuff. */
	private $uid;
    private $msisdn;
    private $language = "en";
	private $msg_en;
    private $msg_de;
    private $msg_fr;
    private $msg_it;
    private $ap_id;
    private $ap_pwd = "disabled";
    private $cert_file;
    private $cert_key;
    private $mid_ca;
    private $mid_ocsp;
    private $mid_timeout_ws;
    private $mid_timeout_mid;

	public function __construct($info, $config) {
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

		if (!is_string($config['msg_en']))
			throw new Exception('MobileID: Missing or invalid msg_en option in config.');
		$this->msg_en = $config['msg_en'];
        
        if (!is_string($config['msg_de']))
			throw new Exception('MobileID: Missing or invalid msg_de option in config.');
		$this->msg_de = $config['msg_de'];
        
		if (!is_string($config['msg_fr']))
			throw new Exception('MobileID: Missing or invalid msg_fr option in config.');
		$this->msg_fr = $config['msg_fr'];
        
		if (!is_string($config['msg_it']))
			throw new Exception('MobileID: Missing or invalid msg_it option in config.');
		$this->msg_it = $config['msg_it'];

        /* Optional options */
        if (is_string($config['default_lang']))
            $this->language = $config['default_lang'];

        if (is_int($config['timeout_ws']))
            $this->mid_timeout_ws = $config['timeout_ws'];
        
        if (is_int($config['timeout_mid']))
            $this->mid_timeout_mid = $config['timeout_mid'];
	}

    /* A helper function for setting the right user id.
     *
     * Ensures international format with 00 and no spaces
     */
    private function getMSISDNfrom($uid) {
        /* Remove all whitespaces */
        $uid = preg_replace('/\s+/', '', $uid);
        /* Replace first + with 00 */
        $uid = str_replace('+', '00', $uid);
        /* Remove all non-digits */
        $uid = preg_replace('/\D/', '', $uid);

        /* Still something here */
        if (strlen($uid) > 5) {
            /* Add implicit 0041 if starting only with one zero */
            if ($uid[0] == '0' && $uid[1] != '0')
                $uid = '0041' . substr($uid, 1);
        }

        return $uid;
    }
    
    /* A helper function for generating a SuisseID number.
     *
     * Based on MSISDN like 0041792080350 we generate a SuisseID conform number
     * 1100-9xxy-yyyy-yyyy where xx is International Prefix and yyy the number itself
     */
    private function getSuisseIDfrom($msisdn) {
        /* Ensure clean format */
        $suisseid = $this->getMSISDNfrom($msisdn);
        
        /* Return empty if not valid US / World number */
        if (strlen($suisseid) != 12 && strlen($suisseid) != 13)
            return '';

        /* Set prefix for non american / american numbers */
        if (substr($suisseid, 0, 2) == '00')         // Non american number
            $suisseid = '1100-9' . substr($suisseid, 2);
        else                                    // -> american number needs one 0 more
            $suisseid = '1100-90' . substr($suisseid, 1);
 
        /* Add - */
        $suisseid = substr($suisseid, 0, 9) . '-' . substr($suisseid, 9, 4) . '-' . substr($suisseid, 13, 4);
        
        return $suisseid;
    }
    
    /* The login function.
     *
     * Cleanup of the username
     * Verification if username has an alias and get the corresponding MSISDN
     * Verification if username has a password and check it
     */
	protected function login($username, $password) {
		/* uid and msisdn defaults to username. */
		$this->uid    = $username;
        $this->msisdn = $this->getMSISDNfrom($username);
        SimpleSAML_Logger::info('MobileID: Login of ' . var_export($this->uid, TRUE) . ' as ' . var_export($this->msisdn, TRUE));
        
        /* Get default language of session/browser */
        $this->language = 'en';
        $this->message = $this->msg_en;

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
            SimpleSAML_Logger::warning('MobileID: error in service call ' . var_export($mobileIdRequest->response_error, TRUE));
            throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }
        
		/* Create the attribute array of the user. */
		$attributes = array(
			'uid' => array($this->uid),
            'mobile' => array($this->msisdn),
            'preferredLanguage' => array($this->language),
            'noredupersonnin' => array($this->getSuisseIDfrom($this->msisdn)),
		);

		/* Return the attributes. */
		return $attributes;
	}
}
?>