<?php

/**
 * View class for displaying an existing RLC application.
 * 
 * @author jbooker
 * @package HMS
 */
class RlcApplicationReView extends hms\View{

    private $student;
    private $application;

    public function __construct(Student $student, HMS_RLC_Application $application){
        $this->student      = $student;
        $this->application  = $application;
    }

    public function show(){
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        
        Layout::addPageTitle("RLC Application Review");
        
        if(UserStatus::isAdmin()){
            $menuCmd = CommandFactory::getCommand('ShowAssignRlcApplicants');
            $tags['MENU_LINK'] = $menuCmd->getLink('&laquo; Return to RLC Applications');
        }else{
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $tags['MENU_LINK'] = $menuCmd->getLink('&laquo; Return to Menu');
        }

        $tags['FULL_NAME']    = $this->student->getFullName();
        $tags['STUDENT_TYPE'] = $this->student->getPrintableType();
        $tags['TERM']         = Term::toString($this->application->getTerm());

        $appType = $this->application->getApplicationType();
        if($appType == RLC_APP_FRESHMEN){
            $tags['APPLICATION_TYPE'] = 'Freshmen';
        }else if($appType == RLC_APP_RETURNING){
            $tags['APPLICATION_TYPE'] = 'Re-application';
        }
        
        $rlcs = HMS_Learning_Community::getRlcList();
        
        $tags['FIRST_CHOICE'] = $rlcs[$this->application->rlc_first_choice_id];

        if(isset($this->application->rlc_second_choice_id)){
            $tags['SECOND_CHOICE'] = $rlcs[$this->application->rlc_second_choice_id];
        }

        if(isset($this->application->rlc_third_choice_id)){
            $tags['THIRD_CHOICE'] = $rlcs[$this->application->rlc_third_choice_id];
        }

        $tags['WHY_SPECIFIC'] = $this->application->why_specific_communities;
        $tags['STRENGTHS_AND_WEAKNESSES'] = $this->application->strengths_weaknesses;
        $tags['WHY_FIRST_CHOICE'] = $this->application->rlc_question_0;

        if(isset($this->application->rlc_second_choice_id)){
            $tags['WHY_SECOND_CHOICE'] = $this->application->rlc_question_1;
        }

        if(isset($this->application->rlc_second_choice_id)){
            $tags['WHY_THIRD_CHOICE'] = $this->application->rlc_question_2;
        }

        // If this application is denied and the person logged in is an admin, show a warning
        if($this->application->isDenied() && UserStatus::isAdmin()){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'This application has been denied.');
        }
        
        // Show options depending of status of application.
        if(UserStatus::isAdmin() && Current_User::allow('hms', 'approve_rlc_applications')){
            if(!$this->application->denied && !HMS_RLC_Assignment::checkForAssignment($this->student->getUsername(), Term::getSelectedTerm())){
                // Approve application for the community selected from dropdown
                $approvalForm = $this->getApprovalForm();
                $approvalForm->mergeTemplate($tags);
                $tags = $approvalForm->getTemplate();
                // Deny application
                $tags['DENY_APP'] = $this->getDenialLink();
            }
        }

        return PHPWS_Template::process($tags, 'hms', 'student/rlc_application.tpl');
    }

    /**
     * Get the link for denying application.
     */
    private function getDenialLink()
    {
        $cmd = CommandFactory::getCommand('JSConfirm');
        $cmd->setLink("<input type='button' value='Deny'></input>");
        $cmd->setTitle('Deny RLC Application');
        $cmd->setQuestion('Are you sure you want to deny this RLC Application?');
        $denyCmd = CommandFactory::getCommand('DenyRlcApplication');
        $denyCmd->setApplicationId($this->application->id);
        $cmd->setOnConfirmCommand($denyCmd);

        return $cmd->getLink();
    }

    /**
     * Get form for approving application for specific community.
     */
    private function getApprovalForm()
    {
        $approveForm = new PHPWS_Form('approve_form');
        $approveForm->addSubmit('approve', 'Accept');
        $approveCmd = CommandFactory::getCommand('AssignRlcApplicants');
        $tpl['RLC_LIST'] = HMS_RLC_Application::generateRLCDropDown(HMS_Learning_Community::getRlcList(),
                                                                             $this->application->id);
        $approveForm->mergeTemplate($tpl);

        $approveCmd->initForm($approveForm);
        return $approveForm;
    }
}
?>
