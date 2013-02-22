swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp

Refer to project documentation for more details:
 * http://simplesamlphp.org/docs/stable/simplesamlphp-modules
 * http://simplesamlphp.org/docs/stable/simplesamlphp-authsource

## Overview

mobileid:auth is a module for login with Mobile ID.


## Install
Checkout the project directly from git under the `simplesamlphp/modules` folder

Enable the cas module:
  `touch modules/mobileid/enable`


## Configuration

Add the module in the sources `config/authsources.php`:

```
'MobileID' => array(
    'mobileid:Auth',
    'cert_file'    => '/opt/mobileid/mycert.crt',         // File containing the certificate for the Mutual Authentication
    'cert_key'     => '/opt/mobileid/mycert.key',         // File containing the private key of the related certificate
    'mid_ca'       => '/opt/mobileid/swisscom-ca.crt',    // CA bag file for the trust anchor validation of the signature response
    'mid_ocsp'     => '/opt/mobileid/swisscom-ocsp.crt',  // OCSP bag file for the revocation check of the signature response
    'hosturi'      => 'https://myidp.com',                // Host prefix for the message to be signed
    'ap_id'        => '<ID provided by Swisscom>',        // ID of the service provider
    'ap_pwd'       => '<Password provided by Swisscom>',  // Password of the service provider
    ),
```

Optional configuration elements
```
    'default_lang'    => 'en|de|..',  // Default language of the signature request
    'remember_msisdn' => true,        // Remember the defined Mobile ID number in a session cookie
    'timeout_ws'      => 90,          // Timeout of the connexion to the Mobile ID service
    'timeout_mid'     => 80,          // Timeout of the Mobile ID request itself
```

## Template/Theming

## Returned elements

* Attributes

`uid`:                  the userid attribute defined at the login window  
`mobile`:               the Mobile ID validated mobile number in international format with 00 as prefix  
`noredupersonnin`:      the `mobile` attribute in the Swisscom SuisseID format 1100-7<mobile> e.g 1100-7417-9208-0350  
`edupersontargetedid `: the persistent anonym ID for the Mobile ID  
`preferredLanguage`:    the language used during the validation process  


* AuthnContext

Returned value is `urn:oasis:names:tc:SAML:2.0:ac:classes:MobileTwoFactorContract`

