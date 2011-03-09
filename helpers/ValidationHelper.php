<?php
class ValidationHelper
{
	
	public static function is_valid_email($email)
	{
		$email=strtolower(trim($email));
		if(empty($email)) return false;
		$s='/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/';
		$r=preg_match($s,$email);
		if(empty($r)) return false;
		
		$s='/^tba@|^noemail@|^nomail@|^no email@|^no@|^unknown@|^na@/i';
		$r=preg_match($s,$email);
		
		if($r) return false;
		else return true;
	}
}
?>