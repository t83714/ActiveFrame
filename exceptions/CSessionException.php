<?php
class CSessionException extends CAppException
{
	const SESSION_EXPIRED=1;
	const SESSION_NOT_STARTED=2;
	const INVALID_LSID=3;
	
	public function __construct($code)
	{
		parent::__construct("Session Error: ".$this->translate_const($code),$code);
	}

}

?>