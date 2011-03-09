<?php
loadException('CSessionException');
class SessionManager
{
	public static $SESSION_COOKIE_PRE="fus_";
	public static $SESSION_DOMAIN=null;
	public static $SESSION_EXPIRE_TIME=86400; //--- seconds
	public static $PERMANENT_SESSION_EXPIRE_TIME=7776000;// three months
	public static $SESSION_PATH=null;
	public static $LSC_NAME='fus_LSID';
	public static $SSC_NAME='fus_SSID';
	public static $INSUB_NAME='fus_INSUB';
	
	public static $nLSID=null;
	public static $LSID=null;
	public static $SSID=null;
	public static $has_started=false;
	public static $has_loaded_settings=false;
	
	public static $INFINITE=3122064000;
	const TIMESTAMP2038=2147483647;
	
	public static function load_setting()
	{
		if(self::$has_loaded_settings) return;
		self::$has_loaded_settings=true;
		global $APP_ENV;
		if((int)$APP_ENV['settings']['session_expire_time']>0) self::$SESSION_EXPIRE_TIME=(int)$APP_ENV['settings']['session_expire_time'];
		if((int)$APP_ENV['settings']['permanent_session_expire_time']>0) self::$PERMANENT_SESSION_EXPIRE_TIME=(int)$APP_ENV['settings']['permanent_session_expire_time'];
		if(trim($APP_ENV['settings']['session_domain'])!='') self::$SESSION_DOMAIN=$APP_ENV['settings']['session_domain'];
		if(trim($APP_ENV['settings']['session_cookie_pre'])!='') self::$SESSION_COOKIE_PRE=$APP_ENV['settings']['session_cookie_pre'];
		if(trim($APP_ENV['settings']['session_path'])!='') self::$SESSION_PATH=$APP_ENV['settings']['session_path'];
		else self::$SESSION_PATH=preg_replace('/\/[^\/]*(\?.*)*$/i','',$_SERVER['REQUEST_URI']).'/';
		self::$LSC_NAME=self::$SESSION_COOKIE_PRE.'LSID';
		self::$SSC_NAME=self::$SESSION_COOKIE_PRE.'SSID';
		self::$INSUB_NAME=self::$SESSION_COOKIE_PRE.'INSUB';
	}
	
	public static function start()
	{

		if(self::$has_started) return;
		self::$has_started=true;
	
		self::load_setting();
		
		session_set_cookie_params(0,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,true);

		session_name(self::$SSC_NAME);
		
		session_start();
		$now=time();
		$lsid=trim((string)$_COOKIE[self::$LSC_NAME]);
		if(!self::valid_session_id($lsid)) $lsid=trim($_SESSION['LSID']);
		if(!self::valid_session_id($lsid)) $session=self::create_long_session();
		else{
			$s=new CSearcher('sessions');
			$s['id']=$lsid;
			$records=$s->fetchResult(1);
			if(empty($records)) $session=self::create_long_session();
			else{
				$session=$records[0];
				if(self::is_session_timeout($session,$now) || self::is_session_expired($session,$now)) {///expirerd!
					$session->delete();
					$session->commit();
					$session=self::create_long_session();
				}else{
					global $APP_ENV;
					$session['access_time']=$now;
					$session['ip_address']=$_SERVER['REMOTE_ADDR'];
					$session['access_path']=$APP_ENV['requestPath'];
					$session['access_controller']=$APP_ENV['controllerName'];
					$session['access_method']=$APP_ENV['requestMethod'];
					$session->commit();
				}
			}
		}
		if($lsid!=$session['id']) {
			$lsid=$session['id'];
			setcookie(self::$LSC_NAME,$lsid,0,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,true);
			$_COOKIE[self::$LSC_NAME]=$lsid;
		}
		self::$LSID=$lsid;
		$_SESSION['LSID']=$lsid;
		if(!is_array($session) && get_class($session)=='CActiveRecord') $_SESSION['session']=$session->get_raw_data();
		else $_SESSION['session']=$session;
		self::$SSID=session_id();
		self::load_session_env();
		self::load_time_zone_setting();
	}
	
	public static function load_time_zone_setting()
	{

		$time_zone_id=$_SESSION['people']['time_zone_id'];
		if(!is_uuid($time_zone_id)) return;
		if(is_uuid($_SESSION['settings']['time_zone_id']) && $_SESSION['settings']['time_zone_id']==$time_zone_id) {
			$r=@date_default_timezone_set($_SESSION['settings']['time_zone']);
			if(!$r){
				unset($_SESSION['settings']['time_zone']);
				unset($_SESSION['settings']['time_zone_id']);
			}
			return;
		}
		$s=new CSearcher('time_zones');
		$s['id']=$time_zone_id;
		$time_zones=$s->fetchPlainArray(1);
		if(empty($time_zones)) return;
		$time_zone=$time_zones[0]['area'].'/'.$time_zones[0]['location'];
		$r=@date_default_timezone_set($time_zone);
		if(!$r) return;
		$_SESSION['settings']['time_zone']=$time_zone;
		$_SESSION['settings']['time_zone_id']=$time_zone_id;
	}
	
