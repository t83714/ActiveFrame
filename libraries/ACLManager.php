<?php
loadException('CACLException');
class ACLManager
{
	const LOGIN_FAILED_TIMES=5;
	
	public static function get_allowed_postions($path,$controller_name,$method_name)
	{
		$s=new CSearcher('allowed_functions');
		$s['path']=$path;
		$s['controller_name']=$controller_name;
		$s['method_name']=$request_method;
		$permissions=$s->fetchPlainArray();
		if(empty($permissions)) return array();
		$role_ids=array();
		$positions=array();
		foreach($permissions as $permission) {
			if(!isset($role_ids[$permission['role_id']])) $positions=array_merge($positions,PositionManager::get_positions_by_role_id($permission['role_id']));
			else $role_ids[$permission['role_id']]=1;
		}
		return $positions;
	}
	
	public static function is_logged_in()
	{
		return is_uuid($_SESSION['people']['id']);
	}
	
	public static function request_permission($path=null,$controller_name=null,$request_method=null)
	{
		global $APP_ENV;
		if($path===NULL) $path=$APP_ENV['requestPath'];
		if($controller_name===NULL) $controller_name=$APP_ENV['controllerName'];
		if($request_method===NULL) $request_method=$APP_ENV['requestMethod'];
		$role_id=PositionManager::get_role_id();
		$s=new CSearcher('allowed_functions');
		$s['role_id']=$role_id;
		$s['path']=$path;
		$s['controller_name']=$controller_name;
		$s['method_name']=$request_method;
		if($s->getTotalRecordsNumber()>0) return;
		if($_SESSION['role']['default']==1) throw new CACLException(CACLException::LOGIN_REQUIRED);
		else throw new CACLException(CACLException::ACCESS_DENIED);
	}

	public static function is_valid_captcha($captcha=null) 
	{
		if(empty($captcha)) return false;
		$captcha=trim($captcha);
		$s=new CSearcher('sessions');
		$s['id']=SessionManager::$LSID;
		$s['captcha']=$captcha;
		return $s->getTotalRecordsNumber()>0;
	}
	
	public static  function captcha_required()
	{
		$s=new CSearcher('sessions');
		$s['id']=SessionManager::$LSID;
		$s['login_failed_times']->min=self::LOGIN_FAILED_TIMES;
		return $s->getTotalRecordsNumber()>0;
	}
	
	public static function login($username,$password,$captcha='')
	{
		if(self::captcha_required() && !self::is_valid_captcha($captcha)) throw new CACLException(CACLException::INVALID_CAPTCHA);
		$username=trim($username);
		$password=trim($password);
		if(!$username || !$password) throw new CACLException(CACLException::INVALID_USERNAME_OR_PASSWORD);
		
		$s=new CSearcher('users');
		$s['username']=$username;
		$s['password']=md5($password);
		$users=$s->fetchResult(1);
		if(empty($users)) throw new CACLException(CACLException::INVALID_USERNAME_OR_PASSWORD);
		$user=$users[0];
		$user['last_login_time']=time();
		$user['last_login_ip']=$_SERVER['REMOTE_ADDR'];
		
		SessionManager::reset_login_failed_times();
		
		if(!is_uuid($user['people_id'])) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		$user=$user->get_raw_data();
		$_SESSION['user']=$user;
		
		
		$s=new CSearcher('peoples');
		$s['id']=$user['people_id'];
		$peoples=$s->fetchPlainArray(1);
		if(empty($peoples)) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		$people=$peoples[0];
		$_SESSION['people']=$people;
		
		SessionManager::update_session_param('user_id',$user['id']);
		SessionManager::update_session_param('people_id',$people['id']);
		
		PositionManager::load_postions();
		PositionManager::update_runtime_position();
	}
	
	public static function login_by_user_id($user_id)
	{
		
		$s=new CSearcher('users');
		$s['id']=$user_id;
		$users=$s->fetchResult(1);
		if(empty($users)) throw new CACLException(CACLException::INVALID_USERNAME_OR_PASSWORD);
		$user=$users[0];
		
		if(!is_uuid($user['people_id'])) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		$user=$user->get_raw_data();
		$_SESSION['user']=$user;
		
		
		$s=new CSearcher('peoples');
		$s['id']=$user['people_id'];
		$peoples=$s->fetchPlainArray(1);
		if(empty($peoples)) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		$people=$peoples[0];
		$_SESSION['people']=$people;
		
		SessionManager::update_session_param('user_id',$user['id']);
		SessionManager::update_session_param('people_id',$people['id']);
		
		PositionManager::load_postions();
		PositionManager::update_runtime_position();
	}
	
	public static function login_by_people_id($people_id)
	{
		$user=array(
			'id'=>null,
			'people_id' => $people_id,
			'username' =>null,
			'password' =>null,
			'last_login_time' =>null,
			'last_login_ip' =>null
		);
		
		if(!is_uuid($user['people_id'])) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		$_SESSION['user']=$user;
		
		$s=new CSearcher('peoples');
		$s['id']=$user['people_id'];
		$peoples=$s->fetchPlainArray(1);
		if(empty($peoples)) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		$people=$peoples[0];
		$_SESSION['people']=$people;
		
		SessionManager::update_session_param('user_id',$user['id']);
		SessionManager::update_session_param('people_id',$people['id']);
		
		PositionManager::load_postions();
		PositionManager::update_runtime_position();
	}
	
	public static function login_by_api_key($api_key)
	{
		$s=new CSearcher('api_keys');
		$s['key']=$api_key;
		$api_keys=$s->fetchPlainArray(1);
		if(empty($api_keys)) throw new CACLException(CACLException::INVALID_API_KEY);
		if(!is_uuid($api_keys[0]['people_id']) || !is_uuid($api_keys[0]['position_id'])) throw new CACLException(CACLException::INVALID_ACCOUNT_INFO);
		self::login_by_people_id($api_keys[0]['people_id']);
		loadlib('PositionManager');
		PositionManager::change_runtime_position($api_keys[0]['position_id']);
	}
	
	public static function logout()
	{
		SessionManager::close();
	}
	
	public static function generate_captcha()
	{
		$rand_string = sprintf('%04d',mt_rand(0,9999));
		$_SESSION['session']['captcha']=$rand_string;
		$sql="UPDATE `sessions` SET `captcha`=:captcha WHERE `id`=:id LIMIT 1";
		$params=array(
			':captcha'=>$rand_string,
			':id'=>SessionManager::$LSID
		);
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$db->query($sql,$params);
		return $rand_string;
	}
	
	
}
?>