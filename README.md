swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp

Refer to http://simplesamlphp.org/docs/stable/simplesamlphp-modules for details.


And configured in `config/authsources.php`:

    'MobileID' => array(
        'mobileid:Auth',
        'dsn' => 'mysql:host=localhost;dbname=mobileid',
        'username' => 'db_username',
        'password' => 'secret_db_password',
        'language' => 'en',
        'DTBS_en' => 'Do you want to login?',
        'DTBS_de' => 'Wollen Sie sich einloggen?',
        'DTBS_fr' => 'Voulez-vous vous connecter?',
        'DTBS_it' => 'Login ?',
    ),

