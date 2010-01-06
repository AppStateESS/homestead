<?php

//TODO finish this class, make a view

class AddRoomViewCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'AddRoomView');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'room_structure')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add a room.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Pricing_Tier.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');


        # Setup the title and color of the title bar
        $tpl['TITLE']       = 'Add Room';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Check to make sure we have a floor and hall.
        $floor = new HMS_Floor($floor_id);
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        $hall = new HMS_Residence_Hall($hall_id);
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        # Check Permissions
        if(!Current_User::allow('hms','room_structure')) {
            HMS_Floor::show_edit_floor($floor_id,NULL,'You do not have permission to add rooms.');
        }

        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$floor->id));

        $form = new PHPWS_Form;
        $form->addText('room_number');
        $form->addHidden('hall_id',$hall->id);
        $form->addHidden('floor_id',$floor->id);

        $form->addDropBox('pricing_tier', HMS_Pricing_Tier::get_pricing_tiers_array());

        if($floor->gender_type == COED) {
            $form->addDropBox('gender_type', array(FEMALE=>FEMALE_DESC, MALE=>MALE_DESC));
            $form->setMatch('gender_type', HMS_Util::formatGender($floor->gender_type));
        }else{
            $form->addDropBox('gender_type', array($floor->gender_type=>HMS_Util::formatGender($floor->gender_type)));
            $form->setReadOnly('gender_type', true);
        }

        //Always show the option to set the default gender
        $defGenders = array(FEMALE => FEMALE_DESC, MALE => MALE_DESC);
        if($floor->gender_type == MALE)     unset($defGenders[FEMALE]);
        if($floor->gender_type == FEMALE)   unset($defGenders[MALE]);
        $form->addDropBox('default_gender', $defGenders);
        if($floor->gender_type != COED) {
            $form->setMatch('default_gender', $floor->gender_type);
        }

        $form->addCheck('is_online', 1);

        $form->addCheck('is_reserved', 1);

        $form->addCheck('ra_room', 1);

        $form->addCheck('private_room', 1);

        $form->addCheck('is_medical', 1);

        $form->addCheck('is_overflow', 1);

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'add_room');

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

?>