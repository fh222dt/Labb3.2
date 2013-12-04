<?php
namespace login\model;

class Create { 
	//sträng för användarnamn
	public $username = "";
	//sträng för lösenord
	public $password = "";

	public $password2 = "";

	//skapa en användare
	//Tar 2 strängar som inparametrar
	public function __construct ($username, $password, $password2) {
		if ($username == "") {
            throw new \Exception("Måste ange användarnamn");
        }
        if ($password == "") {
            throw new \Exception("Måste ange lösenord");
        }
        if ($password2 == "") {
            throw new \Exception("Måste ange lösenord");
        }

        if($password != $password2) {
        	throw new \Exception("Måste vara samma lösenord");
        }

		$this->username = $username;
		$this->password = $password;
		
	}
}