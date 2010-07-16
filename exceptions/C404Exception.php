<?php
class C404Exception extends CAppException
{
	public $reason='';
	public $name='';
	public function __construct($reason,$name)
	{
		$this->reason=$reason;
		$this->name=$name;
	}
	public function handleException()
	{
		$data=array();
		
		$data['name']=$this->reason.'  '.$this->name;
		
		$data['title']='404 Page';

		loadview('404page',$data);
	}
	
}

?>