<?php
class CACLException extends CAppException
{
	const SESSION_EXPIRED=1;
	const SESSION_NOT_STARTED=2;
	const INVALID_ROLE_ID=3;
	const ACCESS_DENIED=4;
	const LOGIN_REQUIRED=5;
	const INVALID_USERNAME_OR_PASSWORD=6;
	const INVALID_CAPTCHA=7;
	const INVALID_ACCOUNT_INFO=8;
	const INVALID_API_KEY=9;
	
	public $request_url;
	
	public function __construct($code)
	{
		$request_url=$_SERVER['REQUEST_URI'];
		parent::__construct("Access Control Error: ".$this->translate_const($code),$code);
	}
	
	private function process_login_required()
	{
		loadlib('SessionManager');
		if(!SessionManager::is_sub_session()){
			URLUtil::redirect('?path=common_func/login',$_SERVER['REQUEST_URI'],"You need to login to access the resource requested!");
			return;
		}
		/*$url=$_SERVER['REQUEST_URI'];
		$qs=$_SERVER["QUERY_STRING"];
		$url=str_replace($qs,'',$url);
		$url=str_replace('subsys/','',$url);*/
		$text="This observer session has been closed.\n\nWindow is about to close.";
		$message=json_encode($text);
		$alt_text="<br/><br/><center>This observer session has been closed.<br/><br/>Please close the browser and try again.</center>";
		$alt_message=json_encode($alt_text);
		echo "<script type='text/javascript'>
			if(window.opener){
				alert($message);
				window.close();
			}else{
				document.write($alt_message);
			}
		</script>";
		return;
	}
	
	/**
	 * Default exception handler
	 *
	 */
	public function handleException()
	{
		switch(self::getCode())
		{
			case self::LOGIN_REQUIRED : $this->process_login_required();break;
			case self::INVALID_USERNAME_OR_PASSWORD : SessionManager::increase_login_failed_times();break;
			case self::ACCESS_DENIED : URLUtil::redirect('?path=common_func/choose_position/access_denied',$_SERVER['REQUEST_URI']); break;
		}
	}

}

?>