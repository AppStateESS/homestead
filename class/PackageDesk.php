<?php

/**
 * Represents a package desk where students can pickup a package.
 *
 * @author Jeremy Booker <jb67803@appstate.edu>
 * @package hms
 */
class PackageDesk {

    private $id;
    private $name;

    // A more verbose description of where the find the package desk
    private $location;

    // Pakcage Desk's address
    private $street;
    private $city;
    private $state;
    private $zip;


    /**
     * Constructor
     *
     * @param string $name
     * @param string $location
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zip
     */
    public function __construct($name, $location, $street, $city, $state, $zip)
    {
        $this->setName($name);
        $this->setLocation($location);
        $this->setStreet($street);
        $this->setCity($city);
        $this->setState($state);
        $this->setZip($zip);
    }

    /**
     * Returns this object's database id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets this object's database id
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the name of the package desk
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of this package desk.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the location description
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the location description of this package desk
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Returns the street address
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the street address
     * @param string $street
     */
    public function setStreet($street){
        $this->street = $street;
    }

    /**
     * Returns the city portion of the address
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the state portion of the address
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the state
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Returns the zip code
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the zip code
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
}

/**
 * A child PacakgeDesk class for loading PackageDesk objects from the database
 * @author jbooker
 * @package hms
 */
class RestoredPackageDesk extends PackageDesk {
    /**
     * Empty constructor
     */
    public function __construct(){
    }
}
