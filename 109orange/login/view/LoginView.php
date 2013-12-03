<?php

namespace login\view;

require_once("./common/Filter.php");
require_once("./login/model/LoginObserver.php");
require_once("./login/model/create.php");

class LoginView implements \login\model\LoginObserver {

	//positions in input arrays
	private static $LOGOUT = "logout";
	private static $LOGIN = "login";
	private static $USERNAME = "LoginView::UserName";
	private static $PASSWORD = "LoginView::Password";
	private static $PASSWORD2 = "LoginView::Password2";
	private static $CHECKED = "LoginView::Checked";

	/**
	 * Used to pass messages to client
	 * 
	 * @var string
	 */
	private $message = "";
	
	
	/**
	 * @return String HTML
	 */
	public function getLoginBox() {	
		 
		$user = $this->getUserName();
		$checked = $this->userWantsToBeRemembered() ? "checked=checked" : "";
		
		$html = "
			<form action='?" . self::$LOGIN . "' method='post' enctype='multipart/form-data'>
				<fieldset>
					$this->message
					<legend>Login - Skriv in användarnamn och lösenord</legend>
					<label for='UserNameID' >Användarnamn :</label>
					<input type='text' size='20' name='" . self::$USERNAME . "' id='UserNameID' value='$user' />
					<label for='PasswordID' >Lösenord  :</label>
					<input type='password' size='20' name='" . self::$PASSWORD . "' id='PasswordID' value='' />
					<label for='AutologinID' >Håll mig inloggad  :</label>
					<input type='checkbox' name='" . self::$CHECKED . "' id='AutologinID' $checked/>
					<input type='submit' name=''  value='Logga in' />
				</fieldset>
			</form>";
			
		return $html;

	}

	public function getCreateBox() {

		$user = $this->getUserName();
		$html = "
				<form action='?create' method='post'>
				<fieldset>
					<legend>Registrera ny användare - Skriv in användarnamn och lösenord</legend>
					$this->message <br/>
					<label for='name'>Namn:</label>
					<input id='name' name='" . self::$USERNAME . "' type='text' size='15' value='$user'></br>
					<label for='password1'>Lösenord:</label>
					<input id='password1' name='" . self::$PASSWORD . "' type='password' size='15'></br>
					<label for='password2'>Repetera lösenord:</label>
					<input id='password2' name='" . self::$PASSWORD2 . "' type='password' size='15'></br>
					<input type='submit' name='reg' value='Registrera'>
				</fieldset>
				</form>";

		return $html;
	}	
	
	/**
	 * @return boolean
	 */
	public function isLoggingOut() {
		return isset($_GET[self::$LOGOUT]);
	}
	
	/**
	 * @return boolean
	 */
	public function isLoggingIn() {
		if (isset($_GET[self::$LOGIN])) {
			return true;
		} else if ($this->hasCookies()) {
			return true;
		} else {
			return false;
		}
	}

