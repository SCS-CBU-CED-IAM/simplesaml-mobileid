<?php
/**
 * This page shows a Mobile ID login form, and passes information from it
 * to the sspmod_mobileid_Auth_Source_Auth class
 *
 * @version     1.0.5
 * @package     simpleSAMLphp-mobileid
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     Licensed under the Apache License, Version 2.0 or later; see LICENSE.md
 * @author      Swisscom (Schweiz) AG
 */

/* Get AuthState and AuthStateID */
if (!array_key_exists('AuthState', $_REQUEST))
    throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');

       
$authStateId = $_REQUEST['AuthState'];

/* Get the mobile number from Request */
if (array_key_exists('msisdn', $_POST)) {
    $msisdn = $_POST['msisdn'];
}
/* Retrieve the authentication state. */
$state = SimpleSAML\Auth\State::loadState($authStateId, sspmod_mobileid_Auth_Source_Auth::STAGEID);
    
/* Login and results */
$globalConfig = SimpleSAML\Configuration::getInstance();
$t = new SimpleSAML\XHTML\Template($globalConfig, 'mobileid:mobileidtemplate.php');

/* Try to login */
$language = $t->t('{mobileid:Auth:language}');
$message = $t->t('{mobileid:Auth:message}');
$errorCode = NULL;
$errorURL = NULL;
$errorDescr = NULL;
$mcc = NULL;
$mnc = NULL;
if (!empty($msisdn)) {
    setCookies($msisdn);
    $errorCode = sspmod_mobileid_Auth_Source_Auth::handleLogin($authStateId, $msisdn, $language, $message);

    /* Explode the error into array */
    $error = explode("##", $errorCode);
    if (array_key_exists(1, $error))
        $errorCode = $error[1];
    if (array_key_exists(2, $error))
        $errorURL = $error[2];
    if (array_key_exists(3, $error))
        $mcc = $error[3];
    if (array_key_exists(4, $error))
        $mnc = $error[4];
}

/* Results */
$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['errorcode'] = $errorCode;
$t->data['errorurl'] = $errorURL;
$t->data['mcc'] = $mcc;
$t->data['mnc'] = $mnc;
$t->show();
exit();
  
function setCookies($msisdn) {
    $sessionHandler = SimpleSAML\SessionHandler::getSessionHandler();
    $params = $sessionHandler->getCookieParams();
    $params['expire']  = 0;
    setcookie('msisdn', $msisdn, $params['expire'], $params['path'], $params['domain'], $params['secure'], $params['httponly']);    
    $_COOKIE['msisdn'] = $msisdn;
}
?>
