#!/usr/bin/php-cli
<?php
/**
 * Fix a problem for session
 * @author Jacky Jiang
 * @version 0.1.2
 */
if(!isset($argc)) exit('CLI Interface can only run from Command Line.');

define('APP_INTERFACE','CLI');
ob_start();

$_SERVER['SERVER_NAME']='localhost';
if(!empty($argv[1])) $_GET['path']=$argv[1];

define('APP_ROOT',dirname(__FILE__).'/');
define('IN_APP', true);
define('APP_CTR_ROOT',APP_ROOT.'application/controllers/');
define('PHP_CLOSE_TAG','?>');
try{
include APP_ROOT.'./include/common.inc.php';

include APP_ROOT.'./include/CController.class.php';

if(!class_exists('C404Exception')) loadException('C404Exception');

//-------------Route user Request-------------------------

	
if(!isset($_GET['path'])||$_GET['path']==='') 
{
	$APP_ENV['controllerName']=$APP_ENV['defaultRequestController'];
	$APP_ENV['controllerFile']=APP_CTR_ROOT.'C_'.$APP_ENV['controllerName'].'.php';
	$APP_ENV['requestMethod']=$APP_ENV['defaultRequestMethod'];
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
?>
