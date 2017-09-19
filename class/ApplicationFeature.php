<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;
use \Homestead\Exception\MissingDataException;
use \PHPWS_Error;
use \PHPWS_DB;

/**
 * A class to represent each of the various "features" which can be enabled/disabled
 * for housing applications of a particular term.
 *
 * @package	hms
 * @author Jeremy Booker
 *
 */
abstract class ApplicationFeature
{

    public $id;
    public $term;
    public $name;
    public $enabled;
    public $start_date;
    public $edit_date;
    public $end_date;

    /**
     * Constructor. Loads an application feature object from the database if ID is set.
     * @param Integer $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        if($id != 0) {
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

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Saves the data in this object to the database
     */
    public function save()
    {
        if(!isset($this->name)) {
            $this->name = preg_replace('/(.+\\\)(.+\\\)(.+)/', '$3', get_class($this));
        }

        $missing = $this->validate();
        if(!empty($missing)) {
            throw new MissingDataException('Missing required data.', $missing);
        }

        $db = new PHPWS_DB('hms_application_feature');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
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

        if($reg->requiresEditDate() && !self::validateDate($this->edit_date)) {
            $missing[] = 'edit_date';
        }

        if($reg->requiresEndDate() && !self::validateDate($this->end_date)) {
            $missing[] = 'end_date';
        }

        return $missing;
    }

    private static function validateDate($date)
    {
        if(is_null($date) || empty($date)) {
            return false;
        }

        return true;
    }

    /**
     * Deletes this obejct from the database
     */
    public function delete()
    {
        if(!isset($this->id) || empty($this->id)) {
            return;
        }

        $db = new PHPWS_DB('hms_application_feature');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
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
     * @return View - A View object which can generate the HTML for this block
     */
    public abstract function getMenuBlockView(Student $student);

    /**************************
     * Getter / Setter methods
     */

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    public function getEditDate()
    {
        return $this->edit_date;
    }

    public function setEditDate($edit_date)
    {
        $this->edit_date = $edit_date;
    }

    public function getEndDate()
    {
        return $this->end_date;
    }

    public function setEndDate($end_date)
    {
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

        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/ApplicationFeature';

        $files = scandir("{$dir}/");

        foreach($files as $file) {
            $featureRem = preg_replace('/\.php$/', '', $file);
            $feature = preg_replace('/Registration/', '', $featureRem);
            if($feature == $featureRem) {
                continue;
            }

            $registration = '\\Homestead\\ApplicationFeature\\' . $feature . 'Registration';
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

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if(empty($result) || is_null($result)) {
            return false;
        }

        if(!$feature->showForStudent($student, $term)) {
            return false;
        }

        if($result['enabled'] == 0) {
            return false;
        }

        if(!is_null($result['start_date']) && time() < $result['start_date']) {
            return false;
        }

        if(!is_null($result['end_date']) && time() > $result['end_date']) {
            return false;
        }

        return true;
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
        $db = new PHPWS_DB('hms_application_feature');
        $db->addWhere('enabled', 1);
        $db->addWhere('term', $term);

        $results = $db->select();

        $features = array();
        foreach($results as $result) {
            // Instanciate a registration object
            $regClass = $result['name'] . 'Registration';
            $regClass = '\\Homestead\\ApplicationFeature\\' . $regClass;
            $reg = new $regClass;

            // Check to see if this feature is allowed for this student
            if(!$reg->showForStudent($student, $term)) {
                continue;
            }

            $className = '\\Homestead\\ApplicationFeature\\' . $result['name'];

            // Check for conflicting priorities in the array, make sure we don't overwrite
            // an existing key
            if(array_key_exists($reg->getPriority(), $features)){
                throw new \Exception("Conflicting menu item priorities: {$result['name']}, $term");
            }

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
        if(!isset($name)){
            throw new \InvalidArgumentException('Missing feature name.');
        }

        if(!isset($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

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

        return null;
    }

    public static function getInstanceByName($name)
    {
        $name = '\\Homestead\\ApplicationFeature\\' . $name;
        $f = new $name();
        return $f;
    }

    public static function plugInstance(array $data)
    {
        $f = self::getInstanceByName($data['name']);
        \PHPWS_Core::plugObject($f, $data);
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
