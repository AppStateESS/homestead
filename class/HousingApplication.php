<?php


abstract class HousingApplication {

    public $id = 0;

    public $term; // The term which this application is for

    public $banner_id;
    public $username;

    public $gender;
    public $application_term; // The term the student started school
    public $cell_phone;

    /**
     * Constructor for the abstract HousingApplication class. It can never be called directly. Instead,
     * it's intended for use by sub-classes.
     *
     * If the object already exists (i.e. has a non-zero 'id'), then it is up to the sub-class
     * to call the 'load()' method in this class to load the core data.
     * 
     * This constrcutor should only be called 
     * remaining parameters are required
     * and this method will handle initializing the values of the core application member variables defined in
     * this class
     */
    public function __construct($term, $banner_id, $username, $gender, $application_term, $cell_phone){
        
        $this->setTerm($term);
        $this->setBannerId($banner_id);
        $this->setUsername($username);
        $this->setGender($gender);
        $this->setApplicationTerm($application_term);
        $this->setCellPhone($cell_phone);
    }

    /**
     * Loads the core housing application data (i.e. the member variables defined in this class).
     * Requires that $this->id be non-zero.
     *
     * Sub-classes can override this method to load their own data specific to that application type.
     * In that case, the sub-class should call parent::load() to execute this method and load the core
     * application data.
     */
    protected function load()
    {
        if($this->id == 0){
            return;
        }

        $db = new PHPWS_DB('hms_new_application');
        if(PHPWS_Error::logIfError($db->loadObject($this))){
            $this->id = 0;
            return false;
        }

        return true;
    }

    /**
     * Saves the core housing application data (i.e. the member variables defined in this class).
     *
     * Sub-classes can override this method to save their own data specific to that application type.
     * In that case, the sub-class should call parent::load() to execute this method and save the care
     * application data.
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_new_application');

        if(PHPWS_Error::logIfError($db->saveObject($this))){
            return false;
        }

        return true;
    }


    /************************
     * Accessors & Mutators *
     ************************/
     
    public function getId(){
        return $this->id;
    }
    
    public function setId($id){
        $this->id = $id;
    }

    public function getTerm(){
        return $this->term;
    }

    public function setTerm($term){
        $this->term = $term;
    }

    public function getBannerId(){
        return $this->banner_id;
    }

    public function setBannerId($id){
        $this->banner_id = $id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getGender(){
        return $this->gender;
    }

    public function setGender($gender){
        $this->gender = $gender;
    }

    public function getApplicationTerm(){
        return $this->application_term;
    }

    public function setApplicationTerm($term){
        $this->application_term = $term;
    }

    public function getCellPhone(){
        return $this->cell_phone;
    }

    public function setCellPhone($phone){
        $this->cell_phone = $phone;
    }

}

?>
