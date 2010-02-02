<?php

PHPWS_Core::initModClass('hms', 'StudentMenuTermBlock.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class FreshmenMainMenuView extends View {
	
    private $student;
    
	public function __construct(Student $student)
	{
	    $this->student = $student;
	}
	
	public function show()
	{
	    $terms = HousingApplication::getAvailableApplicationTermsForStudent($this->student);
	    
	    foreach($terms as $t){
            $termBlock = new StudentMenuTermBlock($this->student, $t['term']);
            $tpl['TERMBLOCK'][] = array('TERMBLOCK_CONTENT'=>$termBlock->show());
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/freshmenMenu.tpl');
	}
	
}

?>
