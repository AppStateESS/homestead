<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditFloorCommand extends Command {

    private $floorId;

    public function setFloorId($id){
        $this->floorId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditFloor');
        	
        if(isset($this->floorId)){
            $vars['floorId'] = $this->floorId;
        }
        	
        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

        if( !Current_User::allow('hms', 'floor_attributes') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit floors.');
        }
        	
        $floorId = $context->get('floorId');
        	
        $viewCmd = CommandFactory::getCommand('EditFloorView');
        $viewCmd->setFloorId($floorId);

        # Create the floor object gien the floor id
        $floor = new HMS_Floor($floorId);
        if(!$floor){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid floor.');
            $viewCmd->redirect();
        }

        # Compare the floor's gender and the gender the user selected
        # If they're not equal, call 'can_change_gender' public function
        if($floor->gender_type != $context->get('gender_type')){
            if(!$floor->can_change_gender($context->get('gender_type'))){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Incompatible gender detected. No changes were made.');
                $viewCmd->redirect();
            }
        }

        # Grab all the input from the form and save the floor
        $floor->gender_type = $context->get('gender_type');

        $context->setDefault('is_online', 0);
        $floor->is_online = $context->get('is_online');

        $floor->ft_movein_time_id = $context->get('ft_movein_time');
        $floor->rt_movein_time_id = $context->get('rt_movein_time');
        $floor->floor_plan_image_id = $context->get('floor_plan_image_id');

        if($context->get('ft_movein_time') == 0){
            $floor->ft_movein_time_id = NULL;
        }else{
            $floor->ft_movein_time_id = $context->get('ft_movein_time');
        }

        if($context->get('rt_movein_time') == 0){
            $floor->rt_movein_time_id = NULL;
        }else{
            $floor->rt_movein_time_id = $context->get('rt_movein_time');
        }

        if($context->get('floor_rlc_id') == 0){
            $floor->rlc_id = NULL;
        }else{
            $floor->rlc_id = $context->get('floor_rlc_id');
        }

        $result = $floor->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was a problem saving the floor data. No changes were made.');
            $viewCmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The floor was updated successfully.');
        $viewCmd->redirect();
    }
}

?>