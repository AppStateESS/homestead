<?php

class RoomChangeListView extends View {

    private $requests;
    private $term;

    public function __construct(Array $roomChangeRequests, $term)
    {
        $this->requests = $roomChangeRequests;
        $this->term = $term;
    }

    public function show()
    {
        // Check for an empty array of requests
        if (sizeof($this->requests) == 0) {
            $tpl['NO_REQUESTS'] = 'No pending requests found.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/roomChangeListView.tpl');
        }

        $tpl['REQUESTS'] = array();
        foreach($this->requests as $request){
            $row = array();

            $participants = $request->getParticipants();

            $participantNames = array();
            foreach ($participants as $p){
                $student = StudentFactory::getStudentByBannerId($p->getBannerId(), $this->term);
                $participantNames[] = $student->getName();
            }

            $row['participants'] = implode(', ', $participantNames);
            $mgmtCmd = CommandFactory::getCommand('ShowManageRoomChange');
            $mgmtCmd->setRequestId($request->getId());

            $row['manage'] = $mgmtCmd->getLink('manage');

            $tpl['REQUESTS'][] = $row;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/roomChangeListView.tpl');
    }
}

?>