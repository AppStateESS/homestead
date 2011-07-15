<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class TestSOAP extends SOAP{

    /**
     * Main public function for getting student info.
     * Used by the rest of the "get" public functions
     * @return SOAP object
     * @throws InvalidArgumentException, SOAPException
     */
    public function getStudentInfo($username, $term)
    {
        // Sanity checking on the username
        if(empty($username) || is_null($username) || !isset($username)){
            throw new InvalidArgumentException('Bad username');
        }

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Bad term');
        }

        //$student->banner_id             = 900325006;
        $student->banner_id             = 900325007;
        $student->last_name             = 'Booker';
        $student->first_name            = 'Jeremy';
        $student->middle_name           = 'Lee';
        $student->dob                   = '1986-09-05';
        $student->gender                = 'M';
        $student->deposit_date          = '';
        $student->deposit_waived        = 'false';

        $student->international         = false;
        $student->student_level         = 'U';

        $student->honors                = true;
        $student->teaching_fellow       = true;
        $student->watauga_member        = true;
        
        $student->disabled_pin			= false;
        $student->housing_waiver		= false;

        //$student->student_type          = 'T';
        //$student->application_term      = '201040';
        //$student->projected_class       = 'FR';

        $student->student_type          = 'F';
        $student->application_term      = '201140';
        $student->projected_class       = 'FR';

        $student->credhrs_completed     = 0;
        $student->credhrs_for_term      = 15;
        $student->on_campus             = 'false';

        $student->address = array();

        // Setup the address object
        $address->atyp_code = 'PS';
        $address->line1     = '123 Rivers St. - PS Address';
        $address->line2     = 'c/o Electronic Student Services';
        $address->line3     = 'Room 267';
        $address->city      = 'Boone';
        $address->county    = '095';
        $address->state     = 'NC';
        $address->zip       = '28608';

        $student->address[] = $address;

        // Setup a second address object
        $address = null;
        $address->atyp_code = 'PR';
        $address->line1     = '123 Rivers Street - PR Address';
        $address->line2     = 'Electronic Student Services';
        $address->line3     = 'Rm 267';
        $address->city      = 'Booone';
        $address->county    = '094';
        $address->state     = 'SC';
        $address->zip       = '28607';

        $student->address[] = $address;

        // Setup an ASU P.O. Box address
        $address = null;
        $address->atyp_code = 'AB';
        $address->line1     = 'ASU Box 32111';
        $address->line2     = '';
        $address->line3     = '';
        $address->city      = 'Booone';
        $address->county    = '095';
        $address->state     = 'SC';
        $address->zip       = '28608';

        $student->address[] = $address;

        // Setup the phone number object
        $phone->area_code   = '123';
        $phone->number      = '4567890';
        $phone->ext         = '1337';

        $student->phone[] = $phone;

        return $student;
    }

    /**
     * Returns the ASU Username for the given banner id
     */
    public function getUsername($bannerId)
    {
        return 'jb67803';
    }

    public function isValidStudent($username, $term)
    {
        return true;
    }

    /**
     * Report that a housing application has been received.
     * Makes First Connections stop bugging the students.
     */
    public function reportApplicationReceived($username, $term)
    {
        //		return false; //error
        return true;
    }

    /**
     * Sends a room assignment to banner. Will cause students to be billed, etc.
     */
    public function reportRoomAssignment($username, $term, $building_code, $room_code, $plan_code, $meal_code)
    {
        //		return false; //error
        return true;
    }

    /**
     * Remove the deletion of a room assignment to Banner.
     * Will cause students to be credited, etc.
     */
    public function removeRoomAssignment($username, $term, $building, $room)
    {
        //		return false; //error
        return true;
    }

    /**
     * Returns a student's current assignment information
     * $opt is one of:
     *  'All'
     *  'HousingApp'
     *  'RoomAssign'
     *  'MealAssign'
     */
    public function getHousMealRegister($username, $term, $opt)
    {
        // Assemble the housing_app object
        $housing_app->plan_code     = 'HOME';
        $housing_app->status_code   = 'AC';
        $housing_app->status_date   = '2007-02-20';

        // Assemble the room_assign object
        $room_assign->bldg_code     = 'JTR';
        $room_assign->room_code     = 02322;
        $room_assign->status_code   = 'AC';
        $room_assign->status_date   = '2008-01-14';

        // Assemble the meal_assign object
        $meal_assign->plan_code     = 1;
        $meal_assign->status_code   = 'AC';
        $meal_assign->status_date   = '2007-11-20';

        // Assemble the final object to be returned
        $hous_meal->housing_app     = $housing_app;
        $hous_meal->room_assign     = $room_assign;
        $hous_meal->meal_assign     = $meal_assign;

        return $hous_meal;
    }
    
    public function getBannerIdByBuildingRoom($building, $room, $term)
    {
    	return null;
    }
}

?>
