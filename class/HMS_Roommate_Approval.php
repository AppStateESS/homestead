<?php

class HMS_Roommate_Approval
{

    var $id;
    var $approval_hash;
    var $number_roommates;
    var $room_id;
    var $roommate_zero;
    var $roommate_zero_approved;
    var $roommate_one;
    var $roommate_one_approved;
    var $roommate_two;
    var $roommate_two_approved;
    var $roommate_three;
    var $roommate_three_approved;

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
        $this->set_approval_hash(NULL);
        $this->set_number_roommates(NULL);
        $this->set_room_id(NULL);
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
