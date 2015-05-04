<?php

class PackageDesk extends Report implements iCsvReport{

    const friendlyName = 'Package Desk Roster Export';
    const shortName = 'PackageDesk';

    private $term;
    private $data;

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $this->data = array();
        
        $query = "SELECT hms_assignment.id, hms_assignment.banner_id, hms_assignment.asu_username, hms_new_application.cell_phone, hms_room.room_number, hms_floor.floor_number, hms_residence_hall.hall_name FROM hms_assignment LEFT JOIN (SELECT username, MAX(term) AS mterm FROM hms_new_application GROUP BY username) AS a ON hms_assignment.asu_username = a.username LEFT JOIN hms_new_application ON a.username = hms_new_application.username AND a.mterm = hms_new_application.term LEFT JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id LEFT JOIN hms_room ON hms_bed.room_id = hms_room.id LEFT JOIN hms_floor ON hms_room.floor_id = hms_floor.id LEFT JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id WHERE ( hms_assignment.term = $this->term) ORDER BY hms_residence_hall.id ASC";

        $results = PHPWS_DB::getAll($query);

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException($results->toString());
        }

        foreach($results as $result){
            try{
                $student = StudentFactory::getStudentByBannerId($result['banner_id'], $this->term);
            }catch(Exception $e){
                $this->data[] = array($result['hall_name'],$result['floor_number'],$result['room_number'],'ERROR','ERROR','ERROR',$result['cell_phone'],$result['asu_username']."@appstate.edu");
                continue;
            }

            $this->data[] = array($result['hall_name'],$result['floor_number'],$result['room_number'],$student->getLastName(),$student->getFirstName(),$result['banner_id'],$result['cell_phone'],$result['asu_username'] . "@appstate.edu");
        }
    }
    
    public function getCsvColumnsArray()
    {
        return array("Hall",
                     "Floor",
                     "Room",
                     "First Name",
                     "Last Name",
                     "Banner ID",
                     "Cell Phone Number",
                     "Email Address");
    }
    
    public function getDefaultOutputViewCmd()
    {
        $cmd = CommandFactory::getCommand('ShowReportCsv');
        $cmd->setReportId($this->id);
        
        return $cmd;
    }
    
    public function getCsvRowsArray()
    {
        return $this->data;
    }
    
    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getTerm(){
        return $this->term;
    }
}

?>