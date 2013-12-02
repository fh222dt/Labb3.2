<?php

namespace application\view;

require_once("common/view/Page.php");
require_once("SwedishDateTimeView.php");



class View {
	/**
	 * @var \Login\view\LoginView
	 */
	private $loginView;

	/**
	 * @var  SwedishDateTimeView $timeView;
	 */
	private $timeView;
	
	/**
	 * @param LoginviewLoginView $loginView 
	 */
	public function __construct(\login\view\LoginView $loginView) {
		$this->loginView = $loginView;
		$this->timeView = new SwedishDateTimeView();
	}
	
	/**
	 * @return \common\view\Page
	 */
	public function getLoggedOutPage() {
		$html = $this->getHeader(false);
		$loginBox = $this->loginView->getLoginBox(); 

		$html.="<a href='?regform'>Registrera ny användare</a>";

		$html .= "<h2>Ej Inloggad</h2>
				  	$loginBox
				 ";
		$html .= $this->getFooter();

		return new \common\view\Page("Laboration. Inte inloggad", $html);
	}
	
	/**
	 * @param \login\login\UserCredentials $user
	 * @return \common\view\Page
	 */
	public function getLoggedInPage(\login\model\UserCredentials $user) {
		$html = $this->getHeader(true);
		$logoutButton = $this->loginView->getLogoutButton(); 
		$userName = $user->getUserName();

		$html .= "
				<h2>$userName är inloggad</h2>
				 	$logoutButton
				 ";
		$html .= $this->getFooter();

		return new \common\view\Page("Laboration. Inloggad", $html);
	}

	public function getCreateUserPage (){
		$html = $this->getHeader(false);
		$createBox = $this->loginView->getCreateBox();

		$html.="<a href='index.php'>Tillbaka</a>";

		$html .= "<h2>Ej Inloggad, Registrerar användare</h2>
				  	$createBox
				 ";
		$html .= $this->getFooter();


		return new\common\view\Page("Registrera ny användare", $html);
	}
	
	
	/**
	 * @param boolean $isLoggedIn
	 * @return  String HTML
	 */
	private function getHeader($isLoggedIn) {
		$ret =  "<h1>Laborationskod fh222dt</h1>";
		return $ret;
		
	}

	/**
	 * @return [type] [description]
	 */
	private function getFooter() {
		$timeString = $this->timeView->getTimeString(time());
		return "<p>$timeString<p>";
	}
	
	
	
}
