<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Bed extends HMS_Item {
    var $bedroom_id       = 0;
    var $bed_letter       = 0;
    var $banner_id        = null;
    var $phone_number     = null;
    var $_curr_assignment = null;
    /**
     * Previous assignments (ie deleted) will be here after loading
     * the current assignment
     * @var array
     */
    var $_prev_assignment = array();

    /**
     * Holds the parent bedroom object of this bed.
     */
    var $_bedroom;

    function HMS_Bed($id = 0)
    {
        $this->construct($id, 'hms_bed');
    }

    function copy($to_term, $bedroom_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_beds, making a copy of this bed<br>";
        
        $new_bed = clone($this);
        $new_bed->reset();
        $new_bed->term    = $to_term;
        $new_bed->bedroom_id = $bedroom_id;
        if (!$new_bed->save()) {
            // There was an error saving the new room
            // Error will be logged.
            //echo "error saving a copy of this bed<br>";
            return false;
        }

        if ($assignments) {
            //echo "loading assignments for this bed<br>";
            $result = $this->loadAssignment();
            if(PEAR::isError($result)){
                //echo "error loading assignments<br>";
                test($result);
                return false;
            }
            
            test($this->_curr_assignment);
            if (isset($this->_curr_assignment)) {
                return $this->_curr_assignment->copy($to_term, $new_bed->id);
            }
        }
    
        //echo "bed copied<br>";
        
        return true;
    }

    function get_row_tags()
    {
        $tpl = $this->item_tags();

        $tpl['BED_LETTER']   = $this->bed_letter;
        $tpl['BANNER_ID']    = $this->banner_id;
        $tpl['PHONE_NUMBER'] = $this->phone_number;

        return $tpl;
    }


    function loadAssignment()
    {
        $assignment_found = false;
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('bed_id', $this->id);
        $db->loadClass('hms', 'HMS_Assignment.php');
        $result = $db->getObjects('HMS_Assignment');

        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            foreach ($result as $ass) {
                if ($ass->deleted == 1) {
                    $this->_prev_assignment[] = $ass;
                } else {
                    if ($assignment_found) {
                        PHPWS_Error::log(HMS_MULTIPLE_ASSIGNMENTS, 'hms', 'HMS_Bed::loadAssignment', 
                                         sprintf('A=%s,B=%s', $ass->id, $this->id));
                    } else {
                        $this->_curr_assignment = $ass;
                        $assignment_found = true;
                    }
                }
            }
        }
    }

    function loadBedroom()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bedroom.php');
        $result = new HMS_Bedroom($this->bedroom_id);
        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        $this->_bedroom = & $result;
        return true;
    }

    function get_parent()
    {
        if(!$this->loadBedroom()){
            return false;
        }

        return $this->_bedroom;
    }

    function get_number_of_assignees()
    {
        $this->loadAssignment();
        return (bool)$this->_curr_assignment ? 1 : 0;
    }

    function save()
    {
        $this->stamp();

        $db = new PHPWS_DB('hms_bed');
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function has_vacancy()
    {
        if($this->get_number_of_assignees() == 0){
            return TRUE;
        }

        return FALSE;
    }
}

?>
