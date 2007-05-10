<?php

class HMS_Roommate_Approval
{

    var $id;
    var $number_roommates;
    var $roommate_zero;
    var $roommate_zero_approved;
    var $roommate_zero_personal_hash;
    var $roommate_one;
    var $roommate_one_approved;
    var $roommate_one_personal_hash;
    var $roommate_two;
    var $roommate_two_approved;
    var $roommate_two_personal_hash;
    var $roommate_three;
    var $roommate_three_approved;
    var $roommate_three_personal_hash;

    /**
     * Sets the id of the group approval
     */
    function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the approval id
     */
    function get_id()
    {
        return $this->id;
    }

    /**
     * Sets the username for the first roommate
     */
    function set_roommate_zero($rz)
    {
        $this->roommate_zero = $rz;
    }

    /**
     * Gets the username for the first roommate
     */
    function get_roommate_zero()
    {
        return $this->roommate_zero;
    }

    /**
     * Sets the approved value for the first roommate
     */
    function set_roommate_zero_approved($rza)
    {
        $this->roommate_zero_approved = $rza;
    }

    /**
     * Gets the approved value for the first roommate
     */
    function get_roommate_zero_approved()
    {
        return $this->roommate_zero_approved;
    }

    /**
     * Sets the username for the second roommate
     */
    function set_roommate_one($ro)
    {
        $this->roommate_one = $ro;
    }

    /**
     * Returns the username for the second roommate
     */
    function get_roommate_one()
    {
        return $this->roommate_one;
    }

    /**
     * Sets the approved value for the second roommate
     */
    function set_roommate_one_approved($roa)
    {
        $this->roommate_one_approved = $roa;
    }

    /**
     * Returns the approved value for the second roommate
     */
    function get_roommate_one_approved()
    {
        return $this->roommate_one_approved;
    }

    /**
     * Sets the username for the third roommate
     */
    function set_roommate_two($rt)
    {
        $this->roommate_two = $rt;
    }

    /**
     * Returns the username for the third roommate
     */
    function get_roommate_two() 
    {
        return $this->roommate_two;
    }

    /**
     * Sets the approved value for the third roommate
     */
    function set_roommate_two_approved($rta)
    {
        $this->roommate_two_approved = $rta;
    }

    /**
     * Returns the approved value for the third roommate
     */
    function get_roommate_two_approved() 
    {
        return $this->roommate_two_approved;
    }

    /**
     * Sets the username for the fourth roommate
     */
    function set_roommate_three($rt)
    {
        $this->roommate_three = $rt;
    }

    /**
     * Returns the username for the fourth roommate
     */ 
    function get_roommate_three()
    {
        return $this->roommate_three;
    }

    /**
     * Sets the approved value for the fourth roommate
     */
    function set_roommate_three_approved($rta)
    {
        $this->roommate_three_approved = $rta;
    }

    /**
     * Returns the approved value for the fourth roommate
     */ 
    function get_roommate_three_approved()
    {
        return $this->roommate_three_approved;
    }

    /**
     * Sets the number of roommates to expect for this object
     */
    function set_number_roommates($number_roommates)
    {
        $this->number_roommates = $number_roommates;
    }

    /**
     * Constructor for the Roommate_Approval class
     * Can be passed the id of a grouping already in the database to
     *   create a new instance of that grouping
     */
    function HMS_Roommate_Approval($id = NULL)
    {
        if($id == NULL) {
            $this->set_values_null();
        }

        return $this;
    }

    /**
     * Sets all member variables to NULL
     */ 
    function set_values_null()
    {
        $this->set_id(NULL);
        $this->set_number_roommates(NULL);
        $this->set_roommate_zero(NULL);
        $this->set_roommate_zero_approved(NULL);
        $this->set_roommate_one(NULL);
        $this->set_roommate_one_approved(NULL);
        $this->set_roommate_two(NULL);
        $this->set_roommate_two_approved(NULL);
        $this->set_roommate_three(NULL);
        $this->set_roommate_three_approved(NULL);
    }

    /**
     * Sets the usernames for each roommate
     */
    function set_roommate_usernames($rz, $ro, $rt = NULL, $rh = NULL)
    {
        $this->set_roommate_zero($rz);
        $this->set_roommate_one($ro);
        $this->set_roommate_two($rt);
        $this->set_roommate_three($rh);
    }

