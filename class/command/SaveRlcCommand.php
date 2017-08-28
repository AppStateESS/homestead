<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

/**
 * SaveRlcCommand - Handles saving a new RLC or updating fields on an existing Learning Community
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package HMS
 */
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

        if(!\Current_User::allow('hms', 'learning_community_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit learning communities.');
        }

        // If we have an id, load the community with that id.. otherwise, create a new community
        if(!is_null($context->get('id'))){
            $community = new HMS_Learning_Community($context->get('id'));
        }else{
            $community = new HMS_Learning_Community();
        }

        // TODO add appropriate sanity checking...

        /*** General Settings ***/
        $community->set_community_name($context->get('community_name'));
        $community->set_abbreviation($context->get('abbreviation'));

        $capacity = $context->get('capacity');
        if(!isset($capacity) || empty($capacity)){
            $capacity = 0;
            \NQ::simple('hms', NotificationView::WARNING, "The community's capacity was set to 0.");
        }
        $community->set_capacity($capacity);

        /*** RLC-specific move-in times ***/
        // Freshmen
        $fMoveinTime = $context->get('f_movein_time');
        if($fMoveinTime == 0){
            $community->setFreshmenMoveinTime(null);
        }else{
            $community->setFreshmenMoveinTime($fMoveinTime);
        }

        // Transfer
        $tMoveinTime = $context->get('t_movein_time');
        if($tMoveinTime == 0){
            $community->setTransferMoveinTime(null);
        }else{
            $community->setTransferMoveinTime($tMoveinTime);
        }

        // Continuing
        $cMoveinTime = $context->get('c_movein_time');
        if($cMoveinTime == 0){
            $community->setContinuingMoveinTime(null);
        }else{
            $community->setContinuingMoveinTime($cMoveinTime);
        }

        /*** Student Types Allowed to Apply ***/
        $community->hide = is_null($context->get('hide')) ? 0 : $context->get('hide');
        $community->setAllowedStudentTypes($context->get('student_types'));
        $community->setAllowedReapplicationStudentTypes($context->get('reapplication_student_types'));

        if(is_null($context->get('members_reapply'))){
            $community->setMembersReapply(0);
        }else{
            $community->setMembersReapply(1);
        }

        /*** Application Questions ***/
        $community->setFreshmenQuestion($context->get('freshmen_question'));
        $community->setReturningQuestion($context->get('returning_question'));

        /*** Terms & Conditions ***/
        $community->setTermsConditions($context->get('terms_conditions'));


        // Save it
        $community->save();

        // View command for the RLC editt page
        $viewCommand = CommandFactory::getCommand('ShowAddRlc');
        $viewCommand->setId($community->getId());

        // Show a success message and redirect
        \NQ::simple('hms', NotificationView::SUCCESS, 'The RLC was saved successfully.');
        $viewCommand->redirect();
    }
}
