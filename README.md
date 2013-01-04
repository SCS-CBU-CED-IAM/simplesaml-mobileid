swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp


And configured in `config/authsources.php`:

    'myauthinstance' => array(
        'mobileid:Auth',
        'dsn' => 'mysql:host=sql.example.org;dbname=userdatabase',
        'username' => 'db_username',
        'password' => 'secret_db_password',
    ),

