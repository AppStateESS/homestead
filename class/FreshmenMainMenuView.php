<?php

class FreshmenMainMenuView extends View {
	
	private $student;
	
	public function __construct(Student $student)
	{
		$this->student = $student;
	}
	
	public function show()
	{
		return "main menu here";
	}
	
}

?>