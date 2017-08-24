<?php

namespace Homestead;

/*
 * HousingApplicationView
 *
 *  Abstract view from which each of the sub views inherit.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpacakge hms
 */

class HousingApplicationView extends View {

    protected $id;

    public function __construct($id){
        $this->id = $id;
    }

    public function getView($id){
        //TODO: create multiple views here
        return new HousingApplicationView($id);
    }

    //public abstract function getTemplate();

    public function show(){
        // TODO: Load application in controller and pass it to HousingApplicationView constructor.
        $application = HousingApplicationFactory::getApplicationById($this->id);
        $student     = StudentFactory::getStudentByUsername($application->username, $application->term);

        $tpl = array();

        //If the application has been submitted plug in the date it was created
        if(isset($application->created_on)){
            $tpl['RECEIVED_DATE']   = "Received on: " . date('d-F-Y h:i:s a', $application->created_on);
        }

        if($application instanceof LotteryApplication && $application->getWaitingListDate() != null){
            $tpl['WAITING_LIST_DATE'] = date("d-F-y h:i:sa", $application->getWaitingListDate());
        }


        // Check if the application has been cancelled
        // isWithdrawn() has been depricated, but I'm leaving it here just for historical sake
        // on the off-chance that it catches an older application that's withdrawn but not cancelled.
        if($application->isCancelled() || $application->isWithdrawn()){
            NQ::simple('hms', NotificationView::WARNING, 'This application has been cancelled.');
        }

        $tpl['STUDENT_NAME']                = $student->getFullName();
        $tpl['GENDER']                      = $student->getPrintableGender();
        $tpl['ENTRY_TERM']                  = Term::toString($application->term);
        $tpl['CLASSIFICATION_FOR_TERM_LBL'] = $student->getPrintableClass();
        $tpl['STUDENT_STATUS_LBL']          = $student->getPrintableType();

        $tpl['MEAL_OPTION']         = HMS_Util::formatMealOption($application->meal_plan);
        if(isset($application->lifestyle_option)){
            $tpl['LIFESTYLE_OPTION']    = $application->lifestyle_option == 1?'Single gender':'Co-ed';
        }else{
            $tpl['LIFESTYLE_OPTION']    = 'n/a';
        }

        if(isset($application->preferred_bedtime)){
            $tpl['PREFERRED_BEDTIME']   = $application->preferred_bedtime == 1?'Early':'Late';
        }else{
            $tpl['PREFERRED_BEDTIME']   = 'n/a';
        }

        if(isset($application->room_condition)){
            $tpl['ROOM_CONDITION']      = $application->room_condition == 1?'Neat':'Cluttered';
        }else{
            $tpl['ROOM_CONDITION']      = 'n/a';
        }

        if(isset($application->smoking_preference)){
            $tpl['SMOKING_PREFERENCE']  = $application->smoking_preference == 1?'No':'Yes';
        }else{
            $tpl['SMOKING_PREFERENCE']  = 'n/a';
        }

        if(isset($application->room_type)){
            $tpl['ROOM_TYPE']           = $application->room_type == ROOM_TYPE_DOUBLE ? 'Double' : 'Private (if available)';
        }

        $tpl['CELLPHONE'] = '';
        if(strlen($application->cell_phone) == 10){
            $tpl['CELLPHONE']   .= '('.substr($application->cell_phone, 0, 3).')';
            $tpl['CELLPHONE']   .= '-'.substr($application->cell_phone, 3, 3);
            $tpl['CELLPHONE']   .= '-'.substr($application->cell_phone, 6, 4);
        }

        /* Emergency Contact */
        $tpl['EMERGENCY_CONTACT_NAME'] 			= $application->getEmergencyContactName();
        $tpl['EMERGENCY_CONTACT_RELATIONSHIP']	= $application->getEmergencyContactRelationship();
        $tpl['EMERGENCY_CONTACT_PHONE'] 		= $application->getEmergencyContactPhone();
        $tpl['EMERGENCY_CONTACT_EMAIL'] 		= $application->getEmergencyContactEmail();

        $tpl['EMERGENCY_MEDICAL_CONDITION'] = $application->getEmergencyMedicalCondition();

        /* Missing Person */
        if(\Current_User::allow('hms', 'view_missing_person_info')) {
            $tpl['MISSING_PERSON_NAME'] 		= $application->getMissingPersonName();
            $tpl['MISSING_PERSON_RELATIONSHIP']	= $application->getMissingPersonRelationship();
            $tpl['MISSING_PERSON_PHONE'] 		= $application->getMissingPersonPhone();
            $tpl['MISSING_PERSON_EMAIL'] 		= $application->getMissingPersonEmail();
        }

        if($application instanceof FallApplication){
            $rlcApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $application->getTerm());
            if(!is_null($rlcApp)){
                $tpl['RLC_INTEREST_1'] = 'Yes (Completed - Use the main menu to view/modify.)';
            }else{
                $tpl['RLC_INTEREST_1'] = $application->rlc_interest == 0?'No':'Yes';
            }
        }

        if(\Current_User::getUsername() == "hms_student"){
            $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Back to main menu', 'hms', array('type'=>'student', 'op'=>'show_main_menu'));
        }

        Layout::addPageTitle("Housing Application");

        return \PHPWS_Template::process($tpl, 'hms', 'admin/student_application.tpl');
    }
}
