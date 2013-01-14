<?php

/**
 * This page shows a Mobile ID login form, and passes information from it
 * to the sspmod_mobileid_Auth_Source_Auth class
 *
 * @author Swisscom
 * @package midsimplesamlphp
 * @version $Id$
 */

/* Get AuthState and AuthStateID */
if (!array_key_exists('AuthState', $_REQUEST))
    throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
$authStateId = $_REQUEST['AuthState'];

/* Language and message */
$language = '{mobileid:Auth:language}';
$message  = '{mobileid:Auth:message}';

/*if (array_key_exists('midlang', $_REQUEST)) {
    $language = $_REQUEST['midlang'];
    }
else {
    $password = '';
}
if (array_key_exists('middtbs', $_REQUEST)) {
    $message = $_REQUEST['middtbs'];
    }
else {
    $message = '';
}*/

/* MSISDN default value */
if (array_key_exists('msisdn', $_REQUEST))
    $msisdn = $_REQUEST['msisdn'];

/* Try to login */
if (!empty($msisdn))
	$errorCode = sspmod_mobileid_Auth_Source_Auth::handleLogin($authStateId, $msisdn, $language, $message);
else
	$errorCode = NULL;

/* Results */
$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'mobileid:mobileidtemplate.php');
$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['errorcode'] = $errorCode;
//$language = $t::getLanguageCookie();
$t->show();
exit();
    
?>