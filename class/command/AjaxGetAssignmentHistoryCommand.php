<?php

/**
* Controller class for retrieving the assignment history for a student in JSON format
*
* @author Chris Detsch
* @package hms
*/

class AjaxGetAssignmentHistoryCommand {

    public function __construct()
    {

    }

    public function execute()
    {
        $banner = $_REQUEST['banner_id'];
        $term = Term::getCurrentTerm();

        $student = StudentFactory::getStudentByBannerId($banner, $term);

        $history = StudentAssignmentHistory::getAssignments($student->getBannerId());
        // var_dump($history);exit;

        $historyArray = array();
        $currentArray = array();

        foreach ($history->getHistory() as $hNode)
        {
            $row = array();
            if(defined($hNode->assigned_reason))
            {
                $assignedReason = constant($hNode->assigned_reason); // for pretty text purposes
            }
            else
            {
                $assignedReason = $hNode->assigned_reason;
            }
            $row['assignedReason'] = $assignedReason;

            if(defined($hNode->removed_reason))
            {
                $removedReason = constant($hNode->removed_reason); // for pretty text purposes
            }
            else
            {
                $removedReason = $hNode->removed_reason;
            }
            $row['removedReason'] = $removedReason;

            if(!is_null($hNode->assigned_on))
            {
                $assignedOn = date('M jS, Y \a\t g:ia', $hNode->assigned_on);
                $assignedBy = $hNode->assigned_by;
            }
            else
            {
                $assignedOn = null;
                $assignedBy = null;
            }
            $row['assignedOn'] = $assignedOn;
            $row['assignedBy'] = $assignedBy;

            if(!is_null($hNode->removed_on))
            {
                $removedOn = date('M jS, Y \a\t g:ia', $hNode->removed_on);
                $removedBy = $hNode->removed_by;
            }
            else
            {
                $removedOn = null;
                $removedBy = null;
            }
            $row['removedOn'] = $removedOn;
            $row['removedBy'] = $removedBy;

            $bed = new HMS_Bed($hNode->getBedId());

            $row['room'] = $bed->where_am_i();
            $row['term'] = Term::toString($hNode->term);
            $row['id']   = $hNode->id;

            $historyArray[] = $row;
        }

        $currentArray['term'] = $term;

        $assignment = HMS_Assignment::getAssignmentByBannerId($banner, $term);
        if($assignment != null)
        {
            $currentArray['id'] = $assignment->getId();
        }
        else
        {
            $currentArray['id'] = null;
        }

        $returnData = array();
        $returnData['history'] = $historyArray;
        $returnData['current'] = $currentArray;

        echo json_encode($returnData);
        exit;
    }
}
