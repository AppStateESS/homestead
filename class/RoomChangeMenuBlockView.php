<?php

class RoomChangeMenuBlockView extends View {

    private $student;
    private $startDate;
    private $endDate;

    public function __construct(Student $student, $startDate, $endDate, $assignment, $changeReq)
    {
        $this->student      = $student;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->assignment   = $assignment;
        $this->changeReq    = $changeReq;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
        }else if(time() > $this->endDate){
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else if(is_null($this->assignment)){ // Not assigned anywhere
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['NOT_ASSIGNED'] = "";
        }else if(!is_null($this->changeReq) && !($this->changeReq->getState() instanceof CompletedChangeRequest) && !($this->changeReq->getState() instanceof DeniedChangeRequest)){ // has pending request
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $tpl['PENDING'] = "";
        }else{
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $changeReqCmd = CommandFactory::getCommand('StudentRoomChange');
            $tpl['NEW_REQUEST'] = $changeReqCmd->getLink('request a room change');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/roomChangeMenuBlock.tpl');
    }
}

?>