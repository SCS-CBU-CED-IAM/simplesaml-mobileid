simplesaml-mobileid
===================

Mobile ID custom auth module for simplesamlphp

Refer to project documentation for more details:
 * http://simplesamlphp.org/docs/stable/simplesamlphp-modules
 * http://simplesamlphp.org/docs/stable/simplesamlphp-authsource

## Overview

mobileid:auth is a module for login with Mobile ID. 


## Install

Checkout the project directly from git under the `simplesamlphp/modules` folder:
```
  cd <simplesamlphp>/modules
  git clone https://github.com/SCS-CBU-CED-IAM/simplesaml-mobileid.git mobileid
```

Dependencies:
 * PHP SOAP https://www.php.net/manual/en/soap.setup.php
 * PHP XML Manipulation https://www.php.net/manual/en/dom.setup.php
 * PHP cURL https://www.php.net/manual/en/book.curl.php


Enable the cas module:
  `touch modules/mobileid/enable`


## Configuration

Put the Mobile ID related certificates into the cert/ directory in the PEM format. For proper signature validation the `mid_ca_file` must contain all relevant issuing(s) and root CA certificates. Sample CA files can be found here [doc_cafiles/](doc_cafiles).

The `certkey_file` file must contain both private key and certificate in PEM format (`cat mycert.crt mycert.key > mycertandkey.crt`). Example of content:
````
-----BEGIN CERTIFICATE-----
...
-----END CERTIFICATE-----
-----BEGIN PRIVATE KEY-----
...
-----END PRIVATE KEY-----
````

Add the module in the sources `config/authsources.php`:

```
'MobileID' => array(
    'mobileid:Auth',
    'certkey_file' => 'mycertandkey.pem',                 // File with private key and certificate for the authentication
    'ssl_ca_file'  => 'mobileid-ca-ssl.crt',              // CA file for the HTTPS connection
    'mid_ca_file'  => 'mobileid-ca-signature.crt',        // CA file for the trust anchor validation of the signature response
    'hosturi'      => 'https://myidp.com',                // Host prefix for the message to be signed
    'ap_id'        => '<ID provided by Swisscom>',        // ID of the service provider
    'ap_pwd'       => '<Password provided by Swisscom>',  // Password of the service provider
    ),
```

Optional configuration elements
```
    'default_lang'    => 'en|de|..',                      // Default language of the signature request; en if option not set
    'proxy_host'      => '',                              // e.g. 'my-proxy.com'
    'proxy_port'      => 8080,                            // Only relevant if proxy_host is set
    'proxy_login'     => 'proxyuser',                     // Only relevant if proxy_host is set
    'proxy_password'  => 'pwd',                           // Only relevant if proxy_login is set
    'service_url'     => 'https://...',                   // Mobile ID service URL, e.g. 'https://mobileid.swisscom.com'
    'allowed_mcc'     => array('228', '543'),             // List of allowed Mobile Country Codes, http://www.mcc-mnc.com
    'timeout_conn'    => 45,                              // Connection timeout towards Mobile ID service
    'timeout_req'     => 35,                              // Request timeout for Mobile ID request
```

## Returned elements

### Attributes

* `uid`:                  the userid attribute provided at the login window
* `mobile`:               the Mobile ID number in international format with 00 as prefix
* `preferredLanguage`:    the language used during the validation process
* `userCertificate`:      the Mobile ID user certificate (PEM encoded)
* `serialNumber`:         the SerialNumber of the Distinguished Name (DN) in the related Mobile ID user certificate
* `pseudonym`:            the `mobile` attribute in the Swisscom SuisseID format 1100-7<mobile> e.g 1100-7417-9208-0350
* `mcc`:                  the Mobile Country Code where the Mobile ID is currently connected to e.g 228 for Switzerland
* `mnc`:                  the Mobile Network Code where the Mobile ID is currently connected to e.g 01 for Swisscom



### AuthnContext

Returned value is `urn:oasis:names:tc:SAML:2.0:ac:classes:MobileTwoFactorContract`

## Advanced configuration

### Error handling
All errors are handled in the 'lib/Auth/Source/Auth.php' file.

Following errors will throw an exception:  
````
/* Filter the configuration errors */
$exception_code = array("102", "103", "104", "107", "108", "109");
if (in_array($erroris, $exception_code)) {
  SimpleSAML\Logger::warning('MobileID: error in service call ' . var_export($errortxt, TRUE));
  throw new Exception('MobileID: error in service call ' . var_export($errortxt, TRUE));
}
````

By default all other errors will display the `dictionaries/errors.definition.json:"descr_DEFAULT":` message and can be translated. If for a specific error a custom text should be displayed, it can be added to the dictionaries as `descr_<errorcode>` and must be defined in the `$dico_code`:  
````
/* Filter the dictionaries errors and map the rest to default */
$dico_code = array("101", "105", "208", "209", "401", "402", "403", "404", "406", "422", "501", "503");
if (!in_array($erroris, $dico_code)) {
	$erroris = 'DEFAULT';
	$errortxt = $errortxt . ' mapped to ' . $erroris;
}
````

Refer to the "Mobile ID - SOAP client reference guide" document from Swisscom for more details about error states.

### Message to be signed

The message is composed by "'hosturi': {mobileid:Auth:message} (#TRANSID#)". #TRANSID# is a placeholder that may be used anywhere in the message to include a unique transaction id.

Example: "http://serviceprovider.com: Authentication with Mobile ID? (6GwBOP)"

### Translations

The actual resources are translated in EN, DE, FR, IT. Refer to the files in the `dictionaries/`.

### Theming

The module follows the 'Theming the user interface in SimpleSAMLphp' rules and it can overridden by copying and adjusting the `mobileidtemplate.php` in your own theming module.
