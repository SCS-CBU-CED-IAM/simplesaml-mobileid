<?php

/**
 * This page shows a Mobile ID login form, and passes information from it
 * to the sspmod_mobileid_Auth_Source_Auth class
 *
 * @author Swisscom
 * @package midsimplesamlphp
 * @version $Id$
 */

if (!array_key_exists('AuthState', $_REQUEST))
    throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');

$authStateId = $_REQUEST['AuthState'];

if (array_key_exists('msisdn', $_REQUEST))
    $msisdn = $_REQUEST['msisdn'];
else
    $msisdn = '';

if (!empty($msisdn))                            // Try to login
	$errorCode = sspmod_mobileid_Auth_Source_Auth::handleLogin($authStateId, $otp);
else
	$errorCode = NULL;

$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'mobileid:mobileidtemplate.php');
$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['errorcode'] = $errorCode;
$t->show();
exit();
    
?>