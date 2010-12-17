<?php

/**
 * SaveRlcCommand - Handles saving a new RLC or updating fields on an existing Learning Community
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class SaveRlcCommand extends Command {

    private $id;

    public function setId($id){
        $this->id = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'SaveRlc');

        if(isset($this->id)){
            $vars['id'] = $this->id;
        }

        return $vars;
    }

    public function execute(CommandContext $context){

        if(!Current_User::allow('hms', 'learning_community_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit learning communities.');
        }

        // If we have an id, load the community with that id.. otherwise, create a new community
        if(!is_null($context->get('id'))){
            $community = new HMS_Learning_Community($context->get('id'));
        }else{
            $community = new HMS_Learning_Community();
        }

        // Set all the fields
        // TODO add appropriate sanity checking...
        $community->set_community_name($context->get('community_name'));
        $community->set_abbreviation($context->get('abbreviation'));
        $community->set_capacity($context->get('capacity'));
        $community->hide = is_null($context->get('hide')) ? 0 : $context->get('hide');
        $community->setAllowedStudentTypes($context->get('student_types'));
        $community->setAllowedReapplicationStudentTypes($context->get('reapplication_student_types'));

        if(is_null($context->get('members_reapply'))){
            $community->setMembersReapply(0);
        }else{
            $community->setMembersReapply(1);
        }

        // Save it
        $result = $community->save();

        // View command for the RLC editt page
        $viewCommand = CommandFactory::getCommand('ShowEditRlc');
        $viewCommand->setId($community->id);

        // Show a success message and redirect
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The RLC was saved successfully.');
        $viewCommand->redirect();
    }
}
?>
