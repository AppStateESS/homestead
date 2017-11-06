<?php

namespace Homestead;

/*
 * View for adding rooms.
 * @package hms
 */

class AddRoomView extends View {

    private $hall;
    private $floor;

    public function __construct(Floor $floor){
        $this->floor = $floor;
        $this->hall = $floor->get_parent();
    }

    public function show()
    {
        $tpl = array();
        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['FLOOR_NUMBER_LINK']   = $this->floor->getLink('Floor');
        $tpl['FLOOR_NUMBER']        = $this->floor->where_am_i();
        $tpl['TERM']                = Term::getPrintableSelectedTerm();

        $cmd = CommandFactory::getCommand('AddRoom');
        $cmd->floor = $this->floor->id;

        $form = new \PHPWS_Form;
        $cmd->initForm($form);

        $form->addText('room_number');
        $form->addCssClass('room_number', 'form-control');
        $form->addHidden('hall_id',$this->hall->id);
        $form->addHidden('floor_id',$this->floor->id);

        if($this->floor->gender_type == COED) {
            $form->addDropBox('gender_type', array(FEMALE=>FEMALE_DESC, MALE=>MALE_DESC));
            $form->addCssClass('gender_type', 'form-control');
            $form->setMatch('gender_type', HMS_Util::formatGender($this->floor->gender_type));
        }else{
            $form->addDropBox('gender_type', array($this->floor->gender_type=>HMS_Util::formatGender($this->floor->gender_type)));
            $form->setReadOnly('gender_type', true);
        }

        // Always show the option to set the default gender
        $defGenders = array(FEMALE => FEMALE_DESC, MALE => MALE_DESC);
        if($this->floor->gender_type == MALE)     unset($defGenders[FEMALE]);
        if($this->floor->gender_type == FEMALE)   unset($defGenders[MALE]);
        $form->addDropBox('default_gender', $defGenders);
        $form->addCssClass('default_gender', 'form-control');
        if($this->floor->gender_type != COED) {
            $form->setMatch('default_gender', $this->floor->gender_type);
        }

        // Add a dropbox to for rlc
        $form->addDropBox('rlc_reserved', array("0"=>"None") + RlcFactory::getRlcList(Term::getSelectedTerm()));
        $form->setLabel('rlc_reserved', 'Reserved for RLC');
        $form->addCssClass('rlc_reserved', 'form-control');

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

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/addRoom.tpl');
    }
}
