<?php

PHPWS_Core::initModClass('hms', 'AssignmentPairing.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

abstract class RoommatePairingStrategy {

    protected $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    abstract function doPairing(&$applications, &$pairs);

    protected function pairAllowed($requestor, $requestee)
    {
        return $requestor->gender == $requestee->gender;
    }

    protected function createPairing($a, $b)
    {
        // Determine lifestyle option
        $option = LO_COED;
        if($a->lifestyle_option == LO_SINGLE_GENDER || $b->lifestyle_option == LO_SINGLE_GENDER) {
            $option = LO_SINGLE_GENDER;
        }

        // Looks like there is no problem here.
        return new AssignmentPairing(
            StudentFactory::getStudentByUsername($a->username, $this->term),
            Studentfactory::getStudentByUsername($b->username, $this->term),
            $option);
    }
}

?>
