<?php
PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'Term.php');

class RlcApplicationReView extends View {

    private $student;
    private $application;

    public function __construct(Student $student, HMS_RLC_Application $application){
        $this->student      = $student;
        $this->application  = $application;
    }

    public function show(){
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        if(UserStatus::isAdmin()){
            $menuCmd = CommandFactory::getCommand('ShowAssignRlcApplicants');
            $tags['MENU_LINK'] = $menuCmd->getLink('Return to RLC Applications');
        }else{
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $tags['MENU_LINK'] = $menuCmd->getLink('Return to Menu');
        }

        $tags['FULL_NAME']    = $this->student->getFullName();
        $tags['STUDENT_TYPE'] = $this->student->getPrintableType();

        $appType = $this->application->getApplicationType();
        if($appType == RLC_APP_FRESHMEN){
            $tags['APPLICATION_TYPE'] = 'Freshmen';
        }else if($appType == RLC_APP_RETURNING){
            $tags['APPLICATION_TYPE'] = 'Re-application';
        }

        $tags['FIRST_CHOICE_LABEL'] = "First choice RLC is: ";
        $tags['SECOND_CHOICE_LABEL'] = "Second choice is: ";
        $tags['THIRD_CHOICE_LABEL'] =  "Third choice is: ";

        $tags['WHY_SPECIFIC_LABEL'] = "Specific communities chosen because: ";
        $tags['STRENGTHS_AND_WEAKNESSES_LABEL'] = "Strengths and weaknesses: ";
        $tags['WHY_FIRST_CHOICE_LABEL'] = "First choice selected because: ";
        $tags['WHY_SECOND_CHOICE_LABEL'] = "Second choice selected because: ";
        $tags['WHY_THIRD_CHOICE_LABEL'] = "Third choice selected because: ";

        //TODO move this to a function in HMS_Learning_Communities
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        $rlcs_raw = $db->select();

        foreach($rlcs_raw as $rlc) {
            $rlcs[$rlc['id']] = $rlc['community_name'];
        }

        $tags['FIRST_CHOICE'] = $rlcs[$this->application->rlc_first_choice_id];

        if(isset($this->application->rlc_second_choice_id)){
            $tags['SECOND_CHOICE'] = $rlcs[$this->application->rlc_second_choice_id];
        }else{
            $tags['SECOND_CHOICE'] = 'None';
        }

        if(isset($this->application->rlc_third_choice_id)){
            $tags['THIRD_CHOICE'] = $rlcs[$this->application->rlc_third_choice_id];
        }else{
            $tags['THIRD_CHOICE'] = 'None';
        }

        $tags['WHY_SPECIFIC'] = $this->application->why_specific_communities;
        $tags['STRENGTHS_AND_WEAKNESSES'] = $this->application->strengths_weaknesses;
        $tags['WHY_FIRST_CHOICE'] = $this->application->rlc_question_0;

        if(isset($this->application->rlc_second_choice_id)){
            $tags['WHY_SECOND_CHOICE'] = $this->application->rlc_question_1;
        }else{
            $tags['WHY_SECOND_CHOICE'] = 'n/a';
        }

        if(isset($this->application->rlc_second_choice_id)){
            $tags['WHY_THIRD_CHOICE'] = $this->application->rlc_question_2;
        }else{
            $tags['WHY_THIRD_CHOICE'] = 'n/a';
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_Learing_Community.php');
        
        // Show options depending of status of application.
        if(!$this->application->denied && !HMS_RLC_Assignment::checkForAssignment($this->student->getUsername(), Term::getSelectedTerm())){
            
            // Approve application for the community selected from dropdown
            $approvalForm = $this->getApprovalForm();
            $approvalForm->mergeTemplate($tags);
            $tags = $approvalForm->getTemplate();
            // Deny application
            $tags['DENY_APP'] = $this->getDenialLink();
        }

        Layout::addPageTitle("RLC Application Review");

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
        $approveForm->addSubmit('approve', 'Approve');
        $approveCmd = CommandFactory::getCommand('AssignRlcApplicants');
        $tpl['RLC_LIST'] = HMS_RLC_Application::generateRLCDropDown(HMS_Learning_Community::getRLCList(), 
                                                                             $this->application->id);
        $approveForm->mergeTemplate($tpl);

        $approveCmd->initForm($approveForm);
        return $approveForm;
    }
}
?>