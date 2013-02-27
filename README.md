simplesaml-mobileid
===================

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

Put the certificates and keys into the cert/ directory.

Add the module in the sources `config/authsources.php`:

```
'MobileID' => array(
    'mobileid:Auth',
    'cert_file'    => 'mycert.crt',                       // File containing the certificate for the Mutual Authentication
    'cert_key'     => 'mycert.key',                       // File containing the private key of the related certificate
    'mid_ca'       => 'swisscom-ca.crt',                  // CA bag file for the trust anchor validation of the signature response
    'mid_ocsp'     => 'swisscom-ocsp.crt',                // OCSP bag file for the revocation check of the signature response
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

## Returned elements

### Attributes

`uid`:                  the userid attribute defined at the login window  
`mobile`:               the Mobile ID validated mobile number in international format with 00 as prefix  
`noredupersonnin`:      the `mobile` attribute in the Swisscom SuisseID format 1100-7<mobile> e.g 1100-7417-9208-0350  
`edupersontargetedid `: the persistent anonym ID for the Mobile ID  
`preferredLanguage`:    the language used during the validation process  

### AuthnContext

Returned value is `urn:oasis:names:tc:SAML:2.0:ac:classes:MobileTwoFactorContract`

## Advanced configuration

### Error handling
All errors are handled in the 'lib/Auth/Source/Auth.php' file.

Following errors will throw an exception: 
 'WRONG_PARAM' 
 'MISSING_PARAM' 
 'WRONG_DATA_LENGTH' 
 'INAPPROPRIATE_DATA' 
 'INCOMPATIBLE_INTERFACE' 
 'UNSUPPORTED_PROFILE' 
 'UNAUTHORIZED_ACCESS' 

Following errors will explicitly be indicated to the user and can be translated over the `dictionaries/errors.*.json`files: 
 'UNKNOWN_CLIENT' 
 'EXPIRED_TRANSACTION' 
 'USER_CANCEL' 
 'PIN_NR_BLOCKED' 
 'CARD_BLOCKED' 
 'REVOKED_CERTIFICATE' 

A timeout in the request is mapped to 'EXPIRED_TRANSACTION'. All other errors will be mapped to the 'INTERNAL_ERROR' and logged explicitly.

Refer to the "Mobile ID - SOAP client reference guide" document from Swisscom for more details about error states.

### Message to be signed
Is composed by "'hosturi': {mobileid:Auth:message}". 
Example: "http://serviceprovider.com: Authentication with Mobile ID?"

### Translations
The actual resources are translated in EN, DE, FR, IT. Refer to the files in the `dictionaries/`.