    /**
     * Checks all listed users are valid students
     */
    function check_valid_students($rz, $ro, $rt = NULL, $rh = NULL)
    {
        // ** ERROR MESSAGES NEED TO CHANGE **
        $error = '';

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        if($rz != NULL && !HMS_SOAP::is_valid_student($rz)) {
            $error .= $rz . " is not a valid student for this Housing term.<br />";
        }

        if($ro != NULL && !HMS_SOAP::is_valid_student($ro)) {
            $error .= $ro . " is not a valid student for this Housing term.<br />";
        }

        if($rt != NULL && !HMS_SOAP::is_valid_student($rt)) {
            $error .= $rt . " is not a valid student for this Housing term.<br />";
        }

        if($rh != NULL && !HMS_SOAP::is_valid_student($rh)) {
            $error .= $rh . " is not a valid student for this Housing term.<br />";
        }
    
        return $error;
    }

    /**
     * 
     */
    function has_requested_someone($username = NULL)
    {
        $db = &new PHPWS_DB('hms_roommate_approval');
        $db->addColumn('id');
        if($username == NULL) 
            $db->addWhere('roommate_zero', $_SESSION['asu_username']);
        else 
            $db->addWhere('roommate_zero', $username);
        $exists = $db->select('one');
        if($exists != FALSE || $exists != NULL) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns an error if the genders of the specified users are different
     */
    function check_consistent_genders($rz, $ro, $rt = NULL, $rh = NULL)
    {
        $error = '';
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $g1 = HMS_SOAP::get_gender($rz);
        $g2 = HMS_SOAP::get_gender($ro);
       
        if($g1 != $g2) $error = $rz . " and " . $ro . " must have the same gender.<br />";

        if($rt != NULL) {
            $g3 = HMS_SOAP::get_gender($rt);
            if($g1 != $g3) $error = $rz . " and " . $rt . " must have the same gender.<br />";
            else if($g2 != $g3) $error = $ro . " and " . $rt . " must have the same gender.<br />";
        }

        if($rh != NULL) {
            $g4 = HMS_SOAP::get_gender($rh);
            if($g1 != $g4) $error = $rz . " and " . $rh . " must have the same gender.<br />";
            else if($g2 != $g4) $error = $ro . " and " . $rh . " must have the same gender.<br />";
            else if($g3 != $g4) $error = $rt . " and " . $rh . " must have the same gender.<br />";
        }
        return $error;
    }

    function set_roommate_zero_personal_hash($hash)
    {
        $this->roommate_zero_personal_hash = $hash;
    }

    function set_roommate_one_personal_hash($hash)
    {
        $this->roommate_one_personal_hash = $hash;
    }

    function set_roommate_two_personal_hash($hash)
    {
        $this->roommate_two_personal_hash = $hash;
    }

    function set_roommate_three_personal_hash($hash)
    {
        $this->roommate_three_personal_hash = $hash;
    }

    function get_roommate_zero_personal_hash()
    {
        return $this->roommate_zero_personal_hash;
    }

    function get_roommate_one_personal_hash()
    {
        return $this->roommate_one_personal_hash;
    }

    function get_roommate_two_personal_hash()
    {
        return $this->roommate_two_personal_hash;
    }

    function get_roommate_three_personal_hash()
    {
        return $this->roommate_three_personal_hash;
    }

    /**
     * Sets values for each member variable
     */
    function set_values()
    {
        if(isset($_REQUEST['id']) && $_REQUEST['id'] != NULL && is_numeric($_REQUEST['id'])) {
            $this->set_id($_REQUEST['id']);
        }

        $this->set_number_roommates('2');
        $this->set_roommate_zero($_REQUEST['first_roommate']);
        $this->set_roommate_one($_REQUEST['second_roommate']);
        $this->set_roommate_two(NULL);
        $this->set_roommate_three(NULL);
        $this->set_roommate_zero_approved('0');
        $this->set_roommate_one_approved('0');
        $this->set_roommate_two_approved(NULL);
        $this->set_roommate_three_approved(NULL);
        $this->set_roommate_zero_personal_hash(md5('Who wants to go to ASU?' . $_REQUEST['first_roommate'] . ' does.'));
        $this->set_roommate_one_personal_hash(md5('Who wants to go to ASU?' . $_REQUEST['second_roommate'] . ' does.'));
        $this->set_roommate_two_personal_hash(NULL);
        $this->set_roommate_three_personal_hash(NULL);
    }

    /** 
     * Create a new two person grouping
     * This is the student-specified group
     */
    function save_roommate_username()
    {   
        // Create the roommate group
        $grouping = new HMS_Roommate_Approval();
        $grouping->set_values();
        $grouping->set_roommate_one_approved('1');
  
        // save it
        $db = &new PHPWS_DB('hms_roommate_approval');
        $result = $db->saveObject($grouping);

        if($result == FALSE || $result == NULL) {
            $msg = "There was an error saving your roommate preference. Please contact Housing and Residence Life.";
        } else {
            // email the students
            HMS_Roommate_Approval::email_students($grouping->get_roommate_zero(), $grouping->get_roommate_zero_personal_hash(), 
                                                  $grouping->get_roommate_one(),  $grouping->get_roommate_one_personal_hash());

            // return a success message
            $msg = "Congratulations! " . $grouping->get_roommate_one() . " has been sent an email to confirm your request of that person as your roommate.<br /><br />";
        } 
        $msg .= PHPWS_Text::secureLink(_('Return to Menu'), 'hms', array('type'=>'student', 'op'=>'main'));
        return $msg;
    }   

    /**
     * Emails the up-to four roommates about the roommate request 
     */ 
    function email_students($rz, $rz_hash, $ro, $ro_hash, $rt = NULL, $rh = NULL) 
    {
        PHPWS_Core::initCoreClass('Mail.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        // set tags for the email to the person doing the requesting
        $message = "To:     " . HMS_SOAP::get_first_name($rz) . " " . HMS_SOAP::get_last_name($rz) . "\n"; 
        $message .= "\n";
        $message .= "From:   Housing Management System\n";
        $message .= "\n";
        $message .= "This is a follow-up email to let you know you have requested " . HMS_SOAP::get_first_name($ro) . " " . HMS_SOAP::get_last_name($ro) . " as your roommate.\n";
        $message .= "\n";
        $message .= "We have sent that person an email asking them to accept or reject the invitation. You will be notified\n";
        $message .= "via email when this occurs.\n";
        $message .= "\n";
        $message .= "Please note that you can not reply to this email.";

        // create the Mail object and send it
        $rz_mail = &new PHPWS_Mail;
        $rz_mail->addSendTo($rz . "@appstate.edu");
        $rz_mail->setFrom('hms@tux.appstate.edu');
        $rz_mail->setSubject('HMS Roommate Request');
        $rz_mail->setMessageBody($message);
        $success = $rz_mail->send();
       
        if($success != TRUE) {
            return "There was an error emailing your requested roommate. Please contact Housing and Residence Life.";
        }

        // create the Mail object and send it
        $message = "To:     " . HMS_SOAP::get_first_name($ro) . " " . HMS_SOAP::get_last_name($ro) . "\n";
        $message .= "\n";
        $message .= "From:  Housing Management System\n" ;
        $message .= "\n";
        $message .= "This email is to let you know " . HMS_SOAP::get_first_name($rz) . " " . HMS_SOAP::get_last_name($rz) . " has requested you as a roommate.\n";
        $message .= "\n";
        $message .= "You can *ACCEPT* this invitation by clicking on the following link:\n";
        $message .= "\n";
        $message .= "http://hms.appstate.edu/index.php?module=hms&type=roommate_approval&op=student_approval&hash=" . $ro_hash . "&user=" . $ro;
        $message .= "\n\n";
        $message .= "You can also *REJECT* this invitation by clicking on the following link:\n";
        $message .= "http://hms.appstate.edu/index.php?module=hms&type=roommate_approval&op=student_denial&hash=" . $ro_hash . "&user=" . $ro;
        $message .= "\n\n";
        $message .= "Please note that you can not reply to this email.";

        $ro_mail = &new PHPWS_Mail;
        $ro_mail->addSendTo($ro . '@appstate.edu');
        $ro_mail->setFrom('hms@tux.appstate.edu');
        $ro_mail->setSubject('HMS Roommate Request');
        $ro_mail->setMessageBody($message);
        $success = $ro_mail->send();
    }

    function student_approve_roommates($username, $hash)
    {
        $db = &new PHPWS_DB('hms_roommate_approval');
        $db->addWhere('roommate_one', $username);
        $db->addWhere('roommate_one_personal_hash', $hash);
        $results = $db->select();

        foreach($results as $result) {
            $rz = $result['roommate_zero'];
            $ro = $result['roommate_one'];
        }

        if($results != NULL && $results != FALSE) {
            $success = HMS_Roommate_Approval::make_roommate_group($rz, $ro);
            HMS_Roommate_Approval::email_students_approved($rz, $ro);
            $db = &new PHPWS_DB('hms_roommate_approval');
            $db->addWhere('roommate_one', $username);
            $db->addWhere('roommate_one_personal_hash', $hash);
            $db->delete();
        }

        return "Congratulations! Your roommate has been sent an email saying that you have accepted their invitation!";
    }

    function student_deny_roommates($username, $hash)
    {
        $db = &new PHPWS_DB('hms_roommate_approval');
        $db->addWhere('roommate_one', $username);
        $db->addWhere('roommate_one_personal_hash', $hash);
        $db->delete();

        return "You have rejected that application.";
    }

    function email_students_approved($rz, $ro)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $message =  "To:      " . HMS_SOAP::get_first_name($rz) . " " . HMS_SOAP::get_last_name($rz) . "\n";
        $message .= "         " . HMS_SOAP::get_first_name($ro) . " " . HMS_SOAP::get_last_name($ro) . "\n";
        $message .= "\n";
        $message .= "From:    Housing Management System\n";
        $message .= "\n";
        $message .= "\n";
        $message .= "Congratulations! The following roommate pairing has been successfully entered into the Housing System.\n";
        $message .= "\n";
        $message .= HMS_SOAP::get_first_name($rz) . " " . HMS_SOAP::get_last_name($rz) . "\n";
        $message .= HMS_SOAP::get_first_name($ro) . " " . HMS_SOAP::get_last_name($ro) . "\n";
        $message .= "\n";
        $message .= "You will be notified at a later date regarding your assignment.\n";
        $message .= "Thank you for using the Housing Management System!\n\n";
        $message .= "If this is incorrect please email corrections to hms@tux.appstate.edu.\n";

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = &new PHPWS_Mail;
        $mail->addSendTo($rz . '@appstate.edu');
        $mail->addSendTo($ro . '@appstate.edu');
        $mail->setFrom('hms@tux.appstate.edu');
        $mail->setSubject('HMS Roommate Approval');
        $mail->setMessageBody($message);
        $success = $mail->send();
    }

    function make_roommate_group($rz, $ro)
    {
        $db = &new PHPWS_DB('hms_roommates');
        $db->addValue('roommate_zero', $rz);
        $db->addValue('roommate_one', $ro);
        $success = $db->insert();
    }

    function student_reject_roommate($username, $hash)
    {
        $db = &new PHPWS_DB('hms_roommate_approval');
        $db->addWhere('roommate_one', $username);
        $db->addWhere('roommate_one_personal_hash', $hash);
        $results = $db->select();

        foreach($results as $result) {
            $rz = $result['roommate_zero'];
            $ro = $result['roommate_one'];
        }

        HMS_Roommate_Approval::email_students_rejected($rz, $ro);
        HMS_Roommate_Approval::drop_roommate_approval($rz, $ro);

        return "An email has been sent to " . HMS_SOAP::get_first_name($rz) . " " . HMS_SOAP::get_last_name($rz) . " notifying them of your rejection of their invitation.";
    }

    /**
     * "main" function for the Roommate_Approval class
     * Checks the desired operation and calls the necessary functions
     */
    function main()
    {
        $op = $_REQUEST['op'];

        switch($op)
        {
            default:
                $final =  "Op is: " . $op;
                break;
        }

        return $final;
    }
};

?>
