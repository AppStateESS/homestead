<?php

PHPWS_Core::initModClass('hms', 'View.php');

/**
 * The UI for administratively creating a roommate group
 */

class CreateRoommateGroupView extends homestead\View {

    private $roommate1;
    private $roommate2;

    public function __construct($roommate1 = NULL, $roommate2 = NULL){
        $this->roommate1 = $roommate1;
        $this->roommate2 = $roommate2;
    }

    public function show()
    {
        $tpl = array();

        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        $form = new PHPWS_Form('roommate_group');

        $submitCmd = CommandFactory::getCommand('CreateRoommateGroup');
        $submitCmd->initForm($form);

        if(isset($this->roommate1)){
            $form->addText('roommate1', $this->roommate1);
        }else{
            $form->addText('roommate1');
        }

        javascript('modules/hms/autoFocus', array('ELEMENT' => $form->getId('roommate1')));

        if(isset($this->roommate1)){
            $form->addText('roommate2', $this->roommate2);
        }else{
            $form->addText('roommate2');
        }

        $form->addSubmit('submit', 'Create Group');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Create Roommate Group");

        return PHPWS_Template::process($tpl, 'hms', 'admin/create_roommate_group.tpl');
    }
}

?>