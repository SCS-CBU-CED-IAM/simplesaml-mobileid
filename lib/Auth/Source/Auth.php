<?php

/*
 * This class implements Mobile ID authentication with optional userid aliasing and password validation
 * against an SQL database.
 *
 * @author Freddy Kaiser <kaiser.freddy@gmail.com>
 * @package simpleSAMLphp
 * @version $Id$
 */

define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__.'/mobileid.php');

class sspmod_mobileid_Auth_Source_Auth extends sspmod_core_Auth_UserPassBase {

	/* The database DSN.
	 * See the documentation for the various database drivers for information about the syntax:
	 *     http://www.php.net/manual/en/pdo.drivers.php
	 */
	private $dsn;

	/* The database username & password. */
	private $dbusername;
	private $dbpassword;

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
		if (!is_string($config['dsn']))
			throw new Exception('MobileID: Missing or invalid dsn option in config.');
		$this->dsn = $config['dsn'];

		if (!is_string($config['username']))
			throw new Exception('MobileID: Missing or invalid username option in config.');
		$this->dbusername = $config['username'];

		if (!is_string($config['password']))
			throw new Exception('MobileID: Missing or invalid password option in config.');
		$this->dbpassword = $config['password'];

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
        
        if (!is_string($config['ap_id']))
			throw new Exception('MobileID: Missing or invalid ap_id option in config.');
		$this->ap_id = $config['ap_id'];

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
        if (is_string($config['ap_pwd']))
            $this->ap_id = $config['ap_pwd'];

        if (is_string($config['default_lang']))
            $this->language = $config['default_lang'];

        if (is_int($config['timeout_ws']))
            $this->mid_timeout_ws = $config['timeout_ws'];
        
        if (is_int($config['timeout_mid']))
            $this->mid_timeout_mid = $config['timeout_mid'];
	}

    /* A helper function for setting the right user id.
     *
     * Ensures international format +99 without spaces
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
            /* Add implicit +41 if starting only with one zero */
            if ($uid[0] == '0' && $uid[1] != '0')
                $uid = '+41' . substr($uid, 1);
            /* Replace 00 with + */
            if ($uid[0] == '0' && $uid[1] == '0' && $uid[2] != '0')
                $uid = '+' . substr($uid, 2);
        }

        return $uid;
    }
    
    /* A helper function for generating a SuisseID number.
     *
     * Based on MSISDN like +41792080350 we generate a SuisseID conform number
     * 1100-9xxy-yyyy-yyyy where xx is International Prefix and yyy the number itself
     */
    private function getSuisseIDfrom($msisdn) {
        /* Ensure clean format */
        $suisseid = $this->getMSISDNfrom($msisdn);
        
        /* Return empty if not starting with + */
        if (strlen($suisseid) == 0 || $suisseid[0] != '+')
            return '';
        /* Return empty if not valid US / World number */
        if (strlen($suisseid) != 11 && strlen($suisseid) != 12)
            return '';

        /* Set prefix for american number */
        $suisseid = str_replace('+1', '1100-901', $suisseid);
        /* Set prefix for non american numbers */
        $suisseid = str_replace('+', '1100-9', $suisseid);
        /* Add - */
        $suisseid = substr($suisseid, 0, 9) . '-' . substr($suisseid, 9, 4) . '-' . substr($suisseid, 13, 4);
        
        return $suisseid;
    }
    
	/* A helper function for validating a password hash.
	 *
	 * In this example we check a SSHA-password, where the database
	 * contains a base64 encoded byte string, where the first 20 bytes
	 * from the byte string is the SHA1 sum, and the remaining bytes is
	 * the salt.
	 */
	private function checkPassword($passwordHash, $password) {
		$passwordHash = base64_decode($passwordHash);
		$digest = substr($passwordHash, 0, 20);
		$salt = substr($passwordHash, 20);

		$checkDigest = sha1($password . $salt, TRUE);
		return $digest === $checkDigest;
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
        
		/* Connect to the database. */
		$db = new PDO($this->dsn, $this->dbusername, $this->dbpassword);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		/* With PDO we use prepared statements. This saves us from having to escape the username in the database query. */
		$st = $db->prepare('SELECT id, msisdn, pwd, mail FROM miduser WHERE id=:username');
		if (!$st->execute(array('username' => $this->uid))) {
			throw new Exception('MobileID: Failed to query database for mobile id user.');
		}
        
		/* Retrieve the row from the database. */
		$row = $st->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			/* User alias found, get the related msisdn. */
			$this->msisdn = $row['msisdn'];
            SimpleSAML_Logger::info('MobileID: Alias found for ' . var_export($this->uid, TRUE) . ' with msisdn ' . var_export($this->msisdn, TRUE));

			/* Password not empty, check the password. */
            if ($password && !$this->checkPassword($row['password_hash'], $password)) {
                    /* Invalid password. */
                    SimpleSAML_Logger::warning('MobileID: Wrong password for user ' . var_export($this->uid, TRUE) . '.');
                    throw new SimpleSAML_Error_Error('WRONGUSERPASS');
                }
        }
        
        /* Get default language of session/browser */
        $this->language = 'en';
        $this->message = $this->msg_en;

        /* New instance of the Mobile ID class */
        $mobileIdRequest = new mobileid($this->ap_id, $this->ap_pwd);
        $mobileIdRequest->cert_file = $this->cert_file;
        $mobileIdRequest->cert_key  = $this->cert_key;
        $mobileIdRequest->cert_ca   = $this->cert_ca;
        if ($this->mid_timeout_mid)
			$mobileIdRequest->TimeOutMIDRequest = (int)$this->mid_timeout_mid;
        if ($this->mid_timeout_ws)
			$mobileIdRequest->TimeOutWSRequest = (int)$this->mid_timeout_ws;
        /* Call Mobile ID */
        $mobileIdRequest->sendRequest($this->msisdn, $this->language, $this->message);
   var_dump($mobileIdRequest);
        if ($mobileIdRequest->response_error) {
            SimpleSAML_Logger::warning('MobileID: error in call ' . var_export($mobileIdRequest->response_error, TRUE));
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