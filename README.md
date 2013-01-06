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
Enable the module `touch modules/mobileid/default-enabled`


## Configuration

Add the module in the sources `config/authsources.php`:

    'MobileID' => array(
        'mobileid:Auth',
        'dsn' => 'mysql:host=localhost;dbname=mobileid',
        'username' => 'db_username',
        'password' => 'secret_db_password',
        'language' => 'en',
        'DTBS_en' => 'Authentification with Mobile ID?',
        'DTBS_de' => 'Authentifizierung mit Mobile ID?',
        'DTBS_fr' => 'Authentification avec Mobile ID?',
        'DTBS_it' => 'Autenticazione con Mobile ID?',
    ),

