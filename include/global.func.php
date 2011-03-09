<?php
if(!defined('IN_APP')) {
	exit('Access Denied');
}

function is_uuid($guid) {
	if(strlen($guid) != 36) return false;
	if(preg_match("/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/i", $guid)) return true;
	return false;
}

function uuid()
{
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
}

function paserControllerPath()
{
	global $APP_ENV;
	$pathArray=explode('/',$_GET['path']);
	$num=count($pathArray);
	if($num<2)
	{
		$APP_ENV['controllerFile']=APP_CTR_ROOT.'C_'.$pathArray[0].'.php';
		$APP_ENV['requestPath']='/';
		$APP_ENV['controllerName']=$pathArray[0];
		$APP_ENV['requestMethod']=$APP_ENV['RequestMethod']='index';
		return;
	}
	$possibleCtrName=$pathArray[$num-2];
	$possibleMethodName=$pathArray[$num-1];
	unset($pathArray[$num-1]);
	unset($pathArray[$num-2]);
	$tempPathStr=implode('/',$pathArray);
	$APP_ENV['controllerFile']=APP_CTR_ROOT.$tempPathStr.'/C_'.$possibleCtrName.'.php';
	if(is_file($APP_ENV['controllerFile'])) 
	{
		$APP_ENV['controllerName']=$possibleCtrName;
		$APP_ENV['requestMethod']=$APP_ENV['RequestMethod']=$possibleMethodName;
		$APP_ENV['requestPath']='/'.$tempPathStr;
		return;
	}else{
		$APP_ENV['controllerFile']=APP_CTR_ROOT.$tempPathStr.'/'.$possibleCtrName.'/C_'.$possibleMethodName.'.php';
		$APP_ENV['controllerName']=$possibleMethodName;
		$APP_ENV['requestMethod']=$APP_ENV['RequestMethod']='index';
		$APP_ENV['requestPath']=($tempPathStr?'/'.$tempPathStr:'').'/'.$possibleCtrName;
		return;
	}
}

function cutstr($string, $length, $dot = '...',$charset='utf-8') 
{

	if(strlen($string) <= $length) {
		return $string;
	}

	$string = str_replace(array('&amp;', '&quot;','&#039;', '&lt;', '&gt;'), array('&', '"','\'', '<', '>'), $string);
	//$string=htmlspecialchars_decode($string,ENT_QUOTES);
	$strcut = '';
	if(strtolower($charset) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}


	$strcut=htmlspecialchars($strcut,ENT_QUOTES);
	return $strcut.$dot;
}


function fileext($filename) {
	return trim(substr(strrchr($filename, '.'), 1, 10));
}



function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function removedir($dirname, $keepdir = FALSE) {

	$dirname = wipespecial($dirname);

	if(!is_dir($dirname)) {
		return FALSE;
	}
	$handle = opendir($dirname);
	while(($file = readdir($handle)) !== FALSE) {
		if($file != '.' && $file != '..') {
			$dir = $dirname . DIRECTORY_SEPARATOR . $file;
			is_dir($dir) ? removedir($dir) : unlink($dir);
		}
	}
	closedir($handle);
	return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}



function appfopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = FALSE) {
	global $version, $boardurl;
	if(ini_get('allow_url_fopen') && !$bysocket && !$post) {
		$fp = @fopen($url, 'r');
		$s = @fread($fp, $limit);
		@fclose($fp);
		return $s;
	}
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$script = $matches['path'].'?'.$matches['query'].'#'.$matches['fragment'];
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) {
		$out = "POST $script HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Accept-Encoding: none\r\n";
		$out .= "User-Agent: Comsenz/1.0 ($version)\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $script HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Accept-Encoding:\r\n";
		$out .= "User-Agent: Comsenz/1.0 ($version)\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = fsockopen($host, $port, $errno, $errstr, 30);
	if(!$fp) {
		return "";
	} else {
		@fwrite($fp, $out);
		while(!feof($fp) && $limit > -1) {
			$limit -= 524;
			$return .= @fread($fp, 524);
		}
		@fclose($fp);
		$return = preg_replace("/\r\n\r\n/", "\n\n", $return, 1);
		$strpos = strpos($return, "\n\n");
		$strpos = $strpos !== FALSE ? $strpos + 2 : 0;
		$return = substr($return, $strpos);
		return $return;
	}
}


//---------------------------------------

function template($file)
{
	global $APP_ENV;
	$tplBaseDir = $APP_ENV['viewRoot'].$APP_ENV['curView'].'/';
	$tplfile=$tplBaseDir.$file.'.tpl.htm';
	$objBaseDir = $APP_ENV['tempRoot'].'viewCacheData/'.$APP_ENV['curView'].'/';
	$objfile=$objBaseDir.$file.'.tpl.php';
	$APP_ENV['runtimeView']=$APP_ENV['curView'];
	if(!is_file($objfile) || @filemtime($tplfile) > @filemtime($objfile)) parse_template($file);
	return $objfile;
}


