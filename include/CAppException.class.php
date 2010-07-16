<?php
class CAppException extends RuntimeException 
{
	public function clearAppEnv()
	{
		ob_clean();
		
	}
	
	
	
}
?>