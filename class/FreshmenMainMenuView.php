<?php

PHPWS_Core::initModClass('hms', 'StudentMenuTermBlock.php');
PHPWS_Core::initModClass('hms', 'StudentMenuWithdrawnTermBlock.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

define('FEATURE_LOCKED_ICON',   '<i class="fa fa-lock"></i>');
define('FEATURE_NOTYET_ICON',   '<i class="fa fa-calendar"></i>');
define('FEATURE_OPEN_ICON',     '<i class="fa fa-arrow-right"></i>');
define('FEATURE_COMPLETED_ICON','<i class="fa fa-check"></i>');

class FreshmenMainMenuView extends hms\View {

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
