<?php
/**
 * @version     1.0.5
 * @package     simpleSAMLphp-mobileid
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     Licensed under the Apache License, Version 2.0 or later; see LICENSE.md
 * @author      Swisscom (Schweiz) AG
 */

class sspmod_mobileid_Auth_Source_Auth extends SimpleSAML\Auth\Source {

    /* The string used to identify our states. */
    const STAGEID = 'sspmod_mobileid_Auth_Source_Auth.state';
    /* The key of the AuthId field in the state. */
    const AUTHID = 'sspmod_mobileid_Auth_Source_Auth.AuthId';

    /* The mobile id related stuff. */
    private $hosturi;
    private $uid;
    private $msisdn;
    private $language = 'en';
    private $message = 'Authentication with Mobile ID? (#TRANSID#)';
    private $mcc = '';				// Mobile Country Code
    private $mnc = '';				// Mobile Network Code
    private $ap_id;
    private $ap_pwd = 'disabled';
    private $certkey_file;
    private $ssl_ca_file;
    private $mid_ca_file;
    private $proxy_host = '';
    private $proxy_port;
    private $proxy_login;
    private $proxy_password;
    private $service_url = '';
    private $allowed_mcc = array();
    private $timeout_conn;
    private $timeout_req;

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

        $globalConfig = SimpleSAML\Configuration::getInstance();
        $certdir = $globalConfig->getPathValue('certdir', 'cert/');

        /* Mandatory options */
        if (!isset($config['hosturi']))
            throw new Exception('MobileID: Missing or invalid hosturi option in config.');
        $this->hosturi = $config['hosturi'];

        if (!isset($config['ap_id']))
            throw new Exception('MobileID: Missing or invalid ap_id option in config.');
        $this->ap_id = $config['ap_id'];
        
        if (!isset($config['ap_pwd']))
            throw new Exception('MobileID: Missing or invalid ap_pwd option in config.');
        $this->ap_pwd = $config['ap_pwd'];

        if (!isset($config['certkey_file']))
            throw new Exception('MobileID: Missing or invalid certkey_file option in config.');
        $this->certkey_file = SimpleSAML\Utilities::resolvePath($config['certkey_file'], $certdir);
        if (!file_exists($this->certkey_file))
            throw new Exception('MobileID: Missing or invalid certkey_file option in config: ' . $this->certkey_file);

        if (!isset($config['ssl_ca_file']))
            throw new Exception('MobileID: Missing or invalid ssl_ca_file option in config.');
        $this->ssl_ca_file = SimpleSAML\Utilities::resolvePath($config['ssl_ca_file'], $certdir);
        if( !file_exists($this->ssl_ca_file))
            throw new Exception('MobileID: Missing or invalid ssl_ca_file option in config: ' . $this->ssl_ca_file);
        
        if (!isset($config['mid_ca_file']))
            throw new Exception('MobileID: Missing or invalid mid_ca_file option in config.');
        $this->mid_ca_file = SimpleSAML\Utilities::resolvePath($config['mid_ca_file'], $certdir);
        if (!file_exists($this->mid_ca_file))
            throw new Exception('MobileID: Missing or invalid mid_ca_file option in config: ' . $this->mid_ca_file);
                
        /* Optional options */
        if (isset($config['default_lang']))
            $this->language = $config['default_lang'];
        
        if (isset($config['proxy_host'])) {
            $this->proxy_host = $config['proxy_host'];
            if (isset($config['proxy_port']))
                $this->proxy_port = $config['proxy_port'];
            if (isset($config['proxy_login'])) {
                $this->proxy_login = $config['proxy_login'];
                if (isset($config['proxy_password']))
                    $this->proxy_password = $config['proxy_password'];
            }
        }

        if (isset($config['service_url']))
            $this->service_url = $config['service_url'];

        if (isset($config['allowed_mcc'])) {
            if (!is_array($config['allowed_mcc']))
                throw new Exception('MobileID: allowed_mcc is not an array() in config: ' . $this->mid_ca_file);
            $this->allowed_mcc = $config['allowed_mcc'];
        }

        if (isset($config['timeout_conn']) && is_numeric($config['timeout_conn']))
            $this->timeout_conn = $config['timeout_conn'];
        if (isset($config['timeout_req']) && is_numeric($config['timeout_req']))
            $this->timeout_req = $config['timeout_req'];
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
        $id = SimpleSAML\Auth\State::saveState($state, self::STAGEID);

        $url = SimpleSAML\Module::getModuleURL('mobileid/mobileidlogin.php');
        SimpleSAML\Utilities::redirect($url, array('AuthState' => $id));
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
        $state = SimpleSAML\Auth\State::loadState($authStateId, self::STAGEID);

