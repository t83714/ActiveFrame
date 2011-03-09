<?php
/**
 * Fix a problem for session
 * @author Jacky Jiang
 * @version 0.1.2 Var
 */
ob_start();

define('APP_ROOT',dirname(__FILE__).'/');
define('IN_APP', true);
define('APP_CTR_ROOT',APP_ROOT.'application/controllers/');
define('PHP_CLOSE_TAG','?>');
try{
include APP_ROOT.'./include/common.inc.php';


include APP_ROOT.'./include/CController.class.php';


header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 

if(!class_exists('C404Exception')) loadException('C404Exception');

//-------------Route user Request-------------------------

	
if(!isset($_GET['path'])||$_GET['path']==='') 
{
	$APP_ENV['controllerName']=$APP_ENV['defaultRequestController'];
	$APP_ENV['controllerFile']=APP_CTR_ROOT.'C_'.$APP_ENV['controllerName'].'.php';
	$APP_ENV['requestMethod']=$APP_ENV['defaultRequestMethod'];
	$APP_ENV['requestPath']='/';
}else
{
	if(preg_match('/^(\w+)(\/\w+)*$/',$_GET['path'])==0) throw new C404Exception($_GET['path'],C404Exception::INVALID_URL);
	else paserControllerPath();
}

if(!is_file($APP_ENV['controllerFile'])) throw new C404Exception('C_'.$APP_ENV['controllerName'].'.php',C404Exception::CONTROLLER);
include $APP_ENV['controllerFile'];
if(!class_exists('C_'.$APP_ENV['controllerName'])) throw new C404Exception($APP_ENV['controllerName'],C404Exception::CONTROLLER);

	$tmp_class_name='C_'.$APP_ENV['controllerName'];
	$APP_ENV['controller']=new $tmp_class_name;
	if(!method_exists($APP_ENV['controller'],$APP_ENV['requestMethod'])) throw new C404Exception($APP_ENV['requestMethod'],C404Exception::METHOD);
	call_user_func(array($APP_ENV['controller'],$APP_ENV['requestMethod']));
	
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