	private static function is_session_expired($session,$now=null)
	{
		if($session['session_expire_time']==0) return false;
		if($now===null) $now=time();
		return $session['session_expire_time']<$now-$session['access_time'];
	}
	
	private static function is_session_timeout($session,$now=null)
	{
		if($session['session_length']==0) return false;
		if($now===null) $now=time();
		return $session['session_length']<$now-$session['create_time'];
	}
	
	private static function clean()
	{
		$sql="DELETE FROM `sessions` WHERE (`session_expire_time`!=0 AND (`session_expire_time`+`access_time`<UNIX_TIMESTAMP()) ) OR (`session_length`!=0 AND `session_length`!=-1 AND (`session_length`+`create_time`<UNIX_TIMESTAMP()))";
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$db->query($sql);
	}
	
	private static function create_long_session()
	{
		$session=new CActiveRecord('sessions');
		$session['id']=uuid();
		$now=time();
		$session['create_time']=$now;
		$session['session_length']='0';
		$session['session_expire_time']=self::$SESSION_EXPIRE_TIME;
		$session['access_time']=$now;
		$session['ip_address']=$_SERVER['REMOTE_ADDR'];
		global $APP_ENV;
		$session['access_path']=$APP_ENV['requestPath'];
		$session['access_controller']=$APP_ENV['controllerName'];
		$session['access_method']=$APP_ENV['requestMethod'];
		$session->commit();
		$_SESSION=array();
		return $session->get_raw_data();
	}
	
	public static function valid_session_id($session_id)
	{
		if(strlen($session_id) != 36) return false;
		if(preg_match("/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/i", $session_id)) return true;
		return false;
	}
	
	public static function reload_session_env()
	{
		if(!self::$has_started) throw new CSessionException(CSessionException::SESSION_NOT_STARTED);
		if(!self::valid_session_id(self::$LSID)) throw new CSessionException(CSessionException::INVALID_LSID);
		if(!empty($_SESSION['session']['user_id']) && is_uuid($_SESSION['user']['user_id'])) {
			$s=new CSearcher('users');
			$s['id']=$_SESSION['session']['user_id'];
			$users=$s->fetchPlainArray(1);
			if(!empty($users)){
				$_SESSION['user']=$users[0];
				if($_SESSION['session']['people_id']!=$_SESSION['user']['people_id'] && is_uuid($_SESSION['user']['people_id'])){
					$_SESSION['session']['people_id']=$_SESSION['user']['people_id'];
					self::update_session_param('people_id',$_SESSION['user']['people_id']);
				}
			}
		}
		if(!empty($_SESSION['session']['people_id']) && is_uuid($_SESSION['session']['people_id'])){
			$s=new CSearcher('peoples');
			$s['id']=$_SESSION['session']['people_id'];
			$peoples=$s->fetchPlainArray(1);
			if(!empty($peoples)) $_SESSION['people']=$peoples[0];
		}
		PositionManager::load_postions();
		PositionManager::update_runtime_position();
	}
	
	public static function update_session_param($field,$value)
	{
		$field=(string)$field;
		if(empty($field)) return;
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$sql_param=array(':field'=>$value,':id'=>self::$LSID);
		$sql="UPDATE sessions SET `$field`=:field WHERE `id`=:id";
		$db->query($sql,$sql_param);
	}
	
	public static function increase_login_failed_times()
	{
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$sql_param=array(':id'=>self::$LSID);
		$sql="UPDATE sessions SET `login_failed_times`=`login_failed_times`+1 WHERE `id`=:id";
		$db->query($sql,$sql_param);
		$_SESSION['session']['login_failed_times']+=1;
	}
	
	public static function reset_login_failed_times()
	{
		global $APP_ENV;
		$db=$APP_ENV['db'];
		$sql_param=array(':id'=>self::$LSID);
		$sql="UPDATE sessions SET `login_failed_times`=0 WHERE `id`=:id";
		$db->query($sql,$sql_param);
		$_SESSION['session']['login_failed_times']=0;
	}
	
	public static function load_session_env()
	{
		if(!is_uuid($_SESSION['position']['id'])) self::reload_session_env();
	}
	
