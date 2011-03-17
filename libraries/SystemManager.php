<?php
class SystemManager
{
	
	static public function dispatch_request($method_name,$controller_name=null,$path=null)
	{
		global $APP_ENV;
		
		$previous_path=$APP_ENV['requestPath'];
		$previous_controller_name=$APP_ENV['controllerName'];
		$previous_method_name=$APP_ENV['requestMethod'];
		$previous_controller_file=substr($previous_path,1).'/'.'C_'.$previous_controller_name.'.php';
		if(strpos($previous_controller_file,'/')===0) $previous_controller_file=substr($previous_controller_file,1);
		$previous_controller_file=APP_CTR_ROOT.$previous_controller_file;
		
		if(!$path) $path=$previous_path;
		if(!$controller_name) $controller_name=$previous_controller_name;
		$controller_file=substr($path,1).'/'.'C_'.$controller_name.'.php';
		if(strpos($controller_file,'/')===0) $controller_file=substr($controller_file,1);
		$controller_file=APP_CTR_ROOT.$controller_file;
		
		$APP_ENV['requestPath']=$path;
		$APP_ENV['controllerName']=$controller_name;
		$APP_ENV['requestMethod']=$method_name;
		$APP_ENV['controllerFile']=$controller_file;
		

		try{
				if($controller_name==$previous_controller_name && $path!=$previous_path) throw new Exception('Cannot internally redirect a request to a controller which has same name with current one.');
				
				$tmp_class_name='C_'.$APP_ENV['controllerName'];
				if(class_exists($tmp_class_name))
				{
					$APP_ENV['controller']=new $tmp_class_name;
				}else{
					if(!is_file($APP_ENV['controllerFile'])) throw new C404Exception('C_'.$APP_ENV['controllerName'].'.php',C404Exception::CONTROLLER);
					include $APP_ENV['controllerFile'];
					if(!class_exists('C_'.$APP_ENV['controllerName'])) throw new C404Exception($APP_ENV['controllerName'],C404Exception::CONTROLLER);
					$APP_ENV['controller']=new $tmp_class_name;
				}
				
				if(!method_exists($APP_ENV['controller'],$APP_ENV['requestMethod'])) throw new C404Exception($APP_ENV['requestMethod'],C404Exception::METHOD);
				call_user_func(array($APP_ENV['controller'],$APP_ENV['requestMethod']));
			
		}catch (Exception $e){
				if(method_exists($e,'handleException')) 
				try {
					$e->handleException();
				}catch(Exception $e)
				{
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
				else echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		exit();	
	}
	
}
?>