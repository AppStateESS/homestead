<?php
/*
 * HousingApplicationView
 *
 *  Abstract view from which each of the sub views inherit.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpacakge hms
 */
PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'FallApplication.php');
PHPWS_Core::initModClass('hms', 'SpringApplication.php');
PHPWS_Core::initModClass('hms', 'SummerApplication.php');

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Util.php');

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
        $application = HousingApplicationFactory::getApplicationById($this->id);
        $student     = StudentFactory::getStudentByUsername($application->username, $application->term);

        $tpl = array();

        //If the application has been submitted plug in the date it was created
        if( isset($application->created_on) )
            $tpl['RECEIVED_DATE']   = "Received on: " . date('d-F-Y h:i:s a', $application->created_on);

        $tpl['STUDENT_NAME']                = $student->getFullName();
        $tpl['GENDER']                      = $student->getPrintableGender();
        $tpl['ENTRY_TERM']                  = Term::toString($application->term);
        $tpl['CLASSIFICATION_FOR_TERM_LBL'] = $student->getPrintableClass();
        $tpl['STUDENT_STATUS_LBL']          = $student->getPrintableType();

        $tpl['MEAL_OPTION']         = HMS_Util::formatMealOption($application->meal_plan);
        $tpl['LIFESTYLE_OPTION']    = $application->lifestyle_option == 1?'Single gender':'Co-ed';
        $tpl['PREFERRED_BEDTIME']   = $application->preferred_bedtime == 1?'Early':'Late';
        
        $tpl['ROOM_CONDITION']      = $application->room_condition == 1?'Neat':'Cluttered';
        
        $tpl['CELLPHONE'] = '';
        if(strlen($application->cell_phone) == 10){
            $tpl['CELLPHONE']   .= '('.substr($application->cell_phone, 0, 3).')';
            $tpl['CELLPHONE']   .= '-'.substr($application->cell_phone, 3, 3);
            $tpl['CELLPHONE']   .= '-'.substr($application->cell_phone, 6, 4);
        }
        
        $special_needs = "";
        if($application->physical_disability == 1){
            $special_needs = 'Physical disability<br />';
        }
        if($application->psych_disability){
            $special_needs .= 'Psychological disability<br />';
        }
        if($application->medical_need){
            $special_needs .= 'Medical need<br />';
        }
        if($application->gender_need){
            $special_needs .= 'Gender need<br />';
        }

        if($special_needs == ''){
            $special_needs = 'None';
        }
        $tpl['SPECIAL_NEEDS_RESULT'] = $special_needs;

        if($application instanceof FallApplication ){
            $tpl['RLC_INTEREST_1'] = $application->rlc_interest == 0?'No':'Yes';
        }

        if(Current_User::getUsername() == "hms_student"){
            $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Back to main menu', 'hms', array('type'=>'student', 'op'=>'show_main_menu'));
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/student_application.tpl');
    }
}
?>
