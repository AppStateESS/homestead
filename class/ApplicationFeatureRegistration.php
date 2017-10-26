<?php

namespace Homestead;

/**
 * A class to hold meta-data for ApplicationFeatures.
 *
 * @package hms
 * @author Jeremy Booker
 *
 */
abstract class ApplicationFeatureRegistration
{

    protected $name;
    protected $description;
    protected $startDateRequired;
    protected $editDateRequired;
    protected $endDateRequired;
    protected $priority;

    /**
     * Empty constructor
     */
    public abstract function __construct();

    /**
     * Returns the name of this feature.
     * @return String The name of this feature.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the description of this feature.
     * @return String Feature description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns whether or not a start date is required
     * @return boolean Start date required
     */
    public function requiresStartDate()
    {
        return $this->startDateRequired;
    }

    /**
     * Returns whether or not an edit date is required
     * @return boolean edit date required
     */
    public function requiresEditDate()
    {
        return $this->editDateRequired;
    }

    /**
     * Returns whether or not an edit date is required.
     * @return boolean end date required
     */
    public function requiresEndDate()
    {
        return $this->endDateRequired;
    }

    /**
     * Returns the priority of this feature. Determines the order in which they're displayed.
     * NB: Feature priorities can conflict! Don't give two features the same priority!
     * @returns Integer feature priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Determines whether or not to show a feature for a particular student.
     *
     * @abstract
     * @param Student $student
     * @param Integer $term
     * @return boolean Wether or not to show this feature for a particular student
     */
    public abstract function showForStudent(Student $student, $term);
}
