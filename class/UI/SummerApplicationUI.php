<?php

class SummerApplicationUI {

    private $application    = null;
    private $term           = null;
    private $error_msg      = null;

    public function __construct($application = NULL, $term = NULL)
    {
        if(isset($application)){
            $this->setApplication($application);
        }

        if(!is_null($term) && isset($term)){
            # TODO error if the term given is not a valid summer term
            $this->setTerm($term);
        }
    }

    public function showForm(){
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $username = $_SESSION['asu_username'];
        $term = $this->getTerm();
        $application = $this->getApplication();

        $form = new PHPWS_Form('summer_application');

        $form->addHidden('term', $_REQUEST['term']);

        /********************
         * Demagraphic Info *
         ********************/
        $tpl = array();
        $tpl['NAME']                = HMS_SOAP::get_full_name($username);
        $tpl['TERM']                = HMS_Term::term_to_text($term, TRUE);
        $tpl['GENDER']              = HMS_Util::formatGender(HMS_SOAP::get_gender($username));
        $tpl['CLASS']               = HMS_Util::formatClass(HMS_SOAP::get_student_class($username));
        $tpl['TYPE']                = HMS_Util::formatType(HMS_SOAP::get_student_type($username));

        /**************
         * Cell phone *
         **************/
        if(isset($_REQUEST['area_code'])){
            $form->addText('area_code', $_REQUEST['area_code']);
        }else if(isset($application->cell_phone)){
            $form->addText('area_code', $application->getAreaCode());
        }else{
            $form->addText('area_code');
        }
        $form->setSize('area_code', 3);
        $form->setMaxSize('area_code', 3);



        if(isset($_REQUEST['exchange'])){
            $form->addText('exchange', $_REQUEST['exchange']);
        }else if(isset($application->cell_phone)){
            $form->addText('exchange', $application->getExchange());
        }else{
            $form->addText('exchange');
        }
        $form->setSize('exchange', 3);
        $form->setMaxSize('exchange', 3);



        if(isset($_REQUEST['number'])){
            $form->addText('number', $_REQUEST['number']);
        }else if(isset($application->cell_phone)){
            $form->addText('number', $application->getAreaCode());
        }else{
            $form->addText('number');
        }
        $form->setSize('number', 4);
        $form->setMaxSize('number', 4);

        $form->addCheck('do_not_call', 1);
        if(isset($_REQUEST['do_not_call'])){
            $form->setMatch('do_not_call', 1);
        }else if(isset($application) && is_null($application->cell_phone)){
            $form->setMatch('do_not_call', 1);
        }

        /***************
         * Meal option *
         ***************/
        # TODO: hard code summer meal option

        /*************
         * Room Type *
         *************/
        $form->addDropBox('room_type', array('0'=>'Two person', '1'=>'Private (if available)'));

        if(isset($_REQUEST['room_type'])){
            $form->setMatch('room_type', $_REQUEST['room_type']);
        }else if(isset($application)){
            $form->setMatch('room_type', $application->getRoomType());
        }else{
            $form->setMatch('room_type', '0');
        }

        /*****************
         * Special needs *
         *****************/
        $tpl['SPECIAL_NEEDS_TEXT'] = ''; // setting this template variable to anything causes the special needs text to be displayed
        $form->addCheck('special_need', array('special_need'));
        $form->setLabel('special_need', array('Yes, I require special needs housing.'));

        if(isset($_REQUEST['special_need'])){
            $form->setMatch('special_need', $_REQUEST['special_need']);
        }else if(isset($application) && 
                ($application->physical_disability == 1 || 
                 $application->psych_disability == 1 ||
                 $application->medical_need == 1||
                 $application->gender_need == 1)){
            $form->setMatch('special_need', 'special_need');
        }


        $form->addSubmit('continue', _('Continue'));
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'summer_application_submit');
        $form->addHidden('first_submit', 'true');

        # Show an error message, if there is one
        $tpl['ERROR_MSG'] = $this->getError();

        $form->mergeTemplate($tpl);
        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/applications/summer_application.tpl');
    }

    public function showConfirmation()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $username = $_SESSION['asu_username'];
        $term = $this->getTerm();
        $application = $this->getApplication();

        $form = new PHPWS_Form('summer_application');

        /********************
         * Demagraphic Info *
         ********************/
        $tpl = array();
        $tpl['NAME']                = HMS_SOAP::get_full_name($username);
        $tpl['TERM']                = HMS_Term::term_to_text($term, TRUE);
        $tpl['GENDER']              = HMS_Util::formatGender(HMS_SOAP::get_gender($username));
        $tpl['CLASS']               = HMS_Util::formatClass(HMS_SOAP::get_student_class($username));
        $tpl['TYPE']                = HMS_Util::formatType(HMS_SOAP::get_student_type($username));

        if(isset($_REQUEST['do_not_call'])){
            $tpl['CELLPHONE'] = 'Not provided';
        }else{
            $tpl['CELLPHONE'] = '('.$_REQUEST['area_code'].')-'.$_REQUEST['exchange'].'-'.$_REQUEST['number'];
        }

        if($_REQUEST['room_type'] == 0){
            $tpl['ROOM_TYPE'] = 'Two person';
        }else{
            $tpl['ROOM_TYPE'] = 'Private (if available)';
        }

        # Special Needs
        $special_needs = "";
        if(isset($_REQUEST['special_needs']['physical_disability'])){
            $special_needs = 'Physical disability<br />';
        }
        if(isset($_REQUEST['special_needs']['psych_disability'])){
            $special_needs .= 'Psychological disability<br />';
        }
        if(isset($_REQUEST['special_needs']['medical_need'])){
            $special_needs .= 'Medical need<br />';
        }
        if(isset($_REQUEST['special_needs']['gender_need'])){
            $special_needs .= 'Gender need<br />';
        }

        if($special_needs == ''){
            $special_needs = 'None';
        }
        $tpl['SPECIAL_NEEDS_RESULT'] = $special_needs;

        # Carry over all the fields submitted on the first page of the application
        foreach($_POST as $key=>$value){
            if($key == 'module' || $key == 'type' || $key == 'op')
                continue;

            $form->addHidden($key, $value);
        }

        $form->addSubmit('Submit Application');
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHIdden('op', 'summer_application_save');
        $form->mergeTemplate($tpl);
        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/applications/summer_application.tpl');
    }

    public function setApplication($application){
        $this->application = $application;
    }

    public function getApplication(){
        return $this->application;
    }

    public function setTerm($term){
        $this->term = $term;
    }

    public function getTerm(){
        return $this->term;
    }

    public function getError(){
        return $this->error_msg;
    }

    public function setError($msg){
        $this->error_msg = $msg;
    }
}

?>
