<?php

PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');


class RlcSelfSelectInviteSaveCommand extends Command {
	
    private $term;
    private $roommateRequestId;
    
    public function setTerm($term)
    {
    	$this->term = $term;
    }
    
    public function setRoommateRequestId($requestId)
    {
    	$this->roommateRequestId = $requestId;
    }
    
    public function getRequestVars()
    {
    	$vars = array('action'=>'RlcSelfSelectInviteSave', 'term'=>$this->term);
        
        if($this->roommateRequestId != null) {
        	$vars['roommateRequestId'] = $this->roommateRequestId;
        }
        
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        // Check to see if the user is coming back from DocuSign contract
        $event = $context->get('event');
        if(isset($event) && $event != null && ($event == 'signing_complete' || $event == 'viewing_complete')) {
            $roommateRequestId = $context->get('roommateRequestId');
        	if(isset($roommateRequestId) && $roommateRequestId != null) {
                $roommateCmd = CommandFactory::getCommand('LotteryShowRoommateRequest');
                $roommateCmd->setRequestId($roommateRequestId);
                $roommateCmd->redirect();
            } else{
                $hallCmd = CommandFactory::getCommand('LotteryShowChooseHall');
                $hallCmd->redirect();
            }
        }
     
        $term = $context->get('term');
        
        $errorCmd = CommandFactory::getCommand('RlcSelfAssignStart');
        $errorCmd->setTerm($term);
        
        // Load the student
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        // Load the RLC Assignment
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $term);
        
        // Check for accept or decline status
        $acceptance = $context->get('acceptance');
        if(!isset($acceptance) || $acceptance == null) {
        	NQ::simple('hms', hms\NotificationView::ERROR, 'Please indicate whether you accept or decline this invitation.');
            $errorCmd->redirect();
        }

        // Student declined        
        if($acceptance == 'decline') {
        	// student declined
            $rlcAssignment->changeState(new RlcAssignmentDeclinedState($rlcAssignment));
            
            NQ::simple('hms', hms\NotificationView::SUCCESS, 'You have <strong>declined</strong> your Residential Learning Community invitation.');

            // Log this!
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_DECLINE_RLC_INVITE, UserStatus::getUsername(), $rlcAssignment->getRlcName());
            
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }
        
        $termsCheck = $context->get('terms_cond');
        
        // Make sure student accepted the terms
        if($acceptance == 'accept' && !isset($termsCheck)){
            // Student accepted the invite, but didn't check the terms/conditions box
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please check the box indicating that you agree to the learning communitiy terms and conditions.');
            $errorCmd->redirect();
        }
        
        
        // Check phone number
        $cellPhone = $context->get('cellphone');
        $doNotCall = $context->get('do_not_call');
        if(is_null($doNotCall) && (!isset($cellPhone) || $cellPhone == '')){
        	NQ::simple('hms', hms\NotificationView::ERROR, 'Please enter your cell phone number, or check the box to indicate you do not want to give a phone number.');
            $errorCmd->redirect();
        }
        
        /* Emergency Contact Sanity Checking */
        $emergencyName = $context->get('emergency_contact_name');
        $emergencyRelationship = $context->get('emergency_contact_relationship');
        $emergencyPhone = $context->get('emergency_contact_phone');
        $emergencyEmail = $context->get('emergency_contact_email');

        if (empty($emergencyName) || empty($emergencyRelationship) || empty($emergencyPhone) || empty($emergencyEmail)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please complete all of the emergency contact person information.');
            $errorCmd->redirect();
        }


        /* Missing Persons Sanity Checking */
        $missingPersonName = $context->get('missing_person_name');
        $missingPersonRelationship = $context->get('missing_person_relationship');
        $missingPersonPhone = $context->get('missing_person_phone');
        $missingPersonEmail = $context->get('missing_person_email');

        if (empty($missingPersonName) || empty($missingPersonRelationship) || empty($missingPersonPhone) || empty($missingPersonEmail)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please complete all of the missing persons contact information.');
            $errorCmd->redirect();
        }
        
        // Check for an existing housing application
        $housingApp = HousingApplicationFactory::getAppByStudent($student, $term, 'lottery');
        
        if(is_null($housingApp)){
            // Make a new Housing Application
            // TODO: imporve this to mirror the regular housing application...
        	$housingApp = new LotteryApplication(0, $term, $student->getBannerId(), $student->getUsername(), $student->getGender(), 'C', $student->getApplicationTerm(), $cellPhone, BANNER_MEAL_STD, 0, 0, 0, 0, $student->isInternational(), NULL, 0, 0, 0, 0, 0, null);
        } else {
            // Update the existing cell phone
        	$housingApp->setCellPhone($cellPhone);
            
            $housingApp->setEmergencyContactName($emergencyName);
            $housingApp->setEmergencyContactRelationship($emergencyRelationship);
            $housingApp->setEmergencyContactPhone($emergencyPhone);
            $housingApp->setEmergencyContactEmail($emergencyEmail);
            
            $housingApp->setMissingPersonName($missingPersonName);
            $housingApp->setMissingPersonRelationship($missingPersonRelationship);
            $housingApp->setMissingPersonPhone($missingPersonPhone);
            $housingApp->setMissingPersonEmail($missingPersonEmail);
        }
        
        $housingApp->save();
        
        $returnCmd = CommandFactory::getCommand('RlcSelfSelectInviteSave');
        $returnCmd->setTerm($term);

        // If we're confirming a roommate request, then set the ID on the return command
        $roommateRequestId = $context->get('roommateRequestId');
        if(isset($roommateRequestId) && $roommateRequestId != null){
        	$returnCmd->setRoommateRequestId($roommateRequestId);
        }
        
        $agreementCmd = CommandFactory::getCommand('ShowTermsAgreement');
        $agreementCmd->setTerm($term);
        $agreementCmd->setAgreedCommand($returnCmd);
        $agreementCmd->redirect();
    }
}