<?php

namespace login\model;

/**
 * Callback interface
 */
interface LoginObserver {
	public function loginFailed();
	public function createFailed();		//lagt till 
	public function loginOK(TemporaryPasswordServer $info);
}