	public static function set_session_length($length)
	{
		if(!self::$has_started) throw new CSessionException(CSessionException::SESSION_NOT_STARTED);
		if(!self::valid_session_id(self::$LSID)) throw new CSessionException(CSessionException::SESSION_NOT_STARTED);
		$length=(int)$length;
		if($length==-1){
			$length=self::$INFINITE;
			self::update_session_param('session_length',$length);
			self::update_session_param('session_expire_time',self::$PERMANENT_SESSION_EXPIRE_TIME);
		}else {
			$length=abs($length);
			self::update_session_param('session_length',$length);
			
		}
		$exp=time()+$length;
		$exp=$exp>self::TIMESTAMP2038 ? self::TIMESTAMP2038:$exp;
		setcookie(self::$LSC_NAME,self::$LSID,$exp,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,true);
	}
	
	public static function close()
	{
		setcookie(self::$LSC_NAME,$lsid,time()-3600,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,true);
		setcookie(self::$SSC_NAME,$lsid,time()-3600,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,true);
		$_SESSION=array();
		session_destroy();
		$s=new CSearcher('sessions');
		$s['parent_session_id']=self::$LSID;
		$s->delete();
		$s=new CSearcher('sessions');
		$s['id']=self::$LSID;
		$s->delete();
	}

	
	public static function inject_session_by_people_id($people_id,$position_id=null)
	{
		$LSID=self::$LSID;
		$SSID=self::$SSID;
		$path=self::$SESSION_PATH;
		session_write_close();
		unset($_COOKIE[self::$LSC_NAME]);
		unset($_COOKIE[self::$SSC_NAME]);
		$_SESSION=array();
		
		$nSSID=md5(uniqid(mt_rand(), true));
		session_id($nSSID);
		self::$SESSION_PATH.='subsys/';
		SessionManager::$has_started=false;
		SessionManager::start();
		$nLSID=self::$LSID;
		SessionManager::update_session_param('parent_session_id',$LSID);
		setcookie(self::$INSUB_NAME,1,0,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,false);
		
		loadlib('ACLManager');
		ACLManager::login_by_people_id($people_id);
		
		if(is_uuid($position_id)){
			loadlib('PositionManager');
			PositionManager::change_runtime_position($position_id);
		}
		
		session_write_close();
		$_SESSION=array();
		$_COOKIE[self::$LSC_NAME]=$LSID;
		$_COOKIE[self::$SSC_NAME]=$SSID;
		session_id($SSID);
		self::$SESSION_PATH=$path;
		SessionManager::$has_started=false;
		SessionManager::start();
		self::$nLSID=$nLSID;
		global $APP_ENV;
		URLUtil::redirect($APP_ENV['baseUrl'].'subsys/');
		exit();
	}
	
	public static function inject_session_by_user_id($user_id)
	{
		$LSID=self::$LSID;
		$SSID=self::$SSID;
		$path=self::$SESSION_PATH;
		session_write_close();
		unset($_COOKIE[self::$LSC_NAME]);
		unset($_COOKIE[self::$SSC_NAME]);
		$_SESSION=array();
		
		$nSSID=md5(uniqid(mt_rand(), true));
		session_id($nSSID);
		self::$SESSION_PATH.='subsys/';
		SessionManager::$has_started=false;
		SessionManager::start();
		$nLSID=self::$LSID;
		SessionManager::update_session_param('parent_session_id',$LSID);
		setcookie(self::$INSUB_NAME,1,0,self::$SESSION_PATH,self::$SESSION_DOMAIN,false,false);
		
		loadlib('ACLManager');
		ACLManager::login_by_user_id($user_id);
		
		session_write_close();
		$_SESSION=array();
		$_COOKIE[self::$LSC_NAME]=$LSID;
		$_COOKIE[self::$SSC_NAME]=$SSID;
		session_id($SSID);
		self::$SESSION_PATH=$path;
		SessionManager::$has_started=false;
		SessionManager::start();
		self::$nLSID=$nLSID;
		global $APP_ENV;
		URLUtil::redirect($APP_ENV['baseUrl'].'subsys/');
		exit();
	}
	
	public static function is_sub_session()
	{
		if($_COOKIE[self::$INSUB_NAME]==1) return true;
		else return false;
	}
	
	private function recover_session()
	{
		

	}
	
	public static function open_handler()
	{
		
	}
	
	public static function close_handler() 
	{
		
	}
	
	public static function read_handler()
	{
		
	}
	
	public static function write_handler()
	{
		
	}
	
	public static function destroy_handler()
	{
		
	}
	
	public static function gc_handler()
	{
		
	}
}
?>