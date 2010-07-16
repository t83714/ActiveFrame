<?php
/**
 * Fix a problem for session
 * @author Jacky Jiang
 * @version 0.1.2
 */
ob_start();

session_set_cookie_params(0,preg_replace('/\/[^\/]*$/i','',$_SERVER['SCRIPT_NAME']).'/');
session_start();

define('APP_ROOT',dirname(__FILE__).'/');
define('IN_APP', true);
define('APP_CTR_ROOT',APP_ROOT.'application/controllers/');
define('PHP_CLOSE_TAG','?>');

include APP_ROOT.'./include/common.inc.php';


include APP_ROOT.'./include/CController.class.php';


header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
/*
$expFiles=scandir(APP_ROOT.'./application/exceptions');
foreach($expFiles as $exp) 
	if(preg_match('/\.php$/',$exp)) include APP_ROOT.'./application/exceptions/'.$exp;
unset($expFiles);
*/
if(!class_exists('C404Exception')) loadException('C404Exception');

//-------------Route user Request-------------------------
try{
	
if(!isset($_GET['path'])||$_GET['path']==='') 
{
	$APP_ENV['controllerName']=$APP_ENV['defaultRequestController'];
	$APP_ENV['controllerFile']=APP_CTR_ROOT.'C_'.$APP_ENV['controllerName'].'.php';
	$APP_ENV['RequestMethod']=$APP_ENV['defaultRequestMethod'];
}else
{
	if(preg_match('/^(\w+)(\/\w+)*$/',$_GET['path'])==0) throw new C404Exception('incorrectUrl',$_GET['path']);
	else paserControllerPath();
}

if(!is_file($APP_ENV['controllerFile'])) throw new C404Exception('file','C_'.$APP_ENV['controllerName'].'.php');
include $APP_ENV['controllerFile'];
if(!class_exists('C_'.$APP_ENV['controllerName'])) throw new C404Exception('class','C_'.$APP_ENV['controllerName']);

	eval('$APP_ENV[\'controller\']=new '.'C_'.$APP_ENV['controllerName'].';');
	if(!method_exists($APP_ENV['controller'],$APP_ENV['RequestMethod'])) throw new C404Exception('method',$APP_ENV['RequestMethod']);
	eval('$APP_ENV[\'controller\']->'.$APP_ENV['RequestMethod'].'();');	
	
}catch (Exception $e)
{
	if(method_exists($e,'handleException')) 
	try {
		$e->handleException();
	}catch(Exception $e)
	{
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	else echo 'Caught exception: ',  $e->getMessage(), "\n";
}
//----------------------Router User Request End--------------------
//foreach($APP_ENV['debugInfo']['sqlStack'] as $s) echo $s.'<br/>';
?>