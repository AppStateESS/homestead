<?php

define('APPLICATION_FEATURE_DIR', 'applicationFeature');

abstract class ApplicationFeatureRegistration {

    protected $name;
    protected $description;
    protected $startDateRequired;
    protected $endDateRequired;
    protected $priority;

    abstract function __construct();

    function getName(){
        return $this->name;
    }

    function getDescription(){
        return $this->description;
    }

    function requiresStartDate(){
        return $this->startDateRequired;
    }

    function requiresEndDate(){
        return $this->endDateRequired;
    }

    function getPriority(){
        return $this->priority;
    }
    
    public abstract function showForStudent(Student $student, $term);
}

/**
 * A class to represent each of the various "features" which can be enabled/disabled
 * for housing applications of a particular term.
 *
 * @author		Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 * @package		modules
 * @subpackage	hms
 */

abstract class ApplicationFeature {

    public $id;
    public $term;
    public $name;
    public $enabled;
    public $start_date;
    public $end_date;

    /**
     * Constructor. Loads an application feature object from the database if ID is set.
     * @param $id
     */
    public function __construct($id = NULL)
    {
        $this->id = $id;
        
        if($id != 0){
            $this->load();
        } else {
        	$this->enabled = false;
        }
    }

    /**
     * Loads the data for this object from the db, $this->id must be set
     */
    public function load()
    {
        $db = new PHPWS_DB('hms_application_feature');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModclass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Saves the data in this object to the database
     */
    public function save()
    {
    	if(!isset($this->name)) {
    		$this->name = get_class($this);
    	}
    	
    	$missing = $this->validate();
    	if(!empty($missing)) {
    	    PHPWS_Core::initModClass('hms', 'exception/MissingDataException.php');
    	    throw new MissingDataException('Missing required data.', $missing);	
    	} 
    	
        $db = new PHPWS_DB('hms_application_feature');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModclass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
    
    /**
     * Validates this object for saving.
     * @return array An array of fields that are WRONG.
     */
    public function validate()
    {
    	$reg = $this->getRegistration();
        $missing = array();
    	
        if($reg->requiresStartDate() && !self::validateDate($this->start_date)) {
            $missing[] = 'start_date';
        }
        
        if($reg->requiresEndDate() && !self::validateDate($this->end_date)) {
        	$missing[] = 'end_date';
        }
        
        return $missing;
    }
    
    private static function validateDate($date)
    {
    	if(is_null($date) || empty($date)) {
    		return FALSE;
    	}
        
        return TRUE;
    }

    /**
     * Deletes this obejct from the database
     */
    public function delete()
    {
        if(!isset($this->id) || empty($this->id))
        return;

        $db = new PHPWS_DB('hms_application_feature');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModclass('exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
    
    /**
     * Gets this object's registration object
     */
    public function getRegistration()
    {
    	$regClass = get_class($this) . 'Registration';
    	return new $regClass();
    }

    /**
     *
     * @param $student - The student we're generating a menu for.
     * @return String - The HTML for this menu block, for this student.
     */
    public abstract function getMenuBlockView(Student $student);

    /**************************
     * Getter / Setter methods
     */
    
    public function getId() {
    	return $this->id;
    }
    
    public function setId($id) {
    	$this->id = $id;
    }

    public function getTerm(){
        return $this->term;
    }
    
    public function setTerm($term) {
    	$this->term = $term;
    }
    
    public function getName() {
    	return $this->name;
    }
    
    public function setName($name) {
    	$this->name = $name;
    }
    
    public function isEnabled() {
    	return $this->enabled;
    }
    
    public function setEnabled($enabled) {
    	$this->enabled = $enabled;
    }

    public function getStartDate(){
        return $this->start_date;
    }
    
    public function setStartDate($start_date) {
    	$this->start_date = $start_date;
    }

    public function getEndDate(){
        return $this->end_date;
    }
    
    public function setEndDate($end_date) {
    	$this->end_date = $end_date;
    }

    /******************
     * Static Methods *
     */

    /**
     * Returns an array of ApplicationFeatureRegistration objects which represents all possible features.
     *
     * @return Array Array of all possible ApplicationFeatureRegistration objects.
     */
    public static function getFeatures()
    {
        $features = array();

        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . APPLICATION_FEATURE_DIR;

        $files = scandir("{$dir}/");

        foreach($files as $file){
            $feature = preg_replace('/\.php$/', '', $file);
            if($feature == $file) continue;
            PHPWS_Core::initModClass('hms', APPLICATION_FEATURE_DIR . '/' . $file);

            $registration = "{$feature}Registration";
            $features[] = new $registration();
        }

        return $features;
    }

    public static function isEnabledForStudent(ApplicationFeatureRegistration $feature, $term, Student $student)
    {
        $db = new PHPWS_DB('hms_application_feature');
         
        $db->addWhere('name', $feature->getName());
        $db->addWhere('term', $term);
        $db->setLimit(1);
         
        $result = $db->select('row');
         
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(empty($result) || is_null($result)){
            return FALSE;
        }
         
        if(!$feature->showForStudent($student, $term)) {
        	return FALSE;
        }
         
        if(!is_null($result['start_date']) && time() < $result['start_date']){
            return FALSE;
        }
         
        if(!is_null($result['end_date']) && time() > $result['end_date']){
            return FALSE;
        }
         
        return TRUE;
    }

    /**
     * Returns an array of ApplicationFeature objects which are enabled for this term, and for this student
     *
     * @param $student The student to use when checking for enabled features
     * @param $term
     * @return Array
     */
    public static function getEnabledFeaturesForStudent(Student $student, $term)
    {
        $features = array();

        $db = new PHPWS_DB('hms_application_feature');
        $db->addWhere('term', $term);

        $results = $db->select();

        $features = array();
        foreach($results as $result){
            // Instanciate a registration object
            $path = 'applicationFeature/' . $result['name'] . '.php';
            PHPWS_Core::initModClass('hms', $path);
            $regClass = $result['name'] . 'Registration';
            $reg = new $regClass;

            # Check to see if this feature is allowed for this student
            if(!$reg->showForStudent($student, $term)){
                continue;
            }

            $className = $result['name'];

            $features[$reg->getPriority()] = new $className($result['id']);
        }

        ksort($features);
        
        return $features;
    }
    
    public static function getInstanceById($id)
    {
       $db = new PHPWS_DB('hms_application_feature');
       $db->addWhere('id', $id);
       $result = $db->select('row');
        
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        
        return self::plugInstance($result);
    }
    
    public static function getInstanceByNameAndTerm($name, $term)
    {
    	$db = new PHPWS_DB('hms_application_feature');
    	$db->addWhere('name', $name);
    	$db->addWhere('term', $term);
    	$result = $db->select('one');
    	
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
    	
    	if(count($result) > 0) {
    		return self::getInstanceById($result);
    	}
    	
    	/*
    	$f = self::getInstanceByName($name);
    	$f->setTerm($term);
    	return $f;
    	*/

    	return NULL;
    }
    
    public static function getInstanceByName($name)
    {
    	PHPWS_Core::initModClass('hms', APPLICATION_FEATURE_DIR . "/$name.php");
        $f = new $name();
        return $f;
    }
    
    public static function plugInstance(array $data)
    {
    	$f = self::getInstanceByName($data['name']);
    	PHPWS_Core::plugObject($f, $data);
        return $f;
    }
    
    public static function getAllForTerm($term)
    {
    	$db = new PHPWS_DB('hms_application_feature');
    	$db->addWhere('term', $term);
    	$result = $db->select();
        
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        
        $features = array();
        foreach($result as $feature)
        {
        	$f = ApplicationFeature::plugInstance($feature);
        	$features[$f->getName()] = $f;
        }
        
        return $features;
    }
}

?>
