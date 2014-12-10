<?php
/**
 * This page shows a Mobile ID login form, and passes information from it
 * to the sspmod_mobileid_Auth_Source_Auth class
 *
 * @version     1.0.0
 * @package     simpleSAMLphp-mobileid
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

/* Get AuthState and AuthStateID */
if (!array_key_exists('AuthState', $_REQUEST))
    throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');

$authStateId = $_REQUEST['AuthState'];

/* MSISDN default value */
if (array_key_exists('msisdn', $_REQUEST)) {
    $msisdn = $_REQUEST['msisdn'];
}

/* Retrieve the authentication state. */
$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_mobileid_Auth_Source_Auth::STAGEID);

/* Remember the mobile number */
if (isset($state['remember_msisdn']) && isset($msisdn)) {
    if ($state['remember_msisdn']) {    // Config is set and true
        setCookies($msisdn);
    } else {                            // Config is set but false
        removeCookies();
    }
}

// Config is not set
if (!isset($state['remember_msisdn'])) {
    removeCookies();
}
    
/* Login and results */
$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'mobileid:mobileidtemplate.php');

/* Try to login */
$language = $t->t('{mobileid:Auth:language}');
$message = $t->t('{mobileid:Auth:message}');
if (!empty($msisdn))
    $errorCode = sspmod_mobileid_Auth_Source_Auth::handleLogin($authStateId, $msisdn, $language, $message);
else
    $errorCode = NULL;

/* Results */
$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['errorcode'] = $errorCode;
$t->show();
exit();
  
function setCookies($msisdn) {
    $sessionHandler = SimpleSAML_SessionHandler::getSessionHandler();
    $params = $sessionHandler->getCookieParams();
    $params['expire']  = time();
    $params['expire'] += 31536000;
    $_COOKIE['msisdn'] = $msisdn;
    setcookie('msisdn', $msisdn, $params['expire'], $params['path'], $params['domain'], $params['secure'], $params['httponly']);    
}

function removeCookies() {
    if (isset($_COOKIE['msisdn'])) {
        unset($_COOKIE['msisdn']);
        setcookie('msisdn', '', time()-3600);
    }   
}
?>
