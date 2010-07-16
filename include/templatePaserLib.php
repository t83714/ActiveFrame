<?php
/**
 * @author Jacky Jiang
 * @version 0.1.1
 */

function mkDirCursive($dir)
{
	if(PHP_VERSION>=5) return mkdir($dir,0777,true);
	if($dir==='' || $dir==='\\' || $dir==='/') return true;
	
	$pos=min(strpos($dir,'/'),strpos($dir,'\\'));
	if($pos===false) return mkdir($baseDir.$dir);
	if($pos===0) return mkdir(substr($dir,1));
	
}

function parse_template($file,$ifWidget=false) {
	global $APP_ENV,$language;
	$nest = 5;
	$tplBaseDir = $APP_ENV['viewRoot'].$APP_ENV['curView'].'/';
	if($ifWidget==false) $tplfile=$tplBaseDir.$file.'.tpl.htm';
	else $tplfile=$tplBaseDir.$file.'.widget.php';
	$objBaseDir = $APP_ENV['tempRoot'].'viewCacheData/'.$APP_ENV['curView'].'/';
	if($ifWidget==false) $objfile=$objBaseDir.$file.'.tpl.php';
	else $objfile=$objBaseDir.$file.'.widget.php';

	$objDirArray=explode('/',$file);
	$num=count($objDirArray);
	if($num<2) $objdir=$objBaseDir;
	else{
		unset($objDirArray[$num-1]);
		$objdir=$objBaseDir.implode('/',$objDirArray);
	}
	
	if(!@$fp = fopen($tplfile, 'r')) {
		if($ifWidget==false) exit('Current view file '.$APP_ENV['curView'].'/'.$file.'.tpl.htm not found or have no access!');
		else exit('Current widget file '.$APP_ENV['curView'].'/'.$file.'.widget.php not found or have no access!');
	}
	$template = @fread($fp, filesize($tplfile));
	fclose($fp);
	$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9\-\.\[\]_\"\'\$\x7f-\xff]+\])*)"; 
	$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
	$function_regexp="([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
	
	$match=array();
	$r=preg_match("/\<\?php[\s\S]*\?\>/is",$template,$match);
	if($r==1)
	{
		$phpCodeInTpl=$match[0];
		$template = str_replace($match[0],'', $template);
	}
	
	
	$template=str_replace('<?',"<?='<?'?>",$template);
	
	$bLoadLanguageFile=false;
	if(preg_match('/\{(lang|langvar)\s+[^\}]+\}/is',$template)) 
	{
		$langaugeFiles=array($file);
		$bLoadLanguageFile=true;
	}
	

	
	$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
	$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
	
	if(preg_match_all('/\s*\{uselanguagefile\s+(.+?)\}\s*/is',$template,$matches)) 
	{
		$bLoadLanguageFile=true;
		$match_times=count($matches);
		foreach($matches[1] as $match) $langaugeFiles[]=$match;
		$template = preg_replace("/\s*\{uselanguagefile\s+(.+?)\}\s*/is", '', $template);
	}
	
	
	$template = preg_replace("/\{lang\s+(.+?)\}/is", "<?=languagevar('\\1','$file')?>", $template);
	
	$template = str_replace("{LF}", "<?=\"\\n\"?".">", $template);

	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?'.'>')", $template);
	$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template); 
	
	$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_\/]+)\}[\n\r\t]*/is", "\n<? include template('\\1'); ?>\n", $template);
	
	$template = preg_replace("/\s*\{template\s+(.+?)\}\s*/is", "\n<?include template('\\1'); ?>\n", $template);
	
	$template = preg_replace("/\s*\{loadformatter\s+(.+?)\}\s*/is", "\n<? loadformatter('\\1'); ?>\n", $template);
	
	$template = preg_replace("/\s*\{widget\s+([^\s]+?)\}\s*/is", "\n<?=loadwidget('\\1'); ?>\n", $template);
	
	$template = preg_replace("/\s*\{eval\s+(.+?)\}\s*/ies", "stripvtags('\n<? \\1  '.PHP_CLOSE_TAG.'\n','')", $template);
	$template = preg_replace("/\s*\{echo\s+(.+?)\}\s*/ies", "stripvtags('\n<? echo \\1; '.PHP_CLOSE_TAG.'\n','')", $template);
	$template = preg_replace("/\s*\{elseif\s+(.+?)\}\s*/ies", "stripvtags('\n<? } elseif(\\1) { '.PHP_CLOSE_TAG.'\n','')", $template);
	$template = preg_replace("/\s*\{else\}\s*/is", "\n<? } else { ".PHP_CLOSE_TAG."\n", $template);

	for($i = 0; $i < $nest; $i++) {
		$template = preg_replace("/\s*\{loop\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies", "stripvtags('\n<? if(is_array(\\1)) { foreach(\\1 as \\2) { '.PHP_CLOSE_TAG,'\n\\3\n<? } } '.PHP_CLOSE_TAG.'\n')", $template);
		$template = preg_replace("/\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies", "stripvtags('\n<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { '.PHP_CLOSE_TAG,'\n\\4\n<? } } '.PHP_CLOSE_TAG.'\n')", $template);
		$template = preg_replace("/\s*\{if\s+(.+?)\}\s*(.+?)\s*\{\/if\}\s*/ies", "stripvtags('\n<? if(\\1) { '.PHP_CLOSE_TAG,'\n\\2\n<? } '.PHP_CLOSE_TAG.'\n')", $template);
	}
	$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1".PHP_CLOSE_TAG, $template);

	
	$template	= preg_replace('/\{langvar\s+\<\?=(.+?)\?\>\}/is', "<?=languagevar(\\1,'$file')?>", $template);
	
	$template= preg_replace('/{widget\s+(\S+)\s+(\S+)\s*}/ies', "stripvtags('\n<?=loadwidget(\\'\\1\\',\\2);'.PHP_CLOSE_TAG.'\n','')", $template);

	//---format var--support 0~3 arguments
	$template= preg_replace('/{\<\?=(.+?)\?\>\s+(\S[^}]*)}/ies', "stripvtags('{\\1 \\2}','')", $template);//---recover var format
	

	$template= preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]*)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/is', "<?=\\2(\\1)?>", $template);
	$template= preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]*)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+([^\s}]+)}/is', "<?=\\2(\\1,\\3)?>", $template);
	$template= preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]*)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+([^\s}]+)\s+([^\s}]+)}/is',"<?=\\2(\\1,\\3,\\4)?>", $template);
	$template= preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]*)\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+([^\s}]+)\s+([^\s}]+)\s+([^\s}]+)}/is', "<?=\\2(\\1,\\3,\\4,\\5)?>", $template);
	
	if($bLoadLanguageFile==true) 
	{
		foreach($langaugeFiles as $langaugeFile) $template = "<? languagestack('$file','$langaugeFile'); ".PHP_CLOSE_TAG.$template;
	}
	
	if(!empty($phpCodeInTpl)) $template =$phpCodeInTpl.$template;
	
	$template = "<? if(!defined('APP_ROOT')) exit('Access Denied'); ".PHP_CLOSE_TAG.$template;
	
	
	if(!@$fp = fopen($objfile, 'w')) {
		mkDirCursive($objdir);
		if(!@$fp = fopen($objfile, 'w')) exit('Aplication Temp Data Directory have no access!');
	}

	$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

	$template = preg_replace("/ \?\>[\n\r]*\<\?=/s", ";\n echo ", $template);
	flock($fp, 3);
	fwrite($fp, $template);
	fclose($fp);
	@chmod($objfile,0777);
}


