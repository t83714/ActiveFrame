<?php

/**
 * @author Jacky Jiang
 * @version 0.1.1
 */

error_reporting(E_ALL ^ E_NOTICE);

include APP_ROOT.'./include/CAppException.class.php';
include APP_ROOT.'./include/CModel.class.php';

//set_magic_quotes_runtime(0);
$mtime = explode(' ', microtime());
$app_start_time = $mtime[1] + $mtime[0];


$APP_ENV=array();
//-----Config------------------------
$APP_ENV['controllerRoot']=APP_CTR_ROOT;
$APP_ENV['viewRoot']=APP_ROOT.'application/views/';
$APP_ENV['langRoot']=APP_ROOT.'application/lang/';
$APP_ENV['tempRoot']=APP_ROOT.'temp/';
$APP_ENV['libRoot']=APP_ROOT.'application/libraries/';
$APP_ENV['helperRoot']=APP_ROOT.'application/helpers/';
$APP_ENV['modelRoot']=APP_ROOT.'application/models/';
$APP_ENV['pluginRoot']=APP_ROOT.'application/plugins/';
$APP_ENV['snippetRoot']=APP_ROOT.'application/snippets/';
$APP_ENV['formatterRoot']=APP_ROOT.'application/formatters/';
$APP_ENV['exceptionRoot']=APP_ROOT.'application/exceptions/';
$APP_ENV['curView']='defaultView';
$APP_ENV['runtimeView']='defaultView';
$APP_ENV['curLang']='defaultLang';
$APP_ENV['languageDictionary']=array();
$APP_ENV['globalLanguageVar']=array();
$APP_ENV['languageOptionHtml']='';
$ps=empty($_SERVER['HTTPS'])?'':($_SERVER['HTTPS'] == "on")?'s':'';
$port=(($_SERVER['SERVER_PORT']=='80'&& $ps=='') || ($_SERVER['SERVER_PORT']=='443'&& $ps=='s'))?'':(':'.$_SERVER['SERVER_PORT']);
$APP_ENV['baseUrl']='http'.$ps.'://'.$_SERVER['SERVER_NAME'].$port.preg_replace('/\/[^\/]*(\?.*)*$/i','',$_SERVER['REQUEST_URI']).'/';
unset($ps,$port);
$APP_ENV['app_start_time']=$app_start_time;
$APP_ENV['debugInfo']['curSql']='';
$APP_ENV['debugInfo']['sqlStack']=array();
$APP_ENV['timestamp']=time();
$APP_ENV['AppRutimeVar']=array('loadedJsFiles'=>array(),'loadedCssFiles'=>array());

$APP_ENV['jsRoot']=$APP_ENV['baseUrl'].'js/';
$APP_ENV['cssRoot']=$APP_ENV['baseUrl'].'css/';

include APP_ROOT.'./config/lang.config.php';
if(!isset($supportLangList)) $APP_ENV['supportLangList']=array();
else{
	$APP_ENV['supportLangList']=$supportLangList;
	unset($supportLangList);
}
include APP_ROOT.'./include/global.func.php';

@include  APP_ROOT.'./config/autoload.config.php';

if(isset($autoloadJs)) 
{
	foreach($autoloadJs as $js) loadJs($js);
	unset($autoloadJs);
}

if(isset($autoloadCss)) 
{
	foreach($autoloadCss as $Css) loadCss($Css);
	unset($autoloadCss);
}
//------------------------------------
require APP_ROOT.'/include/templatePaserLib.php';



if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	exit('Request tainting attempted.');
}

foreach($_COOKIE as $_key => $_value) $_key{0} != '_' && $_COOKIE[$_key] = escapeSpecialChars($_value);
foreach($_POST as $_key => $_value) $_key{0} != '_' && $_POST[$_key] = escapeSpecialChars($_value);
foreach($_GET as $_key => $_value) $_key{0} != '_' && $_GET[$_key] = escapeSpecialChars($_value);

@include APP_ROOT.'./config/db.config.php';
if(!empty($activeGroup) && is_file(APP_ROOT.'./include/dbdrivers/'.$db[$activeGroup]['dbdriver'].'/db_driver.class.php')){
	include APP_ROOT.'./include/dbdrivers/'.$db[$activeGroup]['dbdriver'].'/db_driver.class.php';

	$APP_ENV['db_settings']=$db;
	$APP_ENV['current_db_setting']=$activeGroup;
	$APP_ENV['db_conection_pool']=array();
	
	$APP_ENV['db_conection_pool'][$APP_ENV['current_db_setting']]= new dbstuff;
	$APP_ENV['db_conection_pool'][$APP_ENV['current_db_setting']]->connect($db[$activeGroup]['hostname'],$db[$activeGroup]['username'],$db[$activeGroup]['password'], $db[$activeGroup]['database'], $db[$activeGroup]['pconnect']);
	
	$APP_ENV['db']=$APP_ENV['db_conection_pool'][$APP_ENV['current_db_setting']];
	
	unset($db);
	include APP_ROOT.'./include/CSearcher.class.php';
}
unset($activeGroup);



include APP_ROOT.'./config/system.config.php';

$APP_ENV['default_url_rewrite_ext']=$default_url_rewrite_ext;
$APP_ENV['url_rewrite']=$url_rewrite;
$APP_ENV['url_rewrite_directory_name']=$url_rewrite_directory_name;
unset($url_rewrite);
unset($default_url_rewrite_ext);

if(isset($time_zone)) date_default_timezone_set($time_zone);
$APP_ENV['time_zone']=$time_zone;
unset($time_zone);
$APP_ENV['debug']=$debug;
$APP_ENV['defaultRequestController']=$defaultRequestController;
$APP_ENV['defaultRequestMethod']=$defaultRequestMethod;
unset($defaultRequestController);
unset($defaultRequestMethod);
include APP_ROOT.'./config/application.settings.php';
if(isset($settings)&&is_array($settings)) foreach($settings as $key => $value) $APP_ENV['settings'][$key]=$value;
unset($settings);

?>