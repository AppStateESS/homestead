<?php

/*
 * View for adding rooms.
 */

//TODO combine this with 'RoomView.php' and figure out how to use one view for two different controllers.......

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class AddRoomView extends Homestead\View{

    private $hall;
    private $floor;

    public function __construct(HMS_Floor $floor){
        $this->floor = $floor;
        $this->hall = $floor->get_parent();
    }

    public function show()
    {
        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['FLOOR_NUMBER']        = $this->floor->getLink('Floor');
        $tpl['TERM'] = Term::getPrintableSelectedTerm();
        $tpl['NEW_ROOM'] = ""; // dummy var

        $cmd = CommandFactory::getCommand('AddRoom');
        $cmd->floor = $this->floor->id;

        $form = new PHPWS_Form;
        $cmd->initForm($form);

        $form->addText('room_number');
        $form->addHidden('hall_id',$this->hall->id);
        $form->addHidden('floor_id',$this->floor->id);

        if($this->floor->gender_type == COED) {
            $form->addDropBox('gender_type', array(FEMALE=>FEMALE_DESC, MALE=>MALE_DESC));
            $form->setMatch('gender_type', HMS_Util::formatGender($this->floor->gender_type));
        }else{
            $form->addDropBox('gender_type', array($this->floor->gender_type=>HMS_Util::formatGender($this->floor->gender_type)));
            $form->setReadOnly('gender_type', true);
        }

        //Always show the option to set the default gender
        $defGenders = array(FEMALE => FEMALE_DESC, MALE => MALE_DESC);
        if($this->floor->gender_type == MALE)     unset($defGenders[FEMALE]);
        if($this->floor->gender_type == FEMALE)   unset($defGenders[MALE]);
        $form->addDropBox('default_gender', $defGenders);
        if($this->floor->gender_type != COED) {
            $form->setMatch('default_gender', $this->floor->gender_type);
        }

        $form->addCheck('offline', 1);
        $form->setLabel('offline', 'Offline');

        $form->addCheck('reserved', 1);
        $form->setLabel('reserved','Reserved');

        $form->addCheck('ra', 1);
        $form->setLabel('ra','Reserved for RA');

        $form->addCheck('private', 1);
        $form->setLabel('private','Private');

        $form->addCheck('overflow', 1);
        $form->setLabel('overflow','Overflow');

        $form->addCheck('parlor', 1);
        $form->setLabel('parlor','Parlor');
        
        $form->addCheck('ada', 1);
        $form->setLabel('ada', 'ADA');
        
        $form->addCheck('hearing_impaired', 1);
        $form->setLabel('hearing_impaired', 'Hearing Impaired');
        
        $form->addCheck('bath_en_suite', 1);
        $form->setLabel('bath_en_suite', 'Bath en Suite');

        $form->addSubmit('submit', 'Submit');

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');
    }
}
