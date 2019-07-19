<?php
/**
* This page handles the user cancel
*
* @version     1.0.4
* @package     simpleSAMLphp-mobileid
* @copyright   Copyright (C) 2012. All rights reserved.
* @license     Licensed under the Apache License, Version 2.0 or later; see LICENSE.md
* @author      Swisscom (Schweiz) AG
*/
    
/* Get AuthState and AuthStateID */
if (!array_key_exists('AuthState', $_REQUEST))
    throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
    
$authStateId = $_REQUEST['AuthState'];
    
/* Retrieve the authentication state */
$state = SimpleSAML\Auth\State::loadState($authStateId, sspmod_mobileid_Auth_Source_Auth::STAGEID);

/* User cancel */
$e = new \SimpleSAML\Error\UserAborted();
SimpleSAML\Auth\State::throwException($state, $e);
?>
