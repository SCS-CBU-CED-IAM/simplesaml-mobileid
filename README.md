swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp


And configured in `config/authsources.php`:

    'myauthinstance' => array(
        'mobileid:Auth',
        'dsn' => 'mysql:host=sql.example.org;dbname=userdatabase',
        'username' => 'db_username',
        'password' => 'secret_db_password',
	'language' => 'en',
        'DTBS_en' => 'Do you want to login?',
        'DTBS_de' => 'Wollen Sie sich einloggen?',
        'DTBS_fr' => 'Voulez-vous vous connecter?',
        'DTBS_it' => 'Login ?',
    ),

