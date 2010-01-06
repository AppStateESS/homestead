<?php

///TODO This class is deprecated. Delete it and check for anywhere this code is still in use (by code that's also still in use)

class SummerApplicationControl {

    public function summer_application_submit()
    {
        PHPWS_Core::initModClass('hms', 'UI/SummerApplicationUI.php');
        PHPWS_Core::initModClass('hms', 'UI/SpecialNeedsUI.php');

        # Make sure a valid phone number was entered
        if((empty($_REQUEST['area_code']) || empty($_REQUEST['exchange']) || empty($_REQUEST['number'])) && !isset($_REQUEST['do_not_call'])){
            $summer_app = new SummerApplicationUI(NULL, $_REQUEST['term']);
            $summer_app->setError('Error: You must provide a valid phone number or check the \'I do not wish to provide it\' checkbox.');
            return $summer_app->showForm();
        }

        if(!isset($_REQUEST['do_not_call']) && (!is_numeric($_REQUEST['area_code']) || !is_numeric($_REQUEST['exchange']) || !is_numeric($_REQUEST['number']))){
            $summer_app = new SummerApplicationUI(NULL, $_REQUEST['term']);
            $summer_app->setError('Error: You must provide a valid phone number or check the \'I do not wish to provide it\' checkbox.');
            return $summer_app->showForm();
        }

        # Sanity checking

        # Check for special needs
        if(isset($_REQUEST['special_need'])){
            $special_needs = new SpecialNeedsUI('summer_application_confirmation');
            return $special_needs->show();
        }else{
            $summerUI = new SummerApplicationUI(NULL, $_REQUEST['term']);
            return $summerUI->showConfirmation();
        }
    }

    public function summer_application_confirmation()
    {
        PHPWS_Core::initModClass('hms', 'UI/SummerApplicationUI.php');

        $summerUI = new SummerApplicationUI(NULL, $_REQUEST['term']);
        return $summerUI->showConfirmation();
    }

    public function summer_application_save()
    {
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'UI/SummerApplicationUI.php');
        PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $username   = $_SESSION['asu_username'];
        $term       = $_REQUEST['term'];

        if(isset($_REQUEST['area_code']) && isset($_REQUEST['exchange']) && isset($_REQUEST['number'])){
            $cellphone  = $_REQUEST['area_code'] . $_REQUEST['exchange'] . $_REQUEST['number'];
        }else{
            $cellphone = NULL;
        }

        $meal_plan = BANNER_MEAL_5WEEK;

        # Create the SummerApplication object and try to save it
        $application = new SummerApplication(0,
                                            $term,
                                            HMS_SOAP::get_banner_id($username),
                                            $username,
                                            HMS_SOAP::get_gender($username,TRUE),
                                            HMS_SOAP::get_student_type($username),
                                            HMS_SOAP::get_application_term($username),
                                            $cellphone,
                                            $meal_plan,
                                            isset($_REQUEST['special_needs']['physical_disability']) ? 1 : 0,
                                            isset($_REQUEST['special_needs']['psych_disability']) ? 1 : 0,
                                            isset($_REQUEST['special_needs']['gender_need']) ? 1 : 0,
                                            isset($_REQUEST['special_needs']['medical_need']) ? 1 : 0,
                                            $_REQUEST['room_type']
                                            );


        $result = $application->save();

        # Show an error message if the save fails
        if(!$result){
            $summer_app = new SummerApplicationUI(NULL, $_REQUEST['term']);
            $summer_app->setError('Error: There was a problem saving your application. Please try again, or contact the Department of Housing and Residnece Life.');
            return $summer_app->showForm();
        }

        # Report the application to Banner
        $application->reportToBanner();

        return HMS_Student_UI::show_returning_menu('Your application was successfully saved.');
    }
}

?>
