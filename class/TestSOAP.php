<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class TestSOAP extends SOAP{

    /**
     * Main public function for getting student info.
     * Used by the rest of the "get" public functions
     * @return SOAP response object
     * @throws InvalidArgumentException, SOAPException
     */
    public function getStudentProfile($bannerId, $term)
    {
        // Sanity checking on the username
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Bad BannerId.');
        }

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Bad term');
        }

        $response = new stdClass();

        $student = new stdClass();
        $response->banner_id             = 900325006;
        $response->user_name			 = 'jb67803';
        $response->last_name             = 'Booker';
        $response->first_name            = 'Jeremy';
        $response->middle_name           = 'Lee';
        $response->pref_name		     = 'J-dogg';
        $response->dob                   = '1986-09-05';
        $response->gender                = 'M';
        $response->deposit_date          = '';
        $response->deposit_waived        = 'false';

        $response->confid				 = 'Y'; // TODO double check this value

        $response->international         = false;
        $response->student_level         = 'U';
        $response->app_decision_code     = '1*';

        $response->honors                = true;
        $response->teaching_fellow       = true;
        $response->watauga_member        = true;
        $response->greek                 = 'Y'; //TODO double check this value

        $response->disabled_pin			 = false;
        $response->housing_waiver		 = false;

        //$response->student_type          = 'T';
        //$response->application_term      = '201040';
        //$response->projected_class       = 'FR';

        $response->student_type          = 'F';
        $response->application_term      = '201240';
        $response->projected_class       = 'FR';

        $response->credhrs_completed     = 0;
        $response->credhrs_for_term      = 15;
        $response->on_campus             = 'false';

        $response->address = array();

        // Error fields
        $response->error_num = 0;
        $response->error_desc = null;

        // Setup the address object
        $address = new stdClass();;
        $address->atyp_code = 'PS';
        $address->line1     = '123 Rivers St. - PS Address';
        $address->line2     = 'c/o Electronic Student Services';
        $address->line3     = 'Room 267';
        $address->city      = 'Boone';
        $address->county    = '095';
        $address->state     = 'NC';
        $address->zip       = '28608';

        $response->address[] = $address;

        // Setup a second address object
        $address = new stdClass();
        $address->atyp_code = 'PR';
        $address->line1     = '123 Rivers Street - PR Address';
        $address->line2     = 'Electronic Student Services';
        $address->line3     = 'Rm 267';
        $address->city      = 'Booone';
        $address->county    = '094';
        $address->state     = 'SC';
        $address->zip       = '28607';

        $response->address[] = $address;

        // Setup an ASU P.O. Box address
        $address = new stdClass();
        $address->atyp_code = 'AB';
        $address->line1     = 'ASU Box 32111';
        $address->line2     = '';
        $address->line3     = '';
        $address->city      = 'Booone';
        $address->county    = '095';
        $address->state     = 'SC';
        $address->zip       = '28608';

        $response->address[] = $address;

        // Setup the phone number object
        $phone = new stdClass();
        $phone->area_code   = '123';
        $phone->number      = '4567890';
        $phone->ext         = '1337';

        $response->phone[] = $phone;

        return $response;
    }

    /**
     * Returns the ASU Username for the given banner id
     */
    public function getUsername($bannerId)
    {
        return 'jb67803';
    }

    public function getBannerId($username)
    {
        return '900325006';
    }

    public function isValidStudent($username, $term)
    {
        return true;
    }


    public function hasParentPin($bannerId)
    {
        //TODO
        return true;
    }

    public function getParentAccess($bannerId, $parentPin)
    {
        // TODO
    }

    /**
     * Report that a housing application has been received.
     * Makes First Connections stop bugging the students.
     */
    public function createHousingApp($bannerId, $term)
    {
        //		return false; //error
        return true;
    }

    /**
     * Sends a room assignment to banner. Will cause students to be billed, etc.
     */
    public function createRoomAssignment($bannerId, $term, $building, $bannerBedId, $plan, $meal)
    {
        //		return false; //error
        return true;
    }

    /**
     * Remove the deletion of a room assignment to Banner.
     * Will cause students to be credited, etc.
     */
    public function removeRoomAssignment($bannerId, $term, $building, $bannerBedId)
    {
        //		return false; //error
        return true;
    }


    public function setHousingWaiver($bannerId, $term)
    {
        return true;
    }

    public function clearHousingWaiver($bannerId, $term)
    {
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
        $housing_app = new stdClass();
        $housing_app->plan_code     = 'HOME';
        $housing_app->status_code   = 'AC';
        $housing_app->status_date   = '2007-02-20';

        // Assemble the room_assign object
        $room_assign = new stdClass();
        $room_assign->bldg_code     = 'JTR';
        $room_assign->room_code     = 02322;
        $room_assign->status_code   = 'AC';
        $room_assign->status_date   = '2008-01-14';

        // Assemble the meal_assign object
        $meal_assign = new stdClass();
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
