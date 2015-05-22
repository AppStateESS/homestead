<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class TestSOAP extends SOAP
{

    /**
     * Main public function for getting student info.
     * Used by the rest of the "get" public functions
     * @return SOAP response object
     * @throws InvalidArgumentException, SOAPException
     */
    public function getStudentProfile($bannerId, $term)
    {
        // Sanity checking on the username
        if (empty($bannerId) || is_null($bannerId) || !isset($bannerId)) {
            throw new InvalidArgumentException('Bad BannerId.');
        }

        // Sanity checking on the term
        if (empty($term) || is_null($term) || !isset($term)) {
            throw new InvalidArgumentException('Bad term');
        }
        
        $response = new stdClass();

        // this term is hardwired for now. It uses the same logic as the creator
        $term = strftime('%Y', time()) . '40';
        
        $db = \Database::newDB();
        $soap_tbl = $db->addTable('fake_soap');
        if (!$soap_tbl->exists()) {
            throw \Exception('fake_soap table does not exist');
        }
        
        // not searching on the term yet since it is hard-coded
        $soap_tbl->addFieldConditional('banner_id', $bannerId);
        $student_array = $db->selectOneRow();
        if  (empty($student_array)) {
            throw new \Exception('User not found');
        }
        extract($student_array);
        $student = new stdClass();
        $response->banner_id = $banner_id;
        $response->user_name = $username;
        $response->last_name = $last_name;
        $response->first_name = $first_name;
        $response->middle_name = $middle_name;
        $response->pref_name = $pref_name;
        $response->dob = $dob;
        $response->gender = $gender;
        $response->deposit_date = ''; // unused but present
        $response->deposit_waived = 'false'; // unused but present
        $response->confid = 'Y';
        $response->international = $international;
        $response->student_level = $student_level; // U-undergrad, G-Graduate
        $response->app_decision_code = '1*';
        $response->honors = $honors;
        $response->teaching_fellow = $teaching_fellow;
        $response->watauga_member = $watauga_member;
        $response->greek = $greek;

        $response->disabled_pin = false;
        $response->housing_waiver = $housing_waiver;
        $response->student_type = $student_type;
        $response->application_term = $term;
        $response->projected_class = $projected_class;

        $response->credhrs_completed = $credhrs_completed;
        $response->credhrs_for_term = $credhrs_for_term;
        $response->on_campus = 'false'; // unused

        $address_array = unserialize($address);
        foreach ($address_array as $add) {
            $address_object_array[] = (object)$add;
        }
        
        $phone_array = unserialize($phone);
        foreach ($phone_array as $p) {
            $phone_object_array[] = (object)$p;
        }
        
        $response->address = $address_object_array;
        $response->error_num = 0;
        $response->error_desc = null;
        $response->phone = $phone_object_array;
        return $response;
    }

    /**
     * Returns the ASU Username for the given banner id
     */
    public function getUsername($bannerId)
    {
        $db = \Database::newDB();
        $t = $db->addTable('fake_soap');
        $t->addFieldConditional('banner_id', (string)$bannerId);
        $t->addField('username');
        return $db->selectColumn();
    }

    public function getBannerId($username)
    {
        $db = \Database::newDB();
        $t = $db->addTable('fake_soap');
        $t->addFieldConditional('username', (string)$username);
        $t->addField('banner_id');
        return $db->selectColumn();
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
    public function removeRoomAssignment($bannerId, $term, $building, $bannerBedId, $percentRefund)
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
        $housing_app->plan_code = 'HOME';
        $housing_app->status_code = 'AC';
        $housing_app->status_date = '2007-02-20';

        // Assemble the room_assign object
        $room_assign = new stdClass();
        $room_assign->bldg_code = 'JTR';
        $room_assign->room_code = 02322;
        $room_assign->status_code = 'AC';
        $room_assign->status_date = '2008-01-14';

        // Assemble the meal_assign object
        $meal_assign = new stdClass();
        $meal_assign->plan_code = 1;
        $meal_assign->status_code = 'AC';
        $meal_assign->status_date = '2007-11-20';

        // Assemble the final object to be returned
        $hous_meal->housing_app = $housing_app;
        $hous_meal->room_assign = $room_assign;
        $hous_meal->meal_assign = $meal_assign;

        return $hous_meal;
    }

    public function getBannerIdByBuildingRoom($building, $room, $term)
    {
        return null;
    }

}

?>
