<?php

namespace Homestead;

use \Homestead\Exception\StudentNotFoundException;
use \phpws2\Database;

// Seconds of delay you want to replicate for each query.
define('FAKE_SOAP_DELAY', 0);

class FakeSoapTable extends SOAP
{

    /**
     * Main public function for getting student info.
     * Used by the rest of the "get" public functions
     * @return SOAP response object
     * @throws \InvalidArgumentException, SOAPException
     */
    public function getStudentProfile($bannerId, $term)
    {
        // Sanity checking on the username
        if (empty($bannerId) || is_null($bannerId) || !isset($bannerId)) {
            throw new \InvalidArgumentException('Bad BannerId.');
        }

        // Sanity checking on the term
        if (empty($term) || is_null($term) || !isset($term)) {
            throw new \InvalidArgumentException('Bad term');
        }

        $student = new \stdClass();

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM fake_soap WHERE banner_id = :banner_id";
        $stmt = $db->prepare($query);
        $params = array('banner_id' => $bannerId);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new StudentNotFoundException('User not found', 0, $bannerId);
        }
        $student = new \stdClass();
        $student->banner_id = $result['banner_id'];
        $student->user_name = $result['username'];
        $student->last_name = $result['last_name'];
        $student->first_name = $result['first_name'];
        $student->middle_name = $result['middle_name'];
        $student->pref_name = $result['pref_name'];
        $student->dob = $result['dob'];
        $student->gender = $result['gender'];
        $student->deposit_date = ''; // unused but present
        $student->deposit_waived = 'false'; // unused but present
        $student->confid = 'Y';
        $student->international = $result['international'];
        $student->student_level = $result['student_level']; // U-undergrad, G-Graduat']e
        $student->app_decision_code = '1*';
        $student->honors = $result['honors'];
        $student->teaching_fellow = $result['teaching_fellow'];
        $student->watauga_member = $result['watauga_member'];
        $student->greek = $result['greek'];

        $student->disabled_pin = false;
        $student->housing_waiver = $result['housing_waiver'];
        $student->student_type = $result['student_type'];
        $student->application_term = $result['application_term'];
        $student->projected_class = $result['projected_class'];

        $student->credhrs_completed = $result['credhrs_completed'];
        $student->credhrs_for_term = $result['credhrs_for_term'];
        $student->on_campus = 'false'; // unused

        $address_array = unserialize($result['address']);
        $address_object_array = array();
        foreach ($address_array as $add) {
            $address_object_array[] = (object) $add;
        }


        $phone_array = unserialize($result['phone']);
        $phone_object_array = array();
        foreach ($phone_array as $p) {
            $phone_object_array[] = (object) $p;
        }

        $student->address = $address_object_array;
        $student->error_num = 0;
        $student->error_desc = null;
        $student->phone = $phone_object_array;

        $this->createDelay();
        return $student;
    }

    public function createDelay()
    {
        if (FAKE_SOAP_DELAY > 0) {
            sleep(FAKE_SOAP_DELAY);
        }
    }

    /**
     * Returns the ASU Username for the given banner id
     */
    public function getUsername($bannerId)
    {
        $this->createDelay();
        $db = \phpws2\Database::newDB();
        $t = $db->addTable('fake_soap');
        $t->addFieldConditional('banner_id', (string) $bannerId);
        $t->addField('username');
        return $db->selectColumn();
    }

    public function getBannerId($username)
    {
        $this->createDelay();
        $db = \phpws2\Database::newDB();
        $t = $db->addTable('fake_soap');
        $t->addFieldConditional('username', (string) $username);
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
    public function createRoomAssignment($bannerId, $term, $building, $bannerBedId)
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

    public function createMealPlan($bannerId, $term, $mealCode)
    {
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
        $housing_app = new \stdClass();
        $housing_app->plan_code = 'HOME';
        $housing_app->status_code = 'AC';
        $housing_app->status_date = '2007-02-20';

        // Assemble the room_assign object
        $room_assign = new \stdClass();
        $room_assign->bldg_code = 'JTR';
        $room_assign->room_code = 02322;
        $room_assign->status_code = 'AC';
        $room_assign->status_date = '2008-01-14';

        // Assemble the meal_assign object
        $meal_assign = new \stdClass();
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

    public function addRoomDamageToStudentAccount($bannerId, $term, $amount, $damageDescription, $detailCode)
    {
        return true;
    }

    public function moveRoomAssignment(Array $students, $term){
        return true;
    }
}