function loadwidget($file,$data=null,$ifCaptureOutput=false)
{
	global $APP_ENV;
	$tplBaseDir = $APP_ENV['viewRoot'].$APP_ENV['curView'].'/';
	$tplfile=$tplBaseDir.$file.'.widget.php';
	$objBaseDir = $APP_ENV['tempRoot'].'viewCacheData/'.$APP_ENV['curView'].'/';
	$objfile=$objBaseDir.$file.'.widget.php';
	$APP_ENV['runtimeView']=$APP_ENV['curView'];
	if(!is_file($objfile) || @filemtime($tplfile) > @filemtime($objfile)) parse_template($file,'widget');
	if($data!==null && is_array($data)) $r=extract($data,EXTR_PREFIX_INVALID,'widget_arg');
	if($data!==null && !is_array($data)) $widget_arg_0=$data;
	//unset($data);
	if($ifCaptureOutput===false)
	{
		include $objfile;
		return '';
	}
	else{
		ob_start();
		include $objfile;
		$buffer=ob_get_contents();
		ob_get_clean();
		return $buffer;
	}
}


function loadview($file,$data=false,$ifCaptureOutput=false)
{
	global $APP_ENV;
	if($APP_ENV['debug']) $APP_ENV['debugInfo']['currentView']=$file;
	if($ifCaptureOutput===false)
	{
		if($data!==false && is_array($data)) extract($data);
		include template($file);
		return '';
	}
	else{
		if($data!==false) extract($data);
		ob_start();
		include template($file);
		$buffer=ob_get_contents();
		ob_get_clean();
		return $buffer;
	}
}

function appexit($message='')
{
	echo $message;
	output();
	exit();
}


//-----------Load mod functions----------------------------------------------------------------------
function loadmodel($path)
{
	global $APP_ENV;
	$file=$APP_ENV['modelRoot'].$path.'.php';
	if(is_file($file)) include_once($file);
	else throw new C404Exception($path,C404Exception::MODEL);
}

function loadException($path)
{
	global $APP_ENV;
	$file=APP_ROOT.'exceptions/'.$path.'.php';
	if(is_file($APP_ENV['exceptionRoot'].$path.'.php')) include_once($APP_ENV['exceptionRoot'].$path.'.php');
	elseif(is_file($file)) include_once($file);
	else throw new C404Exception($path,C404Exception::MODEL);
}

function loadlib($path)
{
	global $APP_ENV;
	$file=APP_ROOT.'libraries/'.$path.'.php';
	if(is_file($APP_ENV['libRoot'].$path.'.php')) include_once($APP_ENV['libRoot'].$path.'.php');
	elseif(is_file($file)) include_once($file);
	else throw new C404Exception($path,C404Exception::LIB);
}

function loadhelper($path)
{
	global $APP_ENV;
	$file=APP_ROOT.'helpers/'.$path.'.php';
	if(is_file($APP_ENV['helperRoot'].$path.'.php')) include_once($APP_ENV['helperRoot'].$path.'.php');
	elseif(is_file($file)) include_once($file);
	else throw new C404Exception($path,C404Exception::HELPER);
}

function loadsnippet($path,$ifOutputPath=true)
{
	global $APP_ENV;
	$file=APP_ROOT.'snippets/'.$path.'.php';
	if($ifOutputPath==true)
	{
		if(is_file($APP_ENV['snippetRoot'].$path.'.php')) return $APP_ENV['snippetRoot'].$path.'.php';
		elseif(is_file($file)) return $file;
		else exit("snippet Root $path can't find.");
	}
	if(is_file($APP_ENV['snippetRoot'].$path.'.php')) include $APP_ENV['snippetRoot'].$path.'.php';
	elseif(is_file($file)) include $file;
	else throw new C404Exception($path,C404Exception::SNIPPET);
}

