/*
* ====================================================
* Mobile ID Authentication Method for simplesamlphp
* ---------------------------------------------------
* To be installed in modules/mobileid
*
* mkdir modules/mobileid
* cd modules/mobileid
*  cp ../core/default-enable .
*  mkdir -p lib/Auth/Source
*  touch lib/Auth/Source/Auth.php
*
* Dependencies: curl
* Database:
*   Table: miduser
*   Fields: id,pwd,language,mail
*     id: user id
*     msisdn: related MSIDSN number
*     pwd: optional related password
*     lang: optional language for this user (de, en, fr, it)
*     mail: optional eMail value
* ====================================================
*/

<?php
class sspmod_mymodule_Auth_Source_MyAuth extends sspmod_core_Auth_UserPassBase {

	/* The database DSN.
	* See the documentation for the various database drivers for information about the syntax:
	*     http://www.php.net/manual/en/pdo.drivers.php
	*/
	private $dsn;

	/* The database username & password. */
	private $username;
	private $password;

	/* The mobile id related stuff. */
	private uid;
	private language;
	private dtbs;

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
		uid = array($username);

		/* Retrieve the row from the database. */
		$row = $st->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			/* User alias found. */
			uid = array($row['msisdn'];

			/* Check the password. */
			if (!$this->checkPassword($row['password_hash'], $password)) {
				/* Invalid password. */
				SimpleSAML_Logger::warning('MyAuth: Wrong password for user ' . var_export($username, TRUE) . '.');
				throw new SimpleSAML_Error_Error('WRONGUSERPASS');
			}
		}

		/* CALLLLLLL */


		/* Create the attribute array of the user. */
		$attributes = array(
			'uid' => array($uid),
			'displayName' => array($row['full_name']),
			'eduPersonAffiliation' => array('member', 'employee'),
		);

		/* Return the attributes. */
		return $attributes;
	}
}