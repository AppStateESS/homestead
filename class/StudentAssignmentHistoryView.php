<?php

/**
 * StudentAssignmentHistoryView class - Represents the view for a StudentAssignmentHistory set.
 *
 * @author jbooker
 * @package HMS
 */
class StudentAssignmentHistoryView extends hms\View {

    private $assignmentHistory;

    public function __construct(StudentAssignmentHistory $history)
    {
        $this->assignmentHistory = $history;
    }

    public function show()
    {
        $excess_limit = 3; // Number of rows to show by default

        $count = 0;
        $tpl = array();
        $historyRows = array();
        $excessRows = array();

        $historyArray = $this->assignmentHistory->getHistory();

        foreach($historyArray as $ah){
            $row = array();

            if(defined($ah->assigned_reason)){
                $assignedReason = constant($ah->assigned_reason); // for pretty text purposes
            }else{
                $assignedReason = $ah->assigned_reason;
            }


            if(defined($ah->removed_reason)){
                $removedReason = constant($ah->removed_reason); // for pretty text purposes
            }else{
                $removedReason = $ah->removed_reason;
            }

            if(!is_null($ah->assigned_on)){
                $assignedOn = date('M jS, Y \a\t g:ia', $ah->assigned_on);
            }

            if(!is_null($ah->removed_on)){
                $removedOn = date('M jS, Y \a\t g:ia', $ah->removed_on);
            }

            $bed = new HMS_Bed($ah->getBedId());

            $row['room'] = $bed->where_am_i();
            $row['term'] = Term::toString($ah->term);
             
            // Combine for ease of view
            if(isset($ah->assigned_reason)){
                $row['assignments'] = '<span class="italic">'.$assignedReason.'</span>'.
        	        							' by '.$ah->assigned_by.
        	        							'<br /><span style="font-size:11px;color:#7C7C7C;">on '.$assignedOn.'</span>';
            }else{
                $row['assignments'] = '<span class="disabledText">None</span>';
            }

            if(isset($ah->removed_reason)){
                $row['unassignments'] = '<span class="italic">'.$removedReason.'</span>'.
                        ' by '.$ah->removed_by.
                        '<br /><span style="font-size:11px;color:#7C7C7C;">on '.$removedOn.'</span>';
            }else{
                $row['unassignments'] = '<span class="disabledText">None</span>';
            }
             
            if($count++ < $excess_limit){
                $historyRows[] = $row;
            } else {
                $excessRows[] = $row;
            }
        }

        $tpl['HISTORY'] = $historyRows;
        $tpl['EXTRA_HISTORY'] = $excessRows;

        if(sizeof($historyArray) > $excess_limit){
            $tpl['SHOW_MORE'] = "[ <a id='showMoreLink'>show more</a> ]";
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/StudentAssignmentHistoryView.tpl');
    }
}
?>
