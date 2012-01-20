<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditResidenceHallCommand extends Command {

    private $hallId;

    function setHallId($id){
        $this->hallId = $id;
    }

    function getRequestVars()
    {
        $vars = array('action'=>'EditResidenceHall');
         
        if(isset($this->hallId)){
            $vars['hallId'] = $this->hallId;
        }
         
        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'hall_attributes') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit halls.');
        }
         
        # Make sure a hall ID was set
        $hallId = $context->get('hallId');
        if(is_null($hallId)){
            throw new InvalidArgumentException('Missing hall ID.');
        }
         
        $viewCmd = CommandFactory::getCommand('EditResidenceHallView');
        $viewCmd->setHallId($hallId);

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        # Create the hall object given the hall id
        $hall = new HMS_Residence_Hall($hallId);
        if(!$hall){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid hall.');
            $viewCmd->redirect();
        }

        # Compare the hall's gender and the gender the user selected
        # If they're not equal, call 'can_change_gender' public function
        if($hall->gender_type != $_REQUEST['gender_type']){
            if(!$hall->can_change_gender($_REQUEST['gender_type'])){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Incompatible gender detected. No changes were made.');
                $viewCmd->redirect();
            }
        }

        # Grab all the input from the form and save the hall
        $hall->hall_name                = $context->get('hall_name');
        $hall->gender_type              = $context->get('gender_type');

        # Set the defaults for the check boxes
        $context->setDefault('air_conditioned', 0);
        $context->setDefault('is_online', 0);
        $context->setDefault('meal_plan_required', 0);
        $context->setDefault('assignment_notifications', 0);

        $hall->air_conditioned          = $context->get('air_conditioned');
        $hall->is_online                = $context->get('is_online');
        $hall->meal_plan_required       = $context->get('meal_plan_required');
        $hall->assignment_notifications = $context->get('assignment_notifications');

        $hall->exterior_image_id    = $context->get('exterior_image_id');
        $hall->other_image_id       = $context->get('other_image_id');
        $hall->map_image_id         = $context->get('map_image_id');
        $hall->room_plan_image_id   = $context->get('room_plan_image_id');

        $result = $hall->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was a problem saving the Residence Hall. No changes were made.');
            $viewCmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The Residence hall was updated successfully.');
        $viewCmd->redirect();
    }
}

?>
