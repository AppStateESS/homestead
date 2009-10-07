<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class SummerApplication extends HousingApplication{

    public $room_type = 0;

    public function __construct($id = 0, $term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $student_type = NULL, $application_term = NULL, $cell_phone = NULL, $meal_plan = NULL, $physical_disability = NULL, $psych_disability = NULL, $gender_need = NULL, $medical_need = NULL, $room_type = NULL){
        
        /**
         * If the id is non-zero, then we need to load the other member variables 
         * of this object from the database
         */
        if($id != 0){
            $this->id = (int)$id;
            $this->load();
            return;
        }

        parent::__construct($term, $banner_id, $username, $gender, $student_type, $application_term, $cell_phone, $meal_plan, $physical_disability, $psych_disability, $gender_need, $medical_need);

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
            if(PHPWS_Error::logIfError($db->saveObject($this, false, false))){
                return false;
            }
        }else{
            if(PHPWS_Error::logIfError($db->saveObject($this))){
                return false;
            }
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

    /*
     * Returns the table row tags for the 'unassigned applications report' in
     * HMS_Reports.php
     */
    public function unassigned_applicants_rows()
    {
        $tpl = parent::unassigned_applicants_rows();

        $tpl['ROOM_TYPE']  = $this->getRoomType();

        switch($this->getRoomType()){
            case ROOM_TYPE_DOUBLE:
                $tpl['ROOM_TYPE']   = 'Double';
                break;
            case ROOM_TYPE_PRIVATE:
                $tpl['ROOM_TYPE']   = 'Private';
                break;
            default:
                $tpl['ROOM_TYPE']   = 'Unknown';
                break;
        }

        return $tpl;
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
?>