function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\]/s", "['\\1']", $var));
}

function languagestack($file,$loadFile=null)
{
	global $APP_ENV;
	if(!isset($loadFile)) $loadFile=$file;
	if(!isset($APP_ENV['languageDictionary'][$file])) $APP_ENV['languageDictionary'][$file]=array();
	if(!isset($APP_ENV['languageDictionaryDefault'][$file])) $APP_ENV['languageDictionaryDefault'][$file]=array();
	$langBaseDir = $APP_ENV['langRoot'].$APP_ENV['curView'].'/';
	$langFile=$langBaseDir.$APP_ENV['curLang'].'/'.$loadFile.'.lang.php';
	$langFileDefault=$langBaseDir.'defaultLang'.'/'.$loadFile.'.lang.php';
	if(is_file($langFile)) 
	{
		include $langFile;
		$APP_ENV['languageDictionary'][$file]=array_merge($lang,$APP_ENV['languageDictionary'][$file]); //---the priority of defualt language file is higher
	}
	if(is_file($langFileDefault)) 
	{
		include $langFileDefault;
		$APP_ENV['languageDictionaryDefault'][$file]=array_merge($lang,$APP_ENV['languageDictionaryDefault'][$file]); //---the priority of defualt language file is higher
	}
	
}

function languagevar($var,$file='') 
{
	global $APP_ENV;
	if(isset($APP_ENV['languageDictionary'][$file][$var])) return $APP_ENV['languageDictionary'][$file][$var];
	elseif(isset($APP_ENV['languageDictionaryDefault'][$file][$var])) return $APP_ENV['languageDictionaryDefault'][$file][$var];
	elseif(isset($APP_ENV['globalLanguageVar'][$var])) return $APP_ENV['globalLanguageVar'][$var];
	else return "!{$var}!";
}


function stripvtags($expr, $statement) {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\[\]\"\'\$\x7f-\xff]*)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}

?>