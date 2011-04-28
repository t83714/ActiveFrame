<?php
/**
 * @author Jacky Jiang
 * @name ParentCController
 * @version 0.1.1
 */
class CController
{
	
	 public $js=array();
	 public $css=array();
	/**
	* Controller constructor
	* @author Jacky Jiang
	**/
	function CController($auto_lang_support=true,$auto_start_session=false)
	{
		if($auto_start_session) SessionManager::start();
		loadLib('BrowserManager');
		global $APP_ENV;
		if($auto_lang_support)
		{
			BrowserManager::detect_language();
			if(!isset($APP_ENV['supportLangList']) || array_key_exists($APP_ENV['curLang'],$APP_ENV['supportLangList'])===false) $APP_ENV['curLang']='defaultLang';
			$this->loadGlobalLanguageFile();
			$APP_ENV['languageOptionHtml']=$this->getLanguageOptionHtml();
		}
		
	}
	/**
	* Loads the global language File
	* @author Jacky Jiang
	**/
	function loadGlobalLanguageFile()
	{
		global $APP_ENV;
		$langBaseDir = $APP_ENV['langRoot'].$APP_ENV['curView'].'/';
		$langFile=$langBaseDir.$APP_ENV['curLang'].'.global.lang.php';
		if(!is_file($langFile)) $langFile=$langBaseDir.'defaultLang'.'.global.lang.php';
		if(!is_file($langFile)) $APP_ENV['globalLanguageVar']=array('APP_ERR'=>'Cann\'t find language file!');
		else {
			include $langFile;
			$APP_ENV['globalLanguageVar']=$lang;
		}

	}
	
	function getLanguageOptionHtml()
	{
		global $APP_ENV;
		$option='';
		if(!is_array($APP_ENV['supportLangList'])) return "<option value='defaultLang'>default Language</option>";
		foreach($APP_ENV['supportLangList'] as $k=>$v)
		{
			if($APP_ENV['curLang']==$k) $option.="<option value='$k' selected>$v</option>";
			else $option.="<option value='$k'>$v</option>";
		}
		return $option;
	}
	
	//---need to be remove
	function loadLanguageVar($var,$file)
	{
		return loadLanguageVar($var,$file);
	}
	
	/**
	* This function adds javascript to views
	* Loads files stored in the js folder
	* @author John Kamuchau
	* @param $filename name of the javascript file
	**/
	function loadJavascript($filename)
	{
	  loadJs($filename);
	}	
	/**
	* This function adds css to views
	* Loads css files stored in themes folders
	* @author John Kamuchau
	* @param $filename name of the css file
	**/
	function loadCss($filename, $media=FALSE)
	{
	  loadCss($filename, $media=FALSE);
	}	
	
	/**
	* This function return the url of the current page
	* @author John Kamuchau
	**/
	function selfURL() 
	{
		$s = empty($_SERVER["HTTPS"]) ? ''
			: ($_SERVER["HTTPS"] == "on") ? "s"
			: "";
		$protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
			: (":".$_SERVER["SERVER_PORT"]);
		return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
	}
	
	function strleft($s1, $s2) 
	{
		return substr($s1, 0, strpos($s1, $s2));
	}
	
}



?>