<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class DamageRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'Damage';
        $this->description = 'Room Damage';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 7;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}

class Damage extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeMenuBlockView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);

        $bed = BedFactory::getBedById($assignment->getBedId(), $this->term);

        $room = RoomFactory::getRoomById($bed->getRoomId(), $this->term);

        $damage = RoomDamage::getDamagesByRoom($room);

        var_dump($damage);exit;

        return new RoomChangeMenuBlockView($student, $this->term, $this->getStartDate(), $this->getEndDate(), $assignment, $request);
    }
}