	public function isCreating() {
		if (isset($_GET['create'])) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * @return String HTML
	 */
	public function getLogoutButton() {
		return "$this->message <a href='?" . self::$LOGOUT . "'>Logga ut</a>";
	}
	
	/**
	 * @return UserCredentials
	 */
	public function getUserCredentials() {
		if ($this->hasCookies()) {
			return \login\model\UserCredentials::createWithTempPassword(new \login\model\UserName($this->getUserName()), 
																	  $this->getTemporaryPassword());
		} else {
			return \login\model\UserCredentials::createFromClientData(	new \login\model\UserName($this->getUserName()), 
																	\login\model\Password::fromCleartext($this->getPassword()));
		}
	}

	public function getNewCredentials() {
		$create = new \login\model\create($this->getUserName(), $this->getPassword(), $this->getPassword2());
		
		if(\Common\Filter::hasTags($_POST[self::$USERNAME]) == true) {
			throw new \Exception("Error Processing Request", 1);
			;
		}
		else {
			return \login\model\UserCredentials::create(new \login\model\UserName($create->username), 
													\login\model\Password::fromCleartext($create->password));
		}
	}

		
	
	/**
	 * From \model\LoginObserver
	 */
	public function loginFailed() {
		if ($this->hasCookies()) {
			$this->message = "<p>Felaktig information i cookie</p>";
			$this->removeCookies();
		} else { 
			
			if ($this->getUserName() == "") {
				$this->message .= "<p>Användarnamn saknas</p>";
			} else if ($this->getPassword() == "") {
				$this->message .= "<p>Lösenord saknas</p>";
			} else {
				$this->message = "<p>Felaktigt användarnamn och/eller lösenord</p>";
			}
		}
	}

	public function createFailed() {
		$username = $this->getUserName();
		$password = $this->getPassword();
		$password2 = $this->getPassword2();

		if(\Common\Filter::hasTags($_POST[self::$USERNAME]) == true) {
			$this->message .= "<p>Användarnamnet innehåller ogiltiga tecken</p>";
		}

		if ($password != $password2) {
			$this->message .= "<p>Lösenorden matchar inte</p>";
		}

		if (strlen($username) < 3) {
			$this->message .= "<p>Användarnamnet har för få tecken. Minst 3 tecken</p>";
		}		

		if (strlen($password) < 6) {
			$this->message .= "<p>Lösenorden har för få tecken. Minst 6 tecken</p>";
		}
		else if (strlen($password2) < 6) {
			$this->message .= "<p>Lösenorden har för få tecken. Minst 6 tecken</p>";
		}

		/*if($uniqeUser == null) {	//blir 2 utskrivningar
			$this->message .= "<p>Användarnamnet är redan upptaget</p>";
		}*/

	}

	public function createSuccess() {
		$this->getLoginBox();
		$this->message  = "<p>Registrering av ny användare lyckades</p>";
		//header("Location: index.php");
		
	}
	
	/**
	 * From \model\LoginObserver
	 */
	public function loginOK(\login\model\TemporaryPasswordServer $tempCookie) {

		if ($this->userWantsToBeRemembered() || 
			$this->hasCookies()) {
			if ($this->hasCookies()) {
				$this->message  = "<p>Inloggning lyckades via cookies</p>";
			} else {
				$this->message  = "<p>Inloggning lyckades och vi kommer ihåg dig nästa gång</p>";
			}
			
			$expire = $tempCookie->getExpireDate();// ;
			setcookie(self::$USERNAME, $this->getUserName(), $expire);
			setcookie(self::$PASSWORD, $tempCookie->getTemporaryPassword(), $expire);
		} else {
			$this->message  = "<p>Inloggning lyckades</p>";
		} 
		
	}
	
	public function doLogout() {
		$this->removeCookies();
		
		$this->message  = "<p>Du har nu loggat ut</p>";
	}
	
	
	
	/**
	 * note: private!
	 * @return String
	 */
	private function getUserName() {
		if (isset($_POST[self::$USERNAME]))
			return \Common\Filter::sanitizeString($_POST[self::$USERNAME]);
		else if (isset($_COOKIE[self::$USERNAME]))
			return \Common\Filter::sanitizeString($_COOKIE[self::$USERNAME]);
		else
			return "";
	}
	
	
	/**
	 * note: private!
	 * 
	 * @return String
	 */
	private function getPassword() {
		if (isset($_POST[self::$PASSWORD]))
			return \Common\Filter::sanitizeString($_POST[self::$PASSWORD]);
		else
			return "";
	}

	/**
	 * note: private!
	 * 
	 * @return String
	 */
	private function getPassword2() {
		if (isset($_POST[self::$PASSWORD2]))
			return \Common\Filter::sanitizeString($_POST[self::$PASSWORD2]);
		else
			return "";
	}
	

	/**
	 * If user checks the remember me checkbox
	 * @return boolean 
	 */
	private function userWantsToBeRemembered() {
		return isset($_POST[self::$CHECKED]);
	}

	/**
	 * Get cookie password
	 * @return TemporaryPasswordClient
	 */
	private function getTemporaryPassword() {
		if (isset($_COOKIE[self::$PASSWORD])) {
			$fromCookieString = \Common\Filter::sanitizeString($_COOKIE[self::$PASSWORD]);
			return \login\model\TemporaryPasswordClient::fromString($fromCookieString);
		} else {
			return \login\model\TemporaryPasswordClient::emptyPassword();
		}
	}
	
	/**
	 * Did user supply cookie password?
	 * 
	 * @return boolean 
	 */
	private function hasCookies() {
		return isset($_COOKIE[self::$PASSWORD]) && isset($_COOKIE[self::$USERNAME]);
	}

	/**
	 * Removes cookies from client 
	 */
	private function removeCookies() {

		unset($_COOKIE[self::$USERNAME]);
		unset($_COOKIE[self::$PASSWORD]);
			
		$expireNow = time()-1;
		setcookie(self::$USERNAME, "", $expireNow);
		setcookie(self::$PASSWORD, "", $expireNow);
	}
	
}
