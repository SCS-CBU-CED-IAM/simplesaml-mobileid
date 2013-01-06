<?php

/*
 * This class implements Mobile ID authentication with optional userid aliasing and password validation
 * against an SQL database.
 *
 * @author Freddy Kaiser <kaiser.freddy@gmail.com>
 * @package simpleSAMLphp
 * @version $Id$
 */

class sspmod_mobileid_Auth_Source_Auth extends sspmod_core_Auth_UserPassBase {

	/* The database DSN.
	 * See the documentation for the various database drivers for information about the syntax:
	 *     http://www.php.net/manual/en/pdo.drivers.php
	 */
	private $dsn;

	/* The database username & password. */
	private $username;
	private $password;

	/* The mobile id related stuff. */
	private $uid;
	private $dtbs_en;
    private $dtbs_de;
    private $dtbs_fr;
    private $dtbs_it;

	public function __construct($info, $config) {
		parent::__construct($info, $config);

		if (!is_string($config['dsn'])) {
			throw new Exception('Missing or invalid dsn option in config.');
		}
		$this->dsn = $config['dsn'];

		if (!is_string($config['username'])) {
			throw new Exception('Missing or invalid username option in config.');
		}
		$this->username = $config['username'];

		if (!is_string($config['password'])) {
			throw new Exception('Missing or invalid password option in config.');
		}
		$this->password = $config['password'];

		if (!is_string($config['language'])) {
			throw new Exception('Missing or invalid language option in config.');
		}
		$this->language = $config['language'];

        /* DTBS: TODO move in an array */
		if (!is_string($config['DTBS_en'])) {
			throw new Exception('Missing or invalid DTBS_en option in config.');
		}
		$this->DTBS_en = $config['DTBS_en'];
        
		if (!is_string($config['DTBS_de'])) {
			throw new Exception('Missing or invalid DTBS_de option in config.');
		}
		$this->DTBS_de = $config['DTBS_de'];
        
		if (!is_string($config['DTBS_fr'])) {
			throw new Exception('Missing or invalid DTBS_fr option in config.');
		}
		$this->DTBS_fr = $config['DTBS_fr'];
        
		if (!is_string($config['DTBS_it'])) {
			throw new Exception('Missing or invalid DTBS_it option in config.');
		}
		$this->DTBS_it = $config['DTBS_it'];
	}

	/* A helper function for validating a password hash.
	 *
	 * In this example we check a SSHA-password, where the database
	 * contains a base64 encoded byte string, where the first 20 bytes
	 * from the byte string is the SHA1 sum, and the remaining bytes is
	 * the salt.
	 */
	private function checkPassword($passwordHash, $password) {
		$passwordHash = base64_decode($passwordHash);
		$digest = substr($passwordHash, 0, 20);
		$salt = substr($passwordHash, 20);

		$checkDigest = sha1($password . $salt, TRUE);
		return $digest === $checkDigest;
	}

    /* The login function.
     *
     * Cleanup of the username
     * Verification if username has an alias and get the corresponding MSISDN
     * Verification if username has a password and check it
     */
	protected function login($username, $password) {
		/* Connect to the database. */
		$db = new PDO($this->dsn, $this->username, $this->password);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		/* With PDO we use prepared statements. This saves us from having to escape the username in the database query. */
		$st = $db->prepare('SELECT id, msisdn, pwd, lang, mail FROM miduser WHERE id=:username');

		if (!$st->execute(array('username' => $username))) {
			throw new Exception('Failed to query database for mobile id user.');
		}

		/* uid defaults to username. */
		$uid = array($username);

		/* Retrieve the row from the database. */
		$row = $st->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			/* User alias found. */
			$uid = array($row['msisdn']);

			/* Check the password. */
			if (!$this->checkPassword($row['password_hash'], $password)) {
				/* Invalid password. */
				SimpleSAML_Logger::warning('MyAuth: Wrong password for user ' . var_export($username, TRUE) . '.');
				throw new SimpleSAML_Error_Error('WRONGUSERPASS');
			}
		}

        /* Get default language of session/browser */
        $language = 'en';
        $message = $DTBS_en;

        /* CALLLLLLL */


		/* Create the attribute array of the user. */
		$attributes = array(
			'uid' => array($uid),
			'eMail' => array($row['mail']),
		);

		/* Return the attributes. */
		return $attributes;
	}
}