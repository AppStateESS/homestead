<?php

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Suite extends HMS_Item {
    var $floor_id = 0;
    var $_rooms   = array();
    var $_floor   = null;

    function HMS_Suite($id = 0)
    {
        $this->construct($id, 'hms_suite');
    }

    function save()
    {
        $db = new PHPWS_DB('hms_suite');

        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function copy($to_term, $floor_id, $assignments=false)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_suite, copying suite id $this->id <br>";

        // Create a clone of the current suite object
        // Set id to 0, set term, and save
        $new_suite = clone($this);
        $new_suite->reset();
        $new_suite->term = $to_term;
        $new_suite->floor_id = $floor_id;

        if(!$new_suite->save()) {
            // There was an error saving the new suite
            echo "error saving the new suite<br>";
            test($new_suite->save(),1);
            return false;
        }

        if(empty($this->_rooms)) {
            if(!$this->loadRooms()) {
                // There was an error loading the rooms
                echo "Error loading the rooms<br>";
                return false;
            }
        }

        if(!empty($this->_rooms)) {
            foreach ($this->_rooms as $room) {
                //echo "copying room id: $room->id<br>";
                $result = $room->copy($to_term, $floor_id, $new_suite->id, $assignments);
                if(!$result){
                    echo "error copying rooms inside suites<br>";
                    return false;
                }
            }
        }else{
            //echo "rooms empty!<br>";
        }

        return true;
    }


    # The gender of a suite can only be changed if all of the rooms can be changed
    function can_change_gender($target_gender, $ignore_upper = FALSE)
    {
        if(!$this->loadRooms()){
            return false;
        }

        foreach ($this->_rooms as $room){
            if(!$room->can_change_gender($target_gender, $ignore_upper)){
                return false;
            }
        }

        return true;
    }

    /**
     * Pulls all the rooms associated with this floor and stores
     * them in the _room variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     */
    function loadRooms($deleted = 0)
    {
        $db = new PHPWS_DB('hms_room');
        $db->addWhere('floor_id', $this->floor_id);
        $db->addWhere('suite_id', $this->id);

        switch ($deleted) {
            case -1:
                $db->addWhere('deleted', 1);
                break;
            case 0:
                $db->addWhere('deleted', 0);
                break;
        }

        $db->loadClass('hms', 'HMS_Room.php');
        $result = $db->getObjects('HMS_Room');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_rooms = & $result;
            return true;
        }
    }

    /**
     * Loads the parent floor object of this room
     */
    function loadFloor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $result = new HMS_Floor($this->floor_id);
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        $this->_floor = & $result;
        return true;
    }

    function get_rooms()
    {
        if(!$this->loadRooms()){
            return false;
        }

        return $this->_rooms;
    }

    function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id'  );
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_bedroom', 'bedroom_id', 'id' );
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room', 'room_id', 'id'   );
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_suite', 'suite_id', 'id');

        $db->addWhere('hms_assignment.deleted', 0);
        $db->addWhere('hms_bed.deleted',        0);
        $db->addWhere('hms_bedroom.deleted',    0);
        $db->addWhere('hms_room.deleted',       0);
        $db->addWhere('hms_suite.deleted',      0);

        $db->addWhere('hms_suite.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*****************
    * Static Methods *
    *****************/
    
    function main()
    {
        switch($_REQUEST['op']){
            case 'show_select_suite':
                return HMS_Suite::show_select_suite('Edit Suite', 'suite', 'show_edit_suite');
                break;
            case 'show_edit_suite':
                return HMS_Suite::show_edit_suite();
                break;
            case 'edit_suite_submit':
                return HMS_Suite::edit_suite_submit();
                break;
            default:
                return "Unknown suite op: {$_REQUEST['op']}";
                break;
        }

    }

    function edit_suite_submit()
    {
        # Create the suite object
        $suite = &new HMS_Suite($_REQUEST['suite_id']);
        if(!$suite){
            return HMS_Suite::show_select_suite('Edit Suite', 'suite', 'show_edit_suite', NULL, 'Error: The select suite does not exist');
        }

        # Determine the gender of the suite by looking at all the rooms
        # If there are any rooms, then get the gender of the first room
        # and compare it to the rest of the rooms' genders.
        $rooms = $suite->get_rooms();
        if(isset($rooms)){
            $suite_gender = $rooms[0]->gender_type;
            foreach($rooms as $room){
                if($suite_gender != $room->gender_type){
                    $tpl['ERROR_MSG'] = 'The rooms in this suite are not all of the same gender. Please contact ESS!';
                    return PHPWS_Template::process($tpl, 'hms', 'admin/edit_suite.tpl');
                }
            }
        }

        # Compare the "suite gender" found above and the gender submited.
        if($suite_gender != $_REQUEST['gender_type']){
            if(!$suite->can_change_gender($_REQUEST['gender_type'])){
                return HMS_Suite::show_edit_suite($suite->id, NULL, 'Error: Incompatible gender detected. No changes were made.');
            }
        }

        # Set the new gender on each room.
        $rooms = $suite->get_rooms();
        $db = &new PHPWS_DB('hms_room');
        $db->query('BEGIN');
        
        foreach($rooms as $room){
            $room->gender_type = $_REQUEST['gender_type'];
            $result = $room->save();
            if(!$result || PHPWS_Error::logIfError($result)){
                $db->query('ROLLBACK');
                return HMS_Suite::show_edit_suite($suite->id, NULL, 'Error: There was a problem updating the database. No changes were made.');
            }
        }

        $db->query('COMMIT');

        return HMS_Suite::show_edit_suite($suite->id, 'Suite updated successfully.');
    }

    /*********************
     * Static UI Methods *
     ********************/
    function show_select_suite($title, $type, $op, $success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_suite');

        $tpl = array();

        # Setup the title and color of the title bar
        $tpl['TITLE']       = $title;
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        $tpl['MESSAGE']     = 'Please select a suite: ';

        # Get the halls for the selected term
        $halls = HMS_Residence_Hall::get_halls_array(HMS_Term::get_selected_term());

        # Show an error if there are no halls for the current term
        if($halls == NULL){
           $tpl['ERROR_MSG'] = 'Error: No halls exist for the selected term. Please create a hall first.';
           return PHPWS_Template::process($tpl, 'hms', 'admin/select_suite.tpl'); 
        }

        $halls[0] = 'Select...';

        # Setup the form
        $form = &new PHPWS_Form;
        $form->setMethod('get');
        $form->addDropBox('residence_hall', $halls);
        $form->setLabel('residence_hall', 'Residence hall: ');
        $form->setMatch('residence_hall', 0);
        $form->setExtra('residence_hall', 'onChange="handle_hall_change()"');

        $form->addDropBox('floor', array(0 => ''));
        $form->setLabel('floor', 'Floor: ');
        $form->setExtra('floor', 'disabled onChange="handle_floor_change()"');

        $form->addDropBox('suite', array(0 => ''));
        $form->setLabel('suite', 'Suite: ');
        $form->setExtra('suite', 'disabled onChange="handle_suite_change()"');

        # Use the type and op that was passed in
        $form->addHidden('module', 'hms');
        $form->addHidden('type', $type);
        $form->addHidden('op', $op);

        $form->addSubmit('submit', 'Select');
        $form->setExtra('submit', 'disabled');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/select_suite.tpl');
    }

    function show_edit_suite($suite_id = NULL, $success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        # Determine the suite id
        if(!isset($suite_id)){
            $suite_id = $_REQUEST['suite'];
        }

        # Setup the title and color of the title bar
        $tpl['TITLE']       = 'Edit Suite';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Create the suite object
        $suite = &new HMS_Suite($suite_id);

        # Create the floor object
        $floor = &new HMS_Floor($suite->floor_id);

        # Create the hall object
        $hall = &new HMS_Residence_Hall($floor->residence_hall_id);

        $tpl['HALL_NAME']       = $hall->hall_name;
        $tpl['FLOOR_NUMBER']    = $floor->floor_number;

        $tpl['ROOM_PAGER']      = HMS_Room::get_room_pager_by_suite($suite_id);
        $tpl['ASSIGNMENT_PAGER']= HMS_Assignment::get_assignment_pager_by_suite($suite_id);

        # Determine the gender of the suite by looking at all the rooms
        # If there are any rooms, then get the gender of the first room
        # and compare it to the rest of the rooms' genders.
        $rooms = $suite->get_rooms();
        $suite_gender = NULL;
        if(isset($rooms)){
            $suite_gender = $rooms[0]->gender_type;
            foreach($rooms as $room){
                if($suite_gender != $room->gender_type){
                    $tpl['ERROR_MSG'] = 'The rooms in this suite are not all of the same gender. Please contact ESS!';
                    return PHPWS_Template::process($tpl, 'hms', 'admin/edit_suite.tpl');
                }
            }
        }

        $form = &new PHPWS_Form;

        if($suite->get_number_of_assignees() == 0){
            # All rooms in the suite are empty, show the drop down
            $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC));
            $form->setMatch('gender_type', $suite_gender);
            $form->addSubmit('submit', 'Submit');
        }else{
            # Suite is not empty so just show the gender (no drop down)
            if($suite_gender == FEMALE){
                $tpl['GENDER_MESSAGE'] = "Female";
            }else if($suite_gender == MALE){
                $tpl['GENDER_MESSAGE'] = "Male";
            }else if($suite_gender == COED){
                $tpl['GENDER_MESSAGE'] = "Coed";
            }else{
                $tpl['GENDER_MESSAGE'] = "Error: Undefined gender";
            }
        }
        
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'suite');
        $form->addHidden('op', 'edit_suite_submit');
        $form->addHidden('suite_id', $suite->id);

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_suite.tpl');
    }
}
?>
