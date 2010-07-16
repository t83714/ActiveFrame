<?php   if (!defined('APP_ROOT')) exit('No direct script access allowed'); 
/**
* Modified By Jacky: fixed `cannot open file bug`
*
*/

/**
 * Force Download
 *
 * Generates headers that force a download to happen
 *
 * @access	public
 * @param	string	filename
 * @param	mixed	the data to be downloaded
 * @return	void
 */	
if ( ! function_exists('force_download'))
{
	function force_download($filename = '', $data = null)
	{
		
		if ($filename == '' || empty($data))
		{
			return FALSE;
		}
		
		if (FALSE === strpos($filename, '.'))
		{
			exit('Need specify file type');
			return FALSE;
		}
		$filename=str_replace(array(' ','\\','/',':','*','?','"','<','>','|'),'_',$filename);
		// Grab the file extension
		$x = explode('.', $filename);
		$extension = end($x);
		// Load the mime types
		@include_once(APP_ROOT.'config/mimes.php');

		// Set a default mime if we can't find it
		if ( ! isset($mimes[$extension]))
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}
		header('Content-Type: ' . $mime);
	  header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
   	header('Content-Disposition: attachment; filename="' . $filename . '"');
		// Generate the server headers
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
		{
 			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
		}
		else
		{
			header('Pragma: no-cache');
		}
		if(is_resource($data)){
			@fseek($data,0);
			@fpassthru($data);
			exit();
		}else exit($data);
	}
}


/* End of file download_helper.php */
/* Location: ./system/helpers/download_helper.php */