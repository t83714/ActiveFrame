
function transsid($url, $tag = '', $wml = 0) {
	global $sid;
	$tag = stripslashes($tag);
	if(!$tag || (!preg_match("/^(http:\/\/|mailto:|#|javascript)/i", $url) && !strpos($url, 'sid='))) {
		if($pos = strpos($url, '#')) {
			$urlret = substr($url, $pos);
			$url = substr($url, 0, $pos);
		} else {
			$urlret = '';
		}
		$url .= (strpos($url, '?') ? ($wml ? '&amp;' : '&') : '?').'sid='.$sid.$urlret;
	}
	return $tag.$url;
}

function typeselect($curtypeid = 0, $special = '', $onchange = '', $modelid = 0) {
	global $fid, $sid, $extra;
	$onchange = $onchange ? $onchange : "onchange=\"ajaxget('post.php?action=threadtypes&typeid='+this.options[this.selectedIndex].value+'&fid=$fid&sid=$sid', 'threadtypes', 'threadtypeswait')\"";
	if($threadtypes = $GLOBALS['forum']['threadtypes']) {
		$html = '<select name="typeid" '.(!$special ? $onchange : '').'><option value="0">&nbsp;</option>';
		foreach($threadtypes['types'] as $typeid => $name) {
			if(!$special || $special == 'disabled' || !$threadtypes['special'][$typeid]) {
				$typehtml = '<option value="'.$typeid.'" '.($curtypeid == $typeid ? 'selected="selected"' : '').' '.($threadtypes['special'][$typeid] ? 'class="special"' : '').'>'.strip_tags($name).'</option>';
				$html .= $modelid ? ($threadtypes['modelid'][$typeid] == $modelid ? $typehtml : '') : $typehtml;
			}
		}
		$html .= '</select><span id="threadtypeswait"></span>'.($special === 'disabled' ? '<input type="hidden" name="typeid" value="'.$curtypeid.'" />' : '');
		return $html;
	} else {
		return '';
	}
}


function writelog($file, $log) {
	global $timestamp, $_DCACHE;
	$yearmonth = gmdate('Ym', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
	$logdir = APP_ROOT.'./forumdata/logs/';
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);

		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		$log = is_array($log) ? $log : array($log);
		foreach($log as $tmp) {
			fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
		}
		fclose($fp);
	}
}

function seccodeconvert(&$seccode) {
	global $seccodedata, $charset;
	$seccode = substr($seccode, -6);
	if($seccodedata['type'] == 1) {
		include_once language('seccode');
		$len = strtoupper($charset) == 'GBK' ? 2 : 3;
		$code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
		$seccode = '';
		for($i = 0; $i < 2; $i++) {
			$seccode .= substr($lang['chn'], $code[$i] * $len, $len);
		}
	} else {
		$s = sprintf('%04s', base_convert($seccode, 10, 24));
		$seccode = '';
		$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
		for($i = 0; $i < 4; $i++) {
			$unit = ord($s{$i});
			$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
		}
	}
}




function site() {
	return $_SERVER['HTTP_HOST'];
}

function referer($default = '') {
	global $referer, $indexname;

	$default = empty($default) ? $indexname : '';
	if(empty($referer) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
		$referer = preg_replace("/([\?&])((sid\=[a-z0-9]{6})(&|$))/i", '\\1', $GLOBALS['_SERVER']['HTTP_REFERER']);
		$referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
	} else {
		$referer = dhtmlspecialchars($referer);
	}

	if(!preg_match("/(\.php|[a-z]+(\-\d+)+\.html)/", $referer) || strpos($referer, 'logging.php')) {
		$referer = $default;
	}
	return $referer;
}





function getrobot() {
	if(!defined('IS_ROBOT')) {
		$kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
		$kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
		if(preg_match("/($kw_browsers)/", $_SERVER['HTTP_USER_AGENT'])) {
			define('IS_ROBOT', FALSE);
		} elseif(preg_match("/($kw_spiders)/", $_SERVER['HTTP_USER_AGENT'])) {
			define('IS_ROBOT', TRUE);
		} else {
			define('IS_ROBOT', FALSE);
		}
	}
	return IS_ROBOT;
}

