<?php

/*
 * View for adding rooms.
 */

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class AddRoomView extends View { 

    public $hall;
    public $floor;

    public function show()
    {
        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($this->hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$this->hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($this->floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$this->floor->id));

        $cmd = CommandFactory::getCommand('AddRoom');
        $cmd->floor = $this->floor->id;

        $form = new PHPWS_Form;
        $cmd->initForm($form);

        $form->addText('room_number');
        $form->addHidden('hall_id',$this->hall->id);
        $form->addHidden('floor_id',$this->floor->id);

        $form->addDropBox('pricing_tier', HMS_Pricing_Tier::get_pricing_tiers_array());

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

        $form->addCheck('is_online', 1);

        $form->addCheck('is_reserved', 1);

        $form->addCheck('ra_room', 1);

        $form->addCheck('private_room', 1);

        $form->addCheck('is_medical', 1);

        $form->addCheck('is_overflow', 1);

        $form->addSubmit('submit', 'Submit');

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
    }
}
        