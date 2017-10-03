<?php

namespace Homestead;

class CheckoutFormView extends View
{
    private $student;
    private $hall;
    private $room;
    private $bed;
    private $damages;
    private $checkin;

    public function __construct(Student $student, ResidenceHall $hall, Room $room, Bed $bed, Array $damages = null, Checkin $checkin)
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

        $tpl = array();
        $tpl['STUDENT'] = $this->student->getFullName();
        $tpl['BANNER_ID'] = $this->student->getBannerId();
        $tpl['HALL_NAME'] = $this->hall->getHallName();
        $tpl['room_number'] = $this->room->getRoomNumber();
        $tpl['RESIDENTS'] = json_encode($residents);
        $tpl['CHECKIN_ID'] = $this->checkin->id;
        $tpl['PREVIOUS_KEY_CODE'] = json_encode($this->checkin->key_code);
        $tpl['ROOM_PID'] = $this->room->persistent_id;

        $damage_types = DamageTypeFactory::getDamageTypeAssoc();

        $damage_options = array();
        foreach ($damage_types as $dt) {
            $damage_options[$dt['category']][] = array('id' => $dt['id'], 'description' => $dt['description']);
        }
        $tpl['DAMAGE_TYPES'] = json_encode($damage_types);
        if (empty($this->damages)) {
            $tpl['EXISTING_DAMAGE'] = '[]';
        } else {
            $this->addResponsible($residents);
            $tpl['EXISTING_DAMAGE'] = json_encode($this->damages);
        }

        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'emergencyContact');

        return \PHPWS_Template::process($tpl, 'hms', 'admin/CheckOut.tpl');
    }

    private function addResponsible($residents)
    {
        $respNames = array();
        foreach ($residents as $r) {
            $respNames[$r['studentId']] = $r['name'];
        }

        $persistent_id = $this->room->getPersistentId();
        $query = "select banner_id, damage_id from hms_room_damage_responsibility as t1 left join hms_room_damage as t2 on t1.damage_id=t2.id where t2.room_persistent_id='$persistent_id'";
        $pdo = PdoFactory::getPdoInstance();
        $result = $pdo->query($query, \PDO::FETCH_ASSOC);
        $rows = $result->fetchAll();

        $sdamage = array();

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
