<?php
/**
* This page handles the user cancel
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
    
/* Retrieve the authentication state */
$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_mobileid_Auth_Source_Auth::STAGEID);

/* User cancel */
$e = new sspmod_saml_Error(SAML2_Const::STATUS_RESPONDER, 'urn:oasis:names:tc:SAML:2.0:status:AuthnFailed');
SimpleSAML_Auth_State::throwException($state, $e);
?>
