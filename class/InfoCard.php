<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');

/**
 * Model class for representing InfoCards
 *
 * @author jbooker
 * @package hms
 */
class InfoCard {

	private $checkin;

	private $bannerId;
	private $term;
	private $student;
	private $assignment;
	private $application;

	private $bed;
	private $room;
	private $floor;
	private $hall;

	private $damages;

	/**
	 * Constructor. Requires a Checkin object to get started.
	 * @param Checkin $checkin
	 */
	public function __construct(Checkin $checkin)
	{
		$this->checkin = $checkin;

		$this->bannerId = $this->checkin->getBannerId();
		$this->term = $this->checkin->getTerm();

		$this->student = StudentFactory::getStudentByBannerId($this->bannerId, $this->term);
		$this->assignment = HMS_Assignment::getAssignmentByBannerId($this->bannerId, $this->term);

		$this->application = HousingApplicationFactory::getAppByStudent($this->student, $this->term);

		// Create a dummy application if a real one doesn't exist
		if(!isset($this->application)) {
		    $this->application = new HousingApplication();
		}

		$this->bed = $this->assignment->get_parent();
		$this->room = $this->bed->get_parent();
		$this->floor = $this->room->get_parent();
		$this->hall = $this->floor->get_parent();

		$this->damages = RoomDamageFactory::getDamagesByRoom($this->room);
		if(!isset($damages) || is_null($damages)){
			$this->damages = array();
		}
	}

	public function getStudent(){
		return $this->student;
	}

	public function getHall(){
		return $this->hall;
	}

	public function getFloor(){
		return $this->floor;
	}

	public function getRoom(){
		return $this->room;
	}

	public function getApplication(){
		return $this->application;
	}

	public function getCheckin(){
		return $this->checkin;
	}

	public function getDamages(){
		return $this->damages;
	}
}

?>