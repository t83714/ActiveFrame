<?php
class BrowserManager
{
	
	public static function get_language_code()
	{
		if (!isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"])) return array();
		
		$languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
		// $languages = ' fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3';
		// need to remove spaces from strings to avoid error
		$languages = str_replace( ' ', '', $languages );
		$languages = explode( ",", $languages );
		//$languages = explode( ",", $test);// this is for testing purposes only
		foreach ( $languages as $language_list )
		{
			// pull out the language, place languages into array of full and primary
			// string structure:
			$temp_array = array();
			// slice out the part before ; on first step, the part before - on second, place into array
			$temp_array[0] = substr( $language_list, 0, strcspn( $language_list, ';' ) );//full language
			$temp_array[1] = substr( $language_list, 0, 2 );// cut out primary language
			//place this array into main $user_languages language array
			$user_languages[] = $temp_array;
		}
		return $user_languages;
	}
	
	public static function detect_language()
	{
		global $APP_ENV;
		if(isset($_GET['lang'])) $APP_ENV['curLang']=$_GET['lang'];
		else if(isset($_POST['lang'])) $APP_ENV['curLang']=$_POST['lang'];
		else if(isset($_COOKIE['lang'])) $APP_ENV['curLang']=$_COOKIE['lang'];
		else{
			$langs=self::get_language_code();
			if(empty($langs)) $APP_ENV['curLang']='defaultLang';
			$APP_ENV['curLang']=$tempArray[0][0] ? $tempArray[0][0] : 'defaultLang';
		}
	}
	
}
?>