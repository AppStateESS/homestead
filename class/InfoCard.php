<?php

namespace Homestead;

/**
 * Model class for representing InfoCards
 *
 * @author jbooker
 * @package hms
 */
class InfoCard {
    private $checkin;
    private $term;

    private $bannerId;
    private $student;

    private $application;

    private $bed;
    private $room;
    private $floor;
    private $hall;

    private $checkinDamages;
    private $checkoutDamages;

    /**
     * Constructor.
     * Requires a Checkin object to get started.
     *
     * @param Checkin $checkin
     */
    public function __construct(Checkin $checkin)
    {
        $this->checkin = $checkin;

        $this->bannerId = $this->checkin->getBannerId();
        $this->term = $this->checkin->getTerm();

        $this->student = StudentFactory::getStudentByBannerId($this->bannerId, $this->term);

        // Lookup the student's housing application
        $this->application = HousingApplicationFactory::getAppByStudent($this->student, $this->term);

        // Create a dummy application if a real one doesn't exist
        if (!isset($this->application)) {
            $this->application = new HousingApplication();
        }

        // Get the hall, floor, and room from the checkin's bed
        $this->bed = new Bed($this->checkin->getBedId());
        $this->room = $this->bed->get_parent();
        $this->floor = $this->room->get_parent();
        $this->hall = $this->floor->get_parent();

        // Get the damages at check-in time
        $this->checkinDamages = RoomDamageFactory::getDamagesBefore($this->room, ($this->checkin->getCheckinDate() + RoomDamage::SELF_REPORT_DEADLINE));
        if (sizeof($this->checkinDamages) <= 0) {
            $this->checkinDamages = array();
        }

        // If the student has checked out, get the damages at check-out time
        if(!is_null($this->checkin->getCheckoutDate())){
            $this->checkoutDamages = RoomDamageFactory::getDamagesBefore($this->room, $this->checkin->getCheckoutDate());
        }
        if(sizeof($this->checkoutDamages) <= 0){
            $this->checkoutDamages = array();
        }
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function getHall()
    {
        return $this->hall;
    }

    public function getFloor()
    {
        return $this->floor;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getCheckin()
    {
        return $this->checkin;
    }

    public function getCheckinDamages()
    {
        return $this->checkinDamages;
    }

    public function getCheckoutDamages()
    {
        return $this->checkoutDamages;
    }
}
