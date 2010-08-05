<?php

class RoomChangeMenuBlockView extends View {

    private $student;
    private $startDate;
    private $endDate;

    public function __construct(Student $student, $startDate, $endDate)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
        //}else if(){ // has pending request
        }else{
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $changeReqCmd = CommandFactory::getCommand('StudentRoomChange');
            $tpl['NEW_REQUEST'] = $changeReqCmd->getLink('request a room change');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/roomChangeMenuBlock.tpl');
    }
}

?>