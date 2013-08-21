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
		$this->bed = new HMS_Bed($this->checkin->getBedId());

		$this->application = HousingApplicationFactory::getAppByStudent($this->student, $this->term);

		// Create a dummy application if a real one doesn't exist
		if(!isset($this->application)) {
		    $this->application = new HousingApplication();
		}

		$this->room = $this->bed->get_parent();
		$this->floor = $this->room->get_parent();
		$this->hall = $this->floor->get_parent();

		$this->damages = RoomDamageFactory::getDamagesByRoom($this->room);
		if(sizeof($this->damages) <= 0){
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
