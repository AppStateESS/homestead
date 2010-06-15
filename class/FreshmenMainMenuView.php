<?php

PHPWS_Core::initModClass('hms', 'StudentMenuTermBlock.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

define('FEATURE_LOCKED_ICON',   '<img class="status-icon" src="images/mod/hms/tango/appointment-new.png" alt="Locked"/>');
define('FEATURE_OPEN_ICON',     '<img class="status-icon" src="images/mod/hms/tango/go-next.png" alt="Open"/>');
define('FEATURE_COMPLETED_ICON','<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>'); 

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

        Layout::addPageTitle("Main Menu");
        
        return PHPWS_Template::process($tpl, 'hms', 'student/freshmenMenu.tpl');
	}
	
}

?>
