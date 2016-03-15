<?php

PHPWS_Core::initModClass('hms', 'RoomFactory.php');
PHPWS_Core::initModClass('hms', 'RoomDamage.php');

class AddRoomDamageCommand extends Command {

    private $room;

    public function setRoom(HMS_Room $room)
    {
        $this->room = $room;
    }

    public function getRequestVars()
    {
        return array('action'=> 'AddRoomDamage',
                     'roomId'=> $this->room->getPersistentId(),
                     'term'  => $this->room->getTerm());
    }

    public function execute(CommandContext $context)
    {
        if(UserStatus::isUser())
        {
            $term = Term::getSelectedTerm();
            $username = UserStatus::getUsername();            
            $student = StudentFactory::getStudentByUsername($username, $term);
            $checkin = CheckinFactory::getCheckinByBannerId($student->getBannerId(), $term);
            $end = strtotime('+2 days', $checkin->getCheckinDate());
            if(time() > $end)
            {
                echo json_encode(array('status' => 'The period to add room damages have passed, as it has been more than 48 hours.'));
                exit;
            }
        }
        $roomId = $context->get('roomPersistentId');
        $damageType = $context->get('damageType');
        $term = Term::getSelectedTerm();
        $side = $context->get('side');
        $note = $context->get('description');

        $room = RoomFactory::getRoomByPersistentId($roomId, $term);

        $damage = new RoomDamage($room, $term, $damageType, $side, $note);

        RoomDamageFactory::save($damage);

        echo json_encode(array('status' => 'success'));
        exit;
    }
}
