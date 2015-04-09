<?php

PHPWS_Core::initModClass('hms', 'StudentMenuTermBlock.php');
PHPWS_Core::initModClass('hms', 'StudentMenuWithdrawnTermBlock.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

define('FEATURE_LOCKED_ICON',   '<img class="status-icon" src="mod/hms/img/tango/emblem-readonly.png" alt="Locked"/>');
define('FEATURE_NOTYET_ICON',   '<img class="status-icon" src="mod/hms/img/tango/appointment-new.png" alt="Locked"/>');
define('FEATURE_OPEN_ICON',     '<img class="status-icon" src="mod/hms/img/tango/go-next.png" alt="Open"/>');
define('FEATURE_COMPLETED_ICON','<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>');

class FreshmenMainMenuView extends Homestead\View{

    private $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    public function show()
    {
        $terms = HousingApplication::getAvailableApplicationTermsForStudent($this->student);
        $applications = HousingApplication::getAllApplicationsForStudent($this->student);

        foreach($terms as $t){

            # If the student has a withdrawn application,
            # then show a message instead of the normal menu block.
            if(isset($applications[$t['term']]) && $applications[$t['term']]->isCancelled()){
            $termBlock = new StudentMenuWithdrawnTermBlock($this->student, $t['term']);
        }else{
            $termBlock = new StudentMenuTermBlock($this->student, $t['term']);
        }

        $tpl['TERMBLOCK'][] = array('TERMBLOCK_CONTENT'=>$termBlock->show());
        }

        Layout::addPageTitle("Main Menu");

        return PHPWS_Template::process($tpl, 'hms', 'student/freshmenMenu.tpl');
    }

}

?>
