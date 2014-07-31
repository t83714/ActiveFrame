<?php
class C_welcome extends CController
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		/*--
		Load your lib or model to process reuqest
		--*/
		loadview('welcome',$data);
	}
}
?>