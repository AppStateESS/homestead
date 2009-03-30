<?php

class SummerApplication extends HousingApplication{

    public $room_type = 0;

    public function __construct($id = 0, $term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $application_term = NULL, $cell_phone = NULL, $room_type = NULL){
        
        /**
         * If the id is non-zero, then we need to load the other member variables 
         * of this object from the database
         */
        if($id != 0){
            $this->id = (int)$id;
            $this->load();
            return;
        }

        parent::__construct($term, $banner_id, $username, $gender, $application_term, $cell_phone);

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