function loadformatter($path)
{
	global $APP_ENV;
	$file=APP_ROOT.'formatters/'.$path.'.php';
	if(is_file($APP_ENV['formatterRoot'].$path.'.php')) include_once($APP_ENV['formatterRoot'].$path.'.php');
	elseif(is_file($file)) include_once($file);
	else throw new C404Exception($path,C404Exception::FORMATTER);
	
}

	function loadConfig($name)
	{
	  global $APP_ENV;
	  $filename=$APP_ENV['configRoot'].$name.'.config.php';
	  if(is_file($filename)) return $filename;
		else throw new C404Exception($path,C404Exception::CONFIG);
	}	

	/**
	* This function adds javascript to views
	* Loads files stored in the js folder
	* @author John Kamuchau
	* @param $filename name of the javascript file
	**/
	function loadJs($name)
	{
	  global $APP_ENV;
	  $filename=$APP_ENV['jsRoot'].$name.'.js';
	  if(!in_array($filename,$APP_ENV['AppRutimeVar']['loadedJsFiles'])) $APP_ENV['AppRutimeVar']['loadedJsFiles'][]=$filename;
	}	
	/**
	* This function adds css to views
	* Loads css files stored in themes folders
	* @author John Kamuchau
	* @param $filename name of the css file
	**/
	function loadCss($name, $media=FALSE)
	{
	  global $APP_ENV;
	   $filename=$APP_ENV['cssRoot'].$name.'.css';
	  //Acceptable media types
	  $allowed_media_types=array('all','aural','braille','emboss','handheld','print','projection','screen','tty');
	  if(!array_key_exists($filename,$APP_ENV['AppRutimeVar']['loadedCssFiles']))
	  {
	   if($media!=FALSE  && in_array($media,$allowed_media_types)) $APP_ENV['AppRutimeVar']['loadedCssFiles'][$filename]=$media;
	   else $APP_ENV['AppRutimeVar']['loadedCssFiles'][$filename]='all';
	  }
	}	

//----------------------------------------------------------------------------------------------------------
function output()//reserve for last stage
{
		//stage1: check if output?
		//stage2: url rewrite
		//stage3: cache
	
}

function redirect($uri = '',$ifUrl=false,$method = 'location')
{
	global $APP_ENV;
	
	switch($method)
	{
		case 'refresh'	: 
		if($ifUrl===false) header("Refresh:0;url=".$APP_ENV['baseUrl'].'?path='.$uri);
		else header("Refresh:0;url= $uri");
			break;
		default			: 
		if($ifUrl===false) header("Location: ".$APP_ENV['baseUrl'].'?path='.$uri);
		else header("Location: $uri");
			break;
	}
	appexit();
}

function getDebugInfo()
{
	global $APP_ENV;
	$mtime = explode(' ', microtime());
	$APP_ENV['debugInfo']['processTime']=number_format(($mtime[1] + $mtime[0] - $APP_ENV['app_start_time']), 6);
	$APP_ENV['debugInfo']['querynum']=$APP_ENV['db']->querynum;
	$APP_ENV['debugInfo']['memoryUsage']=memory_get_usage();
}

function loadLanguageVar($var,$file)
{
	languagestack($file);
	return languagevar($var,$file); 
}

function globallanguagevar($var) 
{
	global $APP_ENV;
	if(isset($APP_ENV['globalLanguageVar'][$var])) return $APP_ENV['globalLanguageVar'][$var];
	else return "!{$var}!";
}

function debugInfoSQL() { global $APP_ENV; foreach($APP_ENV['debugInfo']['sqlStack'] as $s) echo $s."<br/>\n";}

//!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

function escapeSpecialChars($string)
{
	if(is_array($string)) foreach($string as $k=>$v) $string[$k]=escapeSpecialChars($v);
	else{
		if(MAGIC_QUOTES_GPC) $string=stripslashes($string);
		$string = htmlspecialchars($string,ENT_QUOTES);

	  $string = addslashes($string);
		
	}
	return $string;
}

function unescapeSpecialChars($string,$mode='all')
{
	if(is_array($string)) foreach($string as $k=>$v) $string[$k]=unescapeSpecialChars($v,$mode);
	else{
    if($mode=='s'||$mode=='all') $string = stripslashes($string);
		if($mode=='h'||$mode=='all') $string = htmlspecialchars_decode($string,ENT_QUOTES);
	}
	return $string;
}

function cutstrWithHints($str,$length)
{
	if(strlen($str) <= $length) {
		return $str;
	}	
	$shortStr=cutstr($str,$length);
	$output="<span onmouseover='ddrivetip(\"$str\",\"#E6EEF7\");' onmouseout='hideddrivetip()'>$shortStr</span>";
	return $output;
}


function changeDBSetting($settingName)
{
		global $APP_ENV;
		if($settingName==$APP_ENV['current_db_setting']) return $APP_ENV['current_db_setting'];
		if(!isset($APP_ENV['db_settings'][$settingName])) exit('Selected DB Selecting Doesn\'t exsited');
		$currentSetting=$APP_ENV['current_db_setting'];
		$APP_ENV['current_db_setting']=$settingName;
		if(!isset($APP_ENV['db_conection_pool'][$settingName]))
		{
			$APP_ENV['db_conection_pool'][$settingName]=new dbstuff;
			$APP_ENV['db_conection_pool'][$settingName]->connect($APP_ENV['db_settings'][$settingName]['hostname'],$APP_ENV['db_settings'][$settingName]['username'],$APP_ENV['db_settings'][$settingName]['password'],$APP_ENV['db_settings'][$settingName]['database'], $APP_ENV['db_settings'][$settingName]['pconnect']);
		}
		$APP_ENV['db']=$APP_ENV['db_conection_pool'][$settingName];
		return $currentSetting;
}
?>