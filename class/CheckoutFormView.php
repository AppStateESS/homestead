<?php

class CheckoutFormView extends hms\View {

    private $student;
    private $hall;
    private $room;
    private $bed;
    private $damages;
    private $checkin;

    public function __construct(Student $student, HMS_Residence_Hall $hall, HMS_Room $room, HMS_Bed $bed, Array $damages = null, Checkin $checkin)
    {
        $this->student      = $student;
        $this->hall         = $hall;
        $this->room         = $room;
        $this->bed          = $bed;
        $this->damages      = $damages;
        $this->checkin      = $checkin;
    }

    public function show()
    {
        $residentStudents = $this->room->get_assignees();

        $residents = array();

        foreach ($residentStudents as $s) {
            $residents[] = array('studentId' => $s->getBannerId(), 'name' => $s->getName());
        }

        $vars = array();

        $vars['DAMAGE_TYPES'] = json_encode(DamageTypeFactory::getDamageTypeAssoc());
        $vars['ASSIGNMENT'] = json_encode(array(
                                        'studentId' => $this->student->getBannerId(),
                                        'hallName'  => $this->hall->getHallName(),
                                        'roomNumber'=> $this->room->getRoomNumber()
                                        ));
        $vars['RESIDENTS'] = json_encode($residents);
        $vars['STUDENT']   = json_encode(array('studentId' => $this->student->getBannerId(), 'name' => $this->student->getName()));

        $vars['CHECKIN'] = json_encode($this->checkin);

        //$http = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] ? 'https:' : 'http:';
        $vars['JAVASCRIPT_BASE'] = PHPWS_SOURCE_HTTP . 'mod/hms/javascript';

        javascript('jquery');

        // Load header for Angular Frontend
        javascriptMod('hms', 'AngularFrontend', $vars);

        $rawfile = PHPWS_SOURCE_HTTP . 'mod/hms/templates/Angular/checkout.html';
        return '<div data-ng-app="hmsAngularApp"><div data-ng-include="\''.$rawfile.'\'"></div></div>';
    }

}
