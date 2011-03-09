<?php
/**
 * Landing pages are:
 * 1> login page
 * 	 Only for annoymous users
 * 2> position selection page
 *  always select position=1 || !is_uuid(last_select_position)
 * 3> Landing Page
 * 4> First Menu
 *
 */
class URLUtil
{
	const DEFAULT_CHOOSE_POSITION_PAGE='?path=common_func/choose_position';
	const DEFAULT_LOGIN_PAGE='?path=common_func/login';
	
	public static function get_landing_page_url()
	{
		if(!is_uuid($_SESSION['position']['role_id'])) return self::DEFAULT_LOGIN_PAGE;
		else if(PositionManager::is_anonymous_user()) {
			if($_SESSION['role']['landing_page']) return $_SESSION['role']['landing_page'];
			try{
				$default_menu_link=MenuManager::get_default_url();
				if(!empty($default_menu_link)) return $default_menu_link;
				else return self::DEFAULT_LOGIN_PAGE;
			}catch (Exception $e){
				return self::DEFAULT_LOGIN_PAGE;
			}
		}else{
			//if( $_SESSION['people']['select_position_always']!=1 || SessionManager::is_sub_session() || count($_SESSION['positions'])<2) {
				if($_SESSION['role']['landing_page']) return $_SESSION['role']['landing_page'];
				return MenuManager::get_default_url();
			//}else return self::DEFAULT_CHOOSE_POSITION_PAGE;
		}
	}
	
	public static function parse_url($url)
	{
		$result=parse_url($url);
		if($result===false) return false;
		$default_info=array(
			'path' => '/',
			'controller_name' => $APP_ENV['defaultRequestController'],
			'method_name'  => $APP_ENV['defaultRequestMethod']
		);
		if(empty($result['query'])) return $default_info;
		parse_str($result['query'], $output);
		if(empty($output['path'])) return $default_info;
		
		global  $APP_ENV;
		$tmp_env=$APP_ENV;
		$tmp_get=$_GET['path'];
		
		$_GET['path']=$output['path'];
		paserControllerPath();
		
		$info=array(
			'path' => $APP_ENV['requestPath'],
			'controller_name' => $APP_ENV['controllerName'],
			'method_name'  => $APP_ENV['requestMethod']
		);
		$APP_ENV=$tmp_env;
		$_GET['path']=$tmp_get;
		return $info;
	}
	
	public static function is_same_location($a,$b)
	{
		if($a===null xor $b===null) return false;
		$info_a=self::parse_url($a);
		$info_b=self::parse_url($b);
		$r=$info_a['controller_name']==$info_b['controller_name'] && $info_a['path']==$info_b['path'] && $info_a['method_name']==$info_b['method_name'] ;
		return $r;
	}
	
	/**
	 * Remove query component from url
	 *
	 * @param string $url
	 * @param string int or array $name If array, will remove all componenets with names in array
	 * @return string
	 */
	
	public static function remove_component($url,$name)
	{
		if(empty($name)) return $url;
		$info=@parse_url($url);
		if($info===false) return $url;
		$query=$info['query'];
		if(!$info['query']) return $url;
		parse_str($info['query'],$q_arr);
		if(empty($q_arr)) return $url;
		if(!is_array($name)) unset($q_arr[$name]);
		else foreach ($name as $item) unset($q_arr[$item]);
		$info['query']=http_build_query($q_arr);
		$url=$info['scheme']?$info['scheme'].'://':'';
		$up=$info['user']? $info['user'].($info['pass']?':'.$info['pass']:''):'';
		$up=$up?$up.'@':'';
		$url.=$info['host']? $up.$info['host'].($info['port']?':'.$info['port']:'') : '';
		$url.=$info['path'];
		$url.=$info['query']?'?'.$info['query']:'';
		$url.=$info['fragment']?'#'.$info['fragment']:'';
		return $url;
	}
	
	public static function url_with_extra($url,$params)
	{
		if(empty($params) || !is_array($params)) return $url;
		
		$info=@parse_url($url);
		if($info===false) return $url;
		$query=$info['query'];
		if(!$info['query']) return $url;
		parse_str($info['query'],$q_arr);
		if(empty($q_arr)) return $url;
		$q_arr=array_merge($q_arr,$params);
		$info['query']=http_build_query($q_arr);
		$url=$info['scheme']?$info['scheme'].'://':'';
		$up=$info['user']? $info['user'].($info['pass']?':'.$info['pass']:''):'';
		$up=$up?$up.'@':'';
		$url.=$info['host']? $up.$info['host'].($info['port']?':'.$info['port']:'') : '';
		$url.=$info['path'];
		$url.=$info['query']?'?'.$info['query']:'';
		$url.=$info['fragment']?'#'.$info['fragment']:'';
		return $url;	
	}
	
	public static function url_with_continue($url,$continue=null,$msg=null)
	{
		if(!$msg && !$continue) return $url;
		if($continue) $params['continue']=$continue;
		if($msg) $params['msg']=$msg;
		return self::url_with_extra($url,$params);
	}
	
	public static function redirect_with_params($url,$params=null,$allow_redirect_self=false)
	{
		if(self::is_same_location($_SERVER['REQUEST_URI'],$url) && !$allow_redirect_self) return;
		$url=self::url_with_extra($url,$params);
		redirect($url,true);
	}
	
	public static function redirect($url,$continue=null,$msg=null,$allow_redirect_self=false)
	{
		if(self::is_same_location($url,$continue)) $continue=null;
		self::redirect_with_params($url,array(
			'continue' =>$continue,
			'msg'=>$msg
		),$allow_redirect_self);
	}
	
}
?>