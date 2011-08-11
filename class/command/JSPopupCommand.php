<?php

class JSPopupCommand extends Command {

	private $viewCommand;

	private $height;
	private $width;
	private $label;
	private $title;
	private $windowName;

	public function setViewCommand(Command $cmd){
		$this->viewCommand = $cmd;
	}

	public function setHeight($height){
		$this->height = $height;
	}

	public function setWidth($width){
		$this->width = $width;
	}

	public function setLabel($lbl){
		$this->label = $lbl;
	}

	public function setTitle($title){
		$this->title = $title;
	}

	public function setWindowName($name){
		$this->winodwName = $name;
	}

	public function getRequestVars()
	{
		return $this->viewCommand->getRequestVars();
	}

	public function getLink($text)
	{
		$vars = $this->getRequestVars();

		$this->setLabel($text);
		
		$js = array();
		$js['width']       = $this->width;
		$js['height']      = $this->height;
		$js['label']       = $this->label;
		$js['title']       = $this->title;
		$js['window_name'] = $this->windowName;

		$address = 'index.php?module=hms';
		foreach($this->viewCommand->getRequestVars() as $key=>$var){
			$address .= "&$key=$var";
		}

		$js['address']     = $address;
		
		return javascript('open_window', $js);
	}

	public function execute(CommandContext $context){

	}
}