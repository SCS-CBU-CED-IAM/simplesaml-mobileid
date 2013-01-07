swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp

Refer to http://simplesamlphp.org/docs/stable/simplesamlphp-modules for details.

## Overview


Call with or without MSISDN Number

1) Without

All done @IDP by asking: ID / Password
  ID: can be anything from MSIDN to eMail
    Works as "search for alias" in the User Store
  Password: optional; if present needs to be validated and correct

  OK: Submit
  Cancel: replies to the SP with a "cancel"

2) With

Reads out the MSIDN from: ?

Enforces the ID field from the "Without" and no password
  Sends the "Ok"
  On Password error asks about the password (Optional Password validation)


## Install
Checkout directly from git under the simplesamlphp modules folder with git clone <git> mobileid

Enable the cas module:
  `touch modules/mobileid/default-enabled`

Apache: nothing special ?

Create the database and the related miduser table:

`CREATE DATABASE mobileid;`
`USE mobileid;`
`CREATE TABLE miduser (
        id VARCHAR(32) PRIMARY KEY NOT NULL,
        pwd VARCHAR(64),
        msisdn TEXT NOT NULL);`

`CREATE USER 'dbuser'@'localhost' IDENTIFIED BY 'dbpwd';`
`GRANT SELECT ON mobileid.miduser TO 'dbuser'@'localhost';`

An example user (with password "secret"):
`INSERT INTO miduser (id, pwd, msisdn) VALUES('exampleuser', 'QwVYkvlrAMsXIgULyQ/pDDwDI3dF2aJD4XeVxg==', '+41791234567');`


## Configuration

Add the module in the sources `config/authsources.php`:

    'MobileID' => array(
        'mobileid:Auth',
        'dsn'          => 'mysql:host=localhost;dbname=mobileid',
        'username'     => 'dbuser',
        'password'     => 'dbpassword',
        'cert_file'    => '/opt/mobileid/mycert.crt',
	'cert_key'     => '/opt/mobileid/mycert.key',
	'mid_ca'       => '/opt/mobileid/swisscom-ca.crt',
	'mid_ocsp'     => '/opt/mobileid/swisscom-ocsp.crt',
	'ap_id'        => '<ID provided by Swisscom>',
        'msg_en'       => 'Authentification with Mobile ID?',
        'msg_de'       => 'Authentifizierung mit Mobile ID?',
        'msg_fr'       => 'Authentification avec Mobile ID?',
        'msg_it'       => 'Autenticazione con Mobile ID?',
    ),

Optional elements
	'ap_pwd'       => '<Password provided by Swisscom>',
        'timeout_ws'   => 90,
        'timeout_mid'  => 80,
        'default_lang' => 'en',