        /* Find authentication source. */
        assert('array_key_exists(self::AUTHID, $state)');
        $source = SimpleSAML\Auth\Source::getById($state[self::AUTHID]);
        if ($source === NULL) {
            throw new Exception('Could not find authentication source with id ' . $state[self::AUTHID]);
        }

        /* $source now contains the authentication source on which authenticate() was called
         * We should call login() on the same authentication source.
         */
        try {
            /* Attempt to log in. */
            $attributes = $source->login($msisdn, $language, $message);
        } catch (SimpleSAML\Error\Error $e) {
            /* Get the error and parameters */
            $error = $e->getErrorCode();
            $params = $e->getParameters();
            /* Add the Mobile ID StatusCode separated by a tag */
            $error .= '##' . $params['StatusCode'];
            /* Add the UserAssistanceURL separated by a tag */
            $error .= '##' . $params['UserAssistanceURL'];
            /* Add the mcc separated by a tag */
            $error .= '##' . $params['mcc'];
            /* Add the mnc separated by a tag */
            $error .= '##' . $params['mnc'];

            /* Login failed. Return the error to the login form */
            return $error;
        }
        
        /* Save the attributes we received from the login-function in the $state-array. */
        assert('is_array($attributes)');
        $state['Attributes'] = $attributes;
        
        /* Set the AuthnContext */
        $state['saml:AuthnContextClassRef'] = 'urn:oasis:names:tc:SAML:2.0:ac:classes:MobileTwoFactorContract';
        
        /* Return control to simpleSAMLphp after successful authentication. */
        SimpleSAML\Auth\Source::completeAuth($state);
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
     * for Switzerland and Lichtenstein only. For the others an empty string will be returned.
     */
    private function getSuisseIDfrom($msisdn) {
        /* Ensure clean format */
        $msisdn = $this->getMSISDNfrom($msisdn, '00');
        
        /* Country based whitelisting */
        if (substr($msisdn, 0, 4) == '0041') {          // Switzerland
            $msisdn = str_pad(substr($msisdn, 4), 9, "0", STR_PAD_LEFT);
            $suisseid = '1100741' . $msisdn;
            }
        elseif (substr($msisdn, 0, 5) == '00423') {     // Lichtenstein
            $msisdn = str_pad(substr($msisdn, 5), 8, "0", STR_PAD_LEFT);
            $suisseid = '11007423' . $msisdn;
            }
        else return('');                                // Blacklisted

        /* Check valid number */
        if (strlen($suisseid) != 16) return('');

        /* Add - */
        $suisseid = substr($suisseid, 0, 4) . '-' . substr($suisseid, 4, 4) . '-' . substr($suisseid, 8, 4) . '-' . substr($suisseid, 12, 4);
        
        return $suisseid;
    }
    
    /* A helper function for generating a unique Transaction ID string.
     *
     * @return string  Transaction ID with a length of 6
     */
    private function generateTransactionID() {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxlen = strlen($pattern) - 1;

        $id = '';
        for ($i = 1; $i <= 6; $i++)
            $id .= $pattern{mt_rand(0, $maxlen)};

        return $id;
    }

