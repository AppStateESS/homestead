<?php

class CheckoutFormView extends hms\View
{
    private $student;
    private $hall;
    private $room;
    private $bed;
    private $damages;
    private $checkin;

    public function __construct(Student $student, HMS_Residence_Hall $hall, HMS_Room $room, HMS_Bed $bed, Array $damages
    = null, Checkin $checkin)
    {
        $this->student = $student;
        $this->hall = $hall;
        $this->room = $room;
        $this->bed = $bed;
        $this->damages = $damages;
        $this->checkin = $checkin;
    }

    public function show()
    {
        $residentStudents = $this->room->get_assignees();
        $home_http = PHPWS_SOURCE_HTTP;

        $residents = array();

        foreach ($residentStudents as $s) {
            $residents[] = array('studentId' => $s->getBannerId(), 'name' => $s->getName());
        }

        $vars = array();
        javascript('jquery');

        // Load header for Angular Frontend

        /**
         * Uncomment below for DEVELOPMENT
         * Comment out for PRODUCTION
         */
        //Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/react/build/react.js'></script>");
        //Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/react/build/JSXTransformer.js'></script>");
        //Layout::addJSHeader("<script type='text/jsx' src='{$home_http}mod/hms/javascript/CheckOut/src/CheckOut.jsx'></script>");

        /**
         * Uncomment below for PRODUCTION
         * Comment out for DEVELOPMENT
         */
        Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/react/build/react.min.js'></script>");
        Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/CheckOut/build/CheckOut.js'></script>");

        /**
         * Remainder of code is untouched regardless of development status
         */
        Layout::addJSHeader("<script type='text/javascript'>var sourceHttp = '{$home_http}';</script>");
        $vars['student'] = $this->student->getFullName();
        $vars['banner_id'] = $this->student->getBannerId();
        $vars['hall_name'] = $this->hall->getHallName();
        $vars['room_number'] = $this->room->getRoomNumber();
        $vars['residents'] = json_encode($residents);
        $vars['checkin_id'] = $this->checkin->id;
        $vars['previous_key_code'] = $this->checkin->key_code;
        $vars['room_pid'] = $this->room->persistent_id;

        $damage_types = DamageTypeFactory::getDamageTypeAssoc();
        foreach ($damage_types as $dt) {
            $damage_options[$dt['category']][] = array('id' => $dt['id'], 'description' => $dt['description']);
        }
        $vars['damage_types'] = json_encode($damage_types);
        if (empty($this->damages)) {
            $vars['existing_damage'] = '[]';
        } else {
            $this->addResponsible($residents);
            $vars['existing_damage'] = json_encode($this->damages);
        }
        $tpl = new \Template($vars);
        $tpl->setModuleTemplate('hms', 'admin/CheckOut.html');
        return $tpl->get();
    }

    private function addResponsible($residents)
    {
        foreach ($residents as $r) {
            $respNames[$r['studentId']] = $r['name'];
        }

        $persistent_id = $this->room->getPersistentId();
        $query = "select banner_id, damage_id from hms_room_damage_responsibility as t1 left join hms_room_damage as t2 on t1.damage_id=t2.id
	where t2.room_persistent_id='$persistent_id'";
        $pdo = PdoFactory::getPdoInstance();
        $result = $pdo->query($query, PDO::FETCH_ASSOC);
        $rows = $result->fetchAll();

        foreach ($rows as $i) {
            if (isset($respNames[$i['banner_id']])) {
                $sdamage[$i['damage_id']][] = $respNames[$i['banner_id']];
            }
        }
        
        foreach ($this->damages as $key => $value) {
            if (isset($sdamage[$value->id])) {
                foreach ($sdamage[$value->id] as $name) {
                    $this->damages[$key]->residents[] = array('name' => $name);
                }
            }
        }
    }

}
