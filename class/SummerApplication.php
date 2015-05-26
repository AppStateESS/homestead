<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class SummerApplication extends HousingApplication{

    public $room_type = 0;

    public function __construct($id = 0, $term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $student_type = NULL, $application_term = NULL, $cell_phone = NULL, $meal_plan = NULL, $physical_disability = NULL, $psych_disability = NULL, $gender_need = NULL, $medical_need = NULL, $international = NULL, $room_type = NULL){

        /**
         * If the id is non-zero, then we need to load the other member variables
         * of this object from the database
         */
        if($id != 0){
            $this->id = (int)$id;
            $this->load();
            return;
        }

        $this->application_type = 'summer';

        parent::__construct($term, $banner_id, $username, $gender, $student_type, $application_term, $cell_phone, $meal_plan, $physical_disability, $psych_disability, $gender_need, $medical_need, $international);

        $this->setRoomType($room_type);
    }


    /**
     * Loads the SummerApplication object with the corresponding id. Requires that $this->id be non-zero.
     */
    protected function load()
    {
        if($this->id == 0){
            return;
        }

        # Load the core application data using the parent class
        if(!parent::load()){
            return false;
        }

        # Load the application-specific data
        $db = new PHPWS_DB('hms_summer_application');

        if(PHPWS_Error::logIfError($db->loadObject($this))){
            $this->id = 0;
            return false;
        }

        return true;
    }

    /**
     * Saves this SummerApplication object
     */
    public function save()
    {
        $is_new = $this->getId() == 0 ? true : false;

        # Save the core application data using the parent class
        if(!parent::save()){
            return false;
        }

        # Save the application-specific data
        $db = new PHPWS_DB('hms_summer_application');

        /* If this is a new object, call saveObject with the third parameter as 'false' so
         * the database class will insert the object with the ID set by the parent::save() call.
         * Otherwise, call save object as normal so that the database class will detect the ID and
         * update the object.
         */
        if($is_new){
            $result = $db->saveObject($this, false, false);
        }else{
            $result = $db->saveObject($this);
        }

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_summer_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            return $result;
        }

        if(!parent::delete()){
            return false;
        }

        return TRUE;
    }

    /**
     * Returns the fields specific to the SummerApplications (used in the UnassignedStudents Report).
     *
     * @return Array Array of fields for this SummerApplication.
     */
    public function unassignedStudentsFields()
    {
        $fields = parent::unassignedStudentsFields();

        switch($this->getRoomType()){
            case ROOM_TYPE_DOUBLE:
                $fields['room_type']   = 'Double';
                break;
            case ROOM_TYPE_PRIVATE:
                $fields['room_type']   = 'Private';
                break;
            default:
                $fields['room_type']   = 'Unknown';
                break;
        }

        return $fields;
    }

    /************************
     * Accessors & Mutators *
     ************************/

    public function getRoomType(){
        return $this->room_type;
    }

    public function setRoomType($type){
        $this->room_type = $type;
    }
}

class RestoredSummerApplication extends SummerApplication {
    public function __construct(){}
}