    /* The login function.
     *
     * @param string $msisdn  The Mobile ID entered.
     * @return string  Attributes.
     */
    protected function login($username, $language, $message) {
        assert('is_string($username)');
        assert('is_string($language)');
        assert('is_string($message)');
        
        require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/libextinc/mobileid.php';
        $attributes = array();

        /* Language and Message with unique transation ID. */
        $this->language = $language;
        $this->message  = $this->hosturi . ': ' . $message;
        $transid = $this->generateTransactionID();
        $this->message = str_replace('#TRANSID#', $transid, $this->message);

        /* uid and msisdn defaults to username. */
        $this->uid    = $username;
        $this->msisdn = $this->getMSISDNfrom($username, '+');
        SimpleSAML\Logger::info('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . ' uid=' . var_export($this->uid, TRUE) . ' msg=' . var_export($this->message, TRUE) . ' lang=' . var_export($this->language, TRUE));

        /* Mobile ID class options */
        $myoptions = array();
        if (isset($this->proxy_host) && (string)$this->proxy_host != '') {
            $myoptions['proxy_host'] = $this->proxy_host;
            if (isset($this->proxy_port))
                $myoptions['proxy_port'] = $this->proxy_port;
            if (isset($this->proxy_login)) {
                $myoptions['proxy_login'] = $this->proxy_login;
                if (isset($this->proxy_password))
                    $myoptions['proxy_password'] = $this->proxy_password;
            }
        }
        if (isset($this->timeout_conn)) {
            $myoptions['connection_timeout'] = $this->timeout_conn;
        }
        /* New instance of the Mobile ID class */
        SimpleSAML\Logger::debug('MobileID: class=' . var_export($this, TRUE));        
        SimpleSAML\Logger::debug('MobileID: options=' . var_export($myoptions, TRUE));
        $mobileID = new mobileid($this->ap_id, $this->ap_pwd, $this->certkey_file, $this->ssl_ca_file, $myoptions, $this->timeout_req);
        /* Handle special options */
        if (isset($this->service_url) && (string)$this->service_url != '') {
            $mobileID->setBaseURL($this->service_url);
        }

        /* Call Mobile ID Signature Request */
        $status = $mobileID->signature($this->msisdn, $this->message, $this->language, $this->mid_ca_file);
        SimpleSAML\Logger::debug('MobileID: service=' . var_export($mobileID, TRUE));

        if (! $status ) {
            /* Get error code and detail */
            $logtype = 'warning';
            $erroris = $mobileID->statuscode;
            $errortxt = $erroris . ' -> ' . $mobileID->statusmessage;
            if (strlen($mobileID->statusdetail))
                $errortxt = $errortxt . ' (' . $mobileID->statusdetail . ')';

            /* Remove the mss:_ prefix in the error code if present */
            $erroris = preg_replace('/^mss:_/', '', $erroris);

            /* Filter the configuration errors */
            $exception_code = array("102", "103", "104", "107", "108", "109");
            if (in_array($erroris, $exception_code)) {
                SimpleSAML\Logger::alert('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . ' error in service call ' . var_export($errortxt, TRUE));
                throw new Exception('MobileID: error in service call ' . var_export($errortxt, TRUE));
            }
 
            /* Special handling on user cancel */
            if ($erroris == "401") {
                $logtype = 'info';
            }
            /* Special handling on HTTP error */
            if ($erroris == 'HTTP') {
                $logtype = 'alert';
            }
 
            /* Filter the dictionaries errors and map the rest to default */
            $dico_code = array("101", "105", "208", "209", "401", "402", "403", "404", "406", "422", "501", "503");
            if (!in_array($erroris, $dico_code)) {
                $erroris = 'DEFAULT';
                $errortxt = $errortxt . ' mapped to ' . $erroris;
            }

            /* Log the details */
            if ($logtype == 'info') {
                SimpleSAML\Logger::info('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . var_export($errortxt, TRUE));
            }
            if ($logtype == 'warning') {
                SimpleSAML\Logger::warning('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . ' error in service call ' . var_export($errortxt, TRUE));
            }
            if ($logtype == 'alert') {
                SimpleSAML\Logger::alert('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . ' error in service call ' . var_export($errortxt, TRUE));
            }

            /* Define the error as array to pass specific parameters beside the proper error code */
            $error = array(
                'UNHANDLEDEXCEPTION',
                'StatusCode' => $erroris,
                'UserAssistanceURL' => $mobileID->getUserAssistance('Mobile ID', true),
                'mcc' => '',
                'mnc' => ''
            );

            /* Set the error */
            throw new SimpleSAML\Error\Error($error);
        }

        /* Get the Subscriber Info 1901 (MCC/MNC) */
        $mccmnc = $mobileID->getSubscriberInfo('1901');
        if ($mccmnc != '' && $mccmnc != 'unknown') {
            $this->mcc = substr($mccmnc, 0, 3);
            $this->mnc = substr($mccmnc, 3, 3);
        } else {
            $this->mcc = '000';
            $this->mnc = '00';
        }

        /* Log the MID details */
        SimpleSAML\Logger::info('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . ' serialNumber=' . var_export($mobileID->mid_serialnumber, TRUE) . ' mcc=' . var_export($this->mcc, TRUE) . ' mnc=' . var_export($this->mnc, TRUE));

        /* Allowed MCC in the config ? */
        if (count($this->allowed_mcc) > 0) {
            if (!in_array($this->mcc, $this->allowed_mcc)) {
                /* Log the details */
                SimpleSAML\Logger::warning('MobileID: msisdn=' . var_export($this->msisdn, TRUE) . 'mcc=' . var_export($this->mcc, TRUE) . ' not in the allowed_mcc list');

                /* Define the error as array to pass specific parameters beside the proper error code */
                $error = array(
                    'UNHANDLEDEXCEPTION',
                    'StatusCode' => 'MCC',
                    'UserAssistanceURL' => '',
                    'mcc' => $this->mcc,
                    'mnc' => $this->mnc
                );

                /* Set the error */
                throw new SimpleSAML\Error\Error($error);
            }
        }

        /* Create the attribute array of the user. */
        $attributes = array(
            'uid'                => array($this->uid),
            'mobile'             => array($this->getMSISDNfrom($this->msisdn, '00')),
            'pseudonym'          => array($this->getSuisseIDfrom($this->msisdn)),
            'serialNumber'       => array($mobileID->mid_serialnumber),
            'preferredLanguage'  => array($this->language),
            'userCertificate'    => array($mobileID->mid_certificate),
            'mcc'                => array($this->mcc),
            'mnc'                => array($this->mnc),
        );
        
        /* Return the attributes. */
        return $attributes;
    }    
}

?>
