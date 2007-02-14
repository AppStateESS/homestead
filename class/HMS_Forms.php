<?php

/**
 * Form objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Form
{

    var $error;

    function HMS_Form()
    {
        $this->error = "";
    }
    
    function set_error_msg($msg)
    {
        $this->error .= $msg;
    }

    function get_error_msg()
    {
        return $this->error;
    }

    function valid_search_request() {
        if($_REQUEST['first_name'] ||
           $_REQUEST['last_name'] ||
           $_REQUEST['asu_username'])
            return TRUE;
        else 
            return FALSE;
    }

    function get_matching_students()
    {
        if(HMS_Form::valid_search_request() == FALSE) {
            $error = "You did not provide any search criteria.<br />";
            $error .= "Please enter something to search by.<br />";
            return HMS_Form::enter_student_search_data($error);
        }

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = &new DBPager('hms_student', 'HMS_Student');
        $pager->setModule('hms');
        $pager->setTemplate('admin/studentList.tpl');
        
        if($_REQUEST['last_name']) {
            $pager->db->addWhere('last_name', '%' . $_REQUEST['last_name'] . '%', 'ILIKE');
        }
        
        if($_REQUEST['first_name']) {
            $pager->db->addWhere('first_name', '%' . $_REQUEST['first_name'] . '%', 'ILIKE');
        }

        if($_REQUEST['asu_username']) {
            $pager->db->addWhere('asu_username', '%' . $_REQUEST['asu_username'] . '%', 'ILIKE');
        }

        $pager->addRowTags('get_row_pager_tags');
        $pager->db->addOrder('last_name');
        $pager->db->addOrder('first_name');
        return $pager->get();
    }

    function search_residence_halls()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = & new PHPWS_Form;

        $terms = array('0'=>"",
                       '1'=>"Spring",
                       '2'=>"Summer I",
                       '3'=>"Summer II",
                       '4'=>"Fall");
        $form->addDropBox('term', $terms);

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('is_online', '1');
        $results = $db->select();
        
        if($results != NULL && $results != FALSE) {
            foreach($results as $result) {
                $halls[$result['id']] = $result['hall_name'];
            }
            $form->addDropBox('hall', $halls);
        } else {
            $form->addDropBox('hall', array(''=>"Please make sure at least ONE hall is added and online!"));
        }

        $floors = array('', 1,2,3,4,5,6,7,8,9,10);
        $form->addDropBox('floor',$floors);

        $form->addText('room');
        $form->addText('bed');

        $form->addRadio('smoking', array(0, 1, 2));
        $form->setLabel('smoking', array(_("Yes"), _("No"), _("Unknown")));
        $form->setMatch('smoking', '2');
        
        $form->addRadio('type', array(0, 1, 2));
        $form->setLabel('type', array(_("Single"), _("Co-ed"), _("Unknown")));
        $form->setMatch('type', '2');
        
        $form->addRadio('status', array(0, 1, 2));
        $form->setLabel('status', array(_("Online"), _("Offline"), _("Unknown")));
        $form->setMatch('status', '2');
        
        $form->addHidden('module', 'hms');
        $form->addHidden('op', 'display_residence_hall');
        $form->addSubmit('submit', _('Search Halls'));
        $tpl = $form->getTemplate();
        $tpl['ERROR'] = $this->error;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/search_residence_halls_radio.tpl');
        return $final;
    }

    function get_usernames_for_new_grouping($error)
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('first_roommate');
        $form->addText('second_roommate');
        $form->addText('third_roommate');
        $form->addText('fourth_roommate');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'roommate');
        $form->addHidden('op', 'save_grouping');
        $form->addSubmit('submit', _('Submit usernames'));

        $tpl = $form->getTemplate();
        $tpl['ERROR'] = $error;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_roommate_usernames.tpl');
        return $final;
    }

    function get_username_for_edit_grouping($error)
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('username');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'roommate');
        $form->addHidden('op', 'select_username_for_edit_grouping');
        $form->addSubmit('submit', _('Search for Grouping'));

        $tpl = $form->getTemplate();
        $tpl['ERROR']   = $error;
        $tpl['MESSAGE'] = "Please enter one of the ASU usernames in the roommate grouping you wish to edit:";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_single_username.tpl');
        return $final;
    }

    function get_username_for_assignment($error)
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('username');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'get_hall_floor_room');
        $form->addSubmit('submit', _('Submit User'));

        $tpl = $form->getTemplate();
        $tpl['ERROR']   = $error;
        $tpl['MESSAGE'] = "Please enter an ASU username to assign:";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_single_username.tpl');
        return $final;
    }

    function get_username_for_deletion($error)
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('username');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'verify_deletion');
        $form->addSubmit('submit', _('Submit User'));

        $tpl = $form->getTemplate();
        $tpl['ERROR']   = $error;
        $tpl['MESSAGE'] = "Please provide the ASU username of the student whose room assignment will be deleted.";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_single_username.tpl');
        return $final;
    }

    function get_hall_floor_room($error = NULL)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('asu_username', $_REQUEST['username'], 'ILIKE');
        $assignment = $db->select('row');
        $msg = '';
        if(!is_null($assignment)) {
            $db = new PHPWS_DB('hms_residence_hall');
            $db->addColumn('hall_name');
            $db->addWhere('id', $assignment['building_id']);
            $hall = $db->select('one');

            $db = new PHPWS_DB('hms_room');
            $db->addColumn('room_number');
            $db->addWhere('id', $assignment['room_id']);
            $room_number = $db->select('one');

            $msg .= "<font color=\"red\"><b>";
            $msg .= $_REQUEST['username'] . " is already assigned to " . $hall . " room " . $room_number . "<br />";
            $msg .= "Warning! This will overwrite the current assignment!<br /><br />";
           
            $db = new PHPWS_DB('hms_assignment');
            $db->addColumn('asu_username');
            $db->addWhere('room_id', $assignment['room_id']);
            $db->addWhere('asu_username', $_REQUEST['username'], '!=');
            $db_assignments = $db->select();
            if(!is_null($db_assignments)) {
                $msg .= $_REQUEST['username'] . " has roommates. These roommates are: <br />";
                foreach($db_assignments as $roommates) {
                    $msg .= $roommates['asu_username'] . "<br />";
                }
            }
            $msg .= "<br /></font></b>";
        }

        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('deleted', '1', '!=');
        $halls_raw = $db->select();

        foreach($halls_raw as $hall) {
            $halls[$hall['id']] = $hall['hall_name'];
        }

        for($i = 1; $i <= 15; $i++) {
            $floors[$i] = $i;
        }

        for ($i = 1; $i <= 30; $i++) {
            $rooms[$i] = $i;
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addDropBox('halls', $halls);
        $form->addDropBox('floors', $floors);
        $form->addDropBox('rooms', $rooms);
        
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'verify_assignment');
        $form->addHidden('username', $_REQUEST['username']);
        $form->addSubmit('submit', _('Assign Room'));

        $tpl = $form->getTemplate();
        $tpl['MESSAGE'] =  "<h2>Assigning Student: " . $_REQUEST['username'] . "</h2><br />";
        $tpl['MESSAGE'] .= $error;
        $tpl['MESSAGE'] .= $msg;
        $tpl['MESSAGE'] .= "Please select a Hall, Floor and Room.";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_hall_floor_room.tpl');

        return $final;
    }

    function verify_assignment()
    {
        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hall_name');
        $db->addWhere('id', $_REQUEST['halls']);
        $hall_name = $db->select('one');

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'create_assignment');
        $form->addHidden('username', $_REQUEST['username']);
        $form->addHidden('hall', $_REQUEST['halls']);
        $form->addHidden('floor', $_REQUEST['floors']);
        $form->addHidden('room', $_REQUEST['rooms']);
        $form->addSubmit('submit', _('Assign Student'));

        $tpl = $form->getTemplate();
        $tpl['MESSAGE'] = "<h2>You are assigning user: " . $_REQUEST['username'] . "</h2>";
        $tpl['HALLS']   = $hall_name; 
        $tpl['FLOORS']  = $_REQUEST['floors'];
        $tpl['ROOMS']   = $_REQUEST['rooms'];

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_hall_floor_room.tpl');
        return $final;
    }

    function verify_deletion()
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('asu_username', $_REQUEST['username'], 'ILIKE');
        $assignment = $db->select('row');

        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hall_name');
        $db->addWhere('id', $assignment['building_id']);
        $hall_name = $db->select('one');

        $db = new PHPWS_DB('hms_room');
        $db->addColumn('room_number');
        $db->addColumn('floor_number');
        $db->addWhere('id', $assignment['room_id']);
        $hms_room_info = $db->select('row');
        $room_number = $hms_room_info['room_number'];
        $floor_number = $hms_room_info['floor_number'];

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'delete_assignment');
        $form->addHidden('assignment_id', $assignment['id']);
        $form->addHidden('asu_username', $assignment['asu_username']);
        $form->addHidden('hall_name', $hall_name);
        $form->addHidden('room_number', $room_number);
        $form->addSubmit('submit', _('Delete Assignment'));

        $tpl = $form->getTemplate();
        $tpl['MESSAGE'] = "<h2>You are deleting the room assignment for: " . $assignment['asu_username'] . "</h2>";
        $tpl['HALLS']   = $hall_name; 
        $tpl['FLOORS']  = $floor_number;
        $tpl['ROOMS']   = $room_number;

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/get_hall_floor_room.tpl');
        return $final;
    }

    function select_username_for_edit_grouping()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = &new DBPager('hms_roommates', 'HMS_Roommate');
        $pager->setModule('hms');
        $pager->setTemplate('admin/roommate_search_results.tpl');
        
        $pager->db->addWhere('roommate_zero', '%' . $_REQUEST['username'] . '%', 'ILIKE', 'OR');
        $pager->db->addWhere('roommate_one', '%' . $_REQUEST['username'] . '%', 'ILIKE', 'OR');
        $pager->db->addWhere('roommate_two', '%' . $_REQUEST['username'] . '%', 'ILIKE', 'OR');
        $pager->db->addWhere('roommate_three', '%' . $_REQUEST['username'] . '%', 'ILIKE', 'OR');

        $pager->addRowTags('get_row_pager_tags');
        return $pager->get();
    }

    function edit_grouping()
    {
        if(isset($_REQUEST['id'])) {
            $db = new PHPWS_DB('hms_roommates');
            $db->addWhere('id', $_REQUEST['id']);
           
            PHPWS_Core::initModClass('hms', 'HMS_Roommate');

            $grouping = new HMS_Roommate;
            $grouping_id = $db->loadObject($grouping);

            PHPWS_Core::initCoreClass('Forms.php');
            $form = new PHPWS_Form;
            $form->addText('first_roommate', $grouping->get_roommate_zero());
            $form->addText('second_roommate', $grouping->get_roommate_one());
            $form->addText('third_roommate', $grouping->get_roommate_two());
            $form->addText('fourth_roommate', $grouping->get_roommate_three());

            $form->addHidden('module', 'hms');
            $form->addHidden('type', 'roommate');
            $form->addHidden('op', 'save_grouping');
            $form->addHidden('id', $grouping->get_id());
            $form->addSubmit('submit', _('Save Group'));

            $tpl = $form->getTemplate();

            $tpl['FIRST_ROOMMATE_NAME']     = "Kevin Michael Wilcox";
            $tpl['FIRST_ROOMMATE_YEAR']     = "Sophomore";
            $tpl['SECOND_ROOMMATE_NAME']    = "Joe Dirt";
            $tpl['SECOND_ROOMMATE_YEAR']    = "Junior";
        
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_roommates.tpl');
            return $final;
        }

        return HMS_Forms::get_username_for_edit_grouping();
    }

    function verify_break_grouping()
    {
        $db = new PHPWS_DB('hms_roommates');
        $db->addWhere('id', $_REQUEST['id']);

        PHPWS_Core::initModClass('hms', 'HMS_Roommate');

        $grouping   = new HMS_Roommate;
        $success    = $db->loadObject($grouping);

        PHPWS_Core::initCoreClass('Forms.php');
        $form = new PHPWS_Form;

        $form->addCheckbox('email_first_roommate');
        $form->addCheckbox('email_second_roommate');
        
        if($grouping->get_roommate_two() != NULL) {
            $form->addCheckbox('email_third_roommate');
        }
       
       if($grouping->get_roommate_three() != NULL) {
            $form->addCheckbox('email_fourth_roommate');
        }

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'roommate');
        $form->addHidden('op', 'break_grouping');
        $form->addHidden('id', $_REQUEST['id']);
        $form->addSubmit('submit', _('Break Group'));

        $tpl = $form->getTemplate();

        $tpl['FIRST_ROOMMATE']  = $grouping->get_roommate_zero();
        $tpl['SECOND_ROOMMATE'] = $grouping->get_roommate_one();
        $tpl['THIRD_ROOMMATE']  = $grouping->get_roommate_two();
        $tpl['FOURTH_ROOMMATE'] = $grouping->get_roommate_three();

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/verify_break_roommates.tpl');
        return $final;
    }

    function select_residence_hall_for_add_floor()
    {
        $content = "";

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addColumn('number_floors');
        $allhalls = $db->select();

        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can add floors to one!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($allhalls as $ahall) {
            $halls[$ahall['id']] = $ahall['hall_name'];
            $content .= $ahall['hall_name'] . " has " . $ahall['number_floors'] . " floors.<br />";
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('halls', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'add_floor');
        $form->addSubmit('submit', _('Submit'));
       
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Residence Hall";
        $tpl['CONTENT'] = $content;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }

    function select_residence_hall_for_delete_floor()
    {
        $content = "";

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addColumn('number_floors');
        $allhalls = $db->select();

        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can delete a floor!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($allhalls as $ahall) {
            $halls[$ahall['id']] = $ahall['hall_name'];
            $content .= $ahall['hall_name'] . " has " . $ahall['number_floors'] . " floors.<br />";
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('halls', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'confirm_delete_floor');
        $form->addSubmit('submit', _('Submit'));
       
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Residence Hall";
        $tpl['CONTENT'] = $content;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }

    function select_residence_hall_for_edit()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $allhalls = $db->select();
        
        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can edit a Hall!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($allhalls as $ahall) {
            $halls[$ahall['id']] = $ahall['hall_name'];
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('halls', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'edit_residence_hall');
        $form->addSubmit('submit', _('Edit Hall'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Hall to Edit";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }

    function select_residence_hall_for_edit_floor()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $allhalls = $db->select();
        
        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can edit a Floor!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($allhalls as $ahall) {
            $halls[$ahall['id']] = $ahall['hall_name'];
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('halls', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'floor');
        $form->addHidden('op', 'select_floor_for_edit');
        $form->addSubmit('submit', _('Submit'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Which Hall has the Floor to Edit";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }

    function select_floor_for_edit()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('id', $_REQUEST['halls']);
        $db->addColumn('number_floors');
        $db->addColumn('hall_name');
        $building = $db->select('row');
        unset($db);
        
        $hall = $building['hall_name'];
        $num_floors = $building['number_floors'];
        unset($building);
      
        for($i = 1; $i <= $num_floors; $i++) {
            $floor[$i] = "$i";
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('floor', $floor);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'floor');
        $form->addHidden('op', 'edit_floor');
        $form->addHidden('hall', $_REQUEST['halls']);
        $form->addSubmit('submit', 'Edit Floor');

        $tpl = $form->getTemplate();
        
        $tpl['TITLE']       = "Select a Floor";
        $tpl['HALL']        = "$hall";
        $tpl['NUM_FLOORS']  = "$num_floors";
       
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_floor_for_edit.tpl');
        return $final;
    }

    function select_residence_hall_for_edit_room()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $allhalls = $db->select();
        
        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can edit a Room!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($allhalls as $ahall) {
            $halls[$ahall['id']] = $ahall['hall_name'];
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('halls', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'select_floor_for_edit_room');
        $form->addSubmit('submit', _('Submit'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Which Hall has the Floor to Edit";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }

    function select_floor_for_edit_room()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('id', $_REQUEST['halls']);
        $db->addWhere('deleted', 0);
        $db->addColumn('number_floors');
        $db->addColumn('hall_name');
        $building = $db->select('row');
        unset($db);
        
        $hall = $building['hall_name'];
        $num_floors = $building['number_floors'];
        unset($building);
      
        for($i = 1; $i <= $num_floors; $i++) {
            $floor[$i] = "$i";
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('floor', $floor);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'select_room_for_edit');
        $form->addHidden('hall', $_REQUEST['halls']);
        $form->addSubmit('submit', 'Submit');

        $tpl = $form->getTemplate();
        
        $tpl['TITLE']       = "Which Floor has the Room To Edit?";
        $tpl['HALL']        = "$hall";
        $tpl['NUM_FLOORS']  = "$num_floors";
       
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_floor_for_edit.tpl');
        return $final;
    }

    function select_room_for_edit()
    {
        $db = &new PHPWS_DB('hms_room');
        $db->addColumn('room_number');
        $db->addWhere('building_id', $_REQUEST['hall']);
        $db->addWhere('floor_number', $_REQUEST['floor']);
        $db->addWhere('deleted', '0');
        $db->addOrder('room_number', 'ASC');
        $rooms = $db->select('column');
        
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('id', $_REQUEST['hall']);
        $db->addColumn('hall_name');
        $hall_name = $db->select('one');

        foreach($rooms as $room) {
            $room_numbers[$room['room_number']] = $room['room_number'];
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addDropBox('room', $room_numbers);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'edit_room');
        $form->addHidden('hall', $_REQUEST['hall']);
        $form->addSubmit('submit', _('Edit Room'));
        
        $tpl = $form->getTemplate();

        $tpl['TITLE']       = "Select Room to Edit";
        $tpl['HALL']        = $hall_name;
        $tpl['FLOOR']       = $_REQUEST['floor'];
        $tpl['NUM_ROOMS']   = count($room_numbers);

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_room_for_edit.tpl');
        return $final;
    }

    function select_residence_hall_for_delete()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $allhalls = $db->select();
        
        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can delete a Hall!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($allhalls as $ahall) {
            $halls[$ahall['id']] = $ahall['hall_name'];
        }

        $form->addDropBox('halls', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'delete_residence_hall');
        $form->addSubmit('submit', _('Delete Hall'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Hall to Delete";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }
    
    function confirm_delete_floor()
    {
        test($_REQUEST);
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('number_floors');
        $db->addColumn('hall_name');
        $db->addWhere('id', $_REQUEST['halls']);
        $last_floor = $db->select('row');
        unset($db);

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('floor', $last_floor['number_floors']);
        $form->addHidden('hall', $_REQUEST['halls']);

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'delete_floor');
        $form->addSubmit('delete', _('Yes, delete the floor.'));
        $form->addSubmit('cancel', _('No, cancel.'));

        $tpl            = $form->getTemplate();
        $tpl['TITLE']   = "Confirm Delete";
        $tpl['FLOOR']   = $last_floor['number_floors'];
        $tpl['HALL']    = $last_floor['hall_name'];

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/confirm_delete_floor.tpl');
        return $final;
    }
    
    function select_learning_community_for_delete()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $db = &new PHPWS_DB('hms_learning_communities');
        $all_lcs = $db->select();

        if($all_lcs == NULL) {
            $tpl['TITLE']   = "Error!";
            $tpl['CONTENT'] = "You must add a Learning Community before you can delete a Community!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }

        foreach($all_lcs as $lc) {
            $lcs[$lc['id']] = $lc['community_name'];
        }

        $form->addDropBox('lcs', $lcs);
        $form->addHidden('module', 'hms');
        $form->addHidden('op', 'delete_learning_community');
        $form->addSubmit('submit', _('Delete Community'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Community to Delete";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_learning_community.tpl');
        return $final;
    }

    function edit_residence_hall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Building.php');
        $hall = &new HMS_Building;
        $hall->id = $_REQUEST['halls'];
        
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->loadObject($hall);
        unset($db);
      
        $tpl = $this->fill_hall_data_display($hall, 'save_residence_hall') ;
        $tpl['TITLE'] = "Edit Residence Hall";
        $tpl['ERROR'] = $this->error;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_hall_data.tpl');
        return $final;
    }

    function edit_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $floor = &new HMS_Floor;
        
        $db = &new PHPWS_DB('hms_floor');
        $db->addWhere('floor_number', $_REQUEST['floor']);
        $db->addWhere('building', $_REQUEST['hall']);
        $db->loadObject($floor);
        unset($db);

        if($floor->get_id() == NULL) {
            // error....
            $err = "That floor does not exist!";
            $this->set_error_msg($err);
            $final = $this->select_floor_for_edit();
        } else {
            $tpl = $this->fill_floor_data_display($floor, 'save_floor');
            $tpl['TITLE'] = "Edit Floor";
            $tpl['ERROR'] = $this->error;
            $tpl['FLOOR'] = $floor->get_floor_number();
            $tpl['ROOMS'] = $floor->get_number_rooms();
            $db = &new PHPWS_DB('hms_residence_hall');
            $db->addColumn('hall_name');
            $db->addWhere('id', $_REQUEST['hall']);
            $hallname = $db->select('one');
            $tpl['BUILDING'] = $hallname;
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_floor_data.tpl');
        }
        return $final;
    }

    function edit_room()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $db = &new PHPWS_DB('hms_room');
        $db->addWhere('deleted', '0');
        $db->addWhere('room_number', $_REQUEST['room']);
        $db->addWhere('building_id', $_REQUEST['hall']);
        $room = $db->select('row');

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hall_name');
        $db->addWhere('id', $_REQUEST['hall']);
        $hall_name = $db->select('one');

        $id                 = $room['id'];
        $room_number        = $room['room_number'];
        $floor_number       = $room['floor_number'];
        $gender_type        = $room['gender_type'];
        $capacity_per_room  = $room['capacity_per_room'];
        $phone_number       = $room['phone_number'];
        $is_medical         = $room['is_medical'];
        $is_reserved        = $room['is_reserved'];
        $is_online          = $room['is_online'];

        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_('No'), _('Yes') ));
        $form->setMatch('is_online', $is_online);

        $form->addRadio('gender_type', array(0, 1, 2));
        $form->setLabel('gender_type', array(_('Female'), _('Male'), _('Coed')));
        $form->setMatch('gender_type', $gender_type);

        $form->addRadio('is_medical', array(0,1));
        $form->setLabel('is_medical', array(_('No'), _('Yes')));
        $form->setMatch('is_medical', $is_medical);

        $form->addRadio('is_reserved', array(0, 1));
        $form->setLabel('is_reserved', array(_('No'), _('Yes')));
        $form->setMatch('is_reserved', $is_reserved);
       
        $form->addText('phone_number', $phone_number);
        $form->setValue('phone_number', $phone_number);

        $form->setSize('phone_number', 8);

        $capacity   =  array('0'=>"0",
                             '1'=>"1",
                             '2'=>"2",
                             '3'=>"3",
                             '4'=>"4");
        $form->addDropBox('capacity_per_room', $capacity);
        $form->setMatch('capacity_per_room', $capacity_per_room);

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'save_room');
        $form->addHidden('id', $id);
        $form->addSubmit('submit', _('Submit'));

        $tpl = $form->getTemplate();
        
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        if(HMS_Room::is_in_suite($id)) {
            $suite_number = HMS_Room::get_suite_number($id);
            $db = &new PHPWS_DB('hms_suite');
            $db->addWhere('id', $suite_number);
            $rooms = $db->select('row');
            
            $tpl['ROOM_ID_ZERO']    = HMS_Room::get_room_number($rooms['room_id_zero']);
            $tpl['ROOM_ID_ONE']     = HMS_Room::get_room_number($rooms['room_id_one']);
            if($rooms['room_id_two'] != NULL) {
                $tpl['ROOM_ID_TWO'] = HMS_Room::get_room_number($rooms['room_id_two']);
            }
            if($rooms['room_id_three'] != NULL) {
                $tpl['ROOM_ID_THREE'] = HMS_Room::get_room_number($rooms['room_id_three']);
            }
            $tpl['EDIT_SUITE_LINK'] = PHPWS_Text::secureLink(_('Edit Suite'), 'hms', array('type'=>'suite', 'op'=>'edit_suite', 'suite'=>$suite_number));
        } else {
            $tpl['ROOM_ID_ZERO'] = "Not in a Suite";
            $tpl['EDIT_SUITE_LINK'] = PHPWS_Text::secureLink(_('Create Suite'), 'hms', array('type'=>'suite', 'op'=>'edit_suite', 'room'=>$id));
        }

        $tpl['TITLE']           = "Edit Room";
        $tpl['HALL_NAME']       = $hall_name;
        $tpl['FLOOR_NUMBER']    = $floor_number;
        $tpl['ROOM_NUMBER']     = $room_number;
        
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_room_data.tpl');
        return $final;
    }

    function add_floor()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $hall = $db->select('row');
        if($hall == NULL) {
            $tpl['TITLE']   = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can add a Floor!<br />";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            return $final;
        }
        unset($db);

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_("No"), _("Yes") ));
        $form->setMatch('is_online', $hall['is_online']);

        $form->addRadio('gender_type', array(0, 1, 2));
        $form->setLabel('gender_type', array(_("Female"), _("Male"), _("Coed")));
        $form->setMatch('gender_type', $hall['gender_type']);
      
        $form->addHidden('building', $hall['id']);
        $form->addHidden('floor_number', $hall['number_floors'] + 1);
        $form->addHidden('number_rooms', $hall['rooms_per_floor']);
        $form->addHidden('capacity_per_room', $hall['capacity_per_room']);
        
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('deleted', '0');
        $form->addHidden('op', 'save_new_floor');

        $form->addSubmit('submit', _('Add Floor'));

        $tpl                        = $form->getTemplate();
        $tpl['ERROR']               = $this->error;
        $tpl['TITLE']               = "Add a Floor";
        $tpl['HALL_NAME']           = $hall['hall_name'];
        $tpl['NUMBER_FLOORS']       = $hall['number_floors'];
        $tpl['FLOOR_NUMBER']        = $hall['number_floors'] + 1;
        $tpl['ROOMS_PER_FLOOR']     = $hall['rooms_per_floor'];
        $tpl['CAPACITY_PER_ROOM']   = $hall['capacity_per_room'];

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/add_floor.tpl');
        return $final;
    }

    function add_learning_community()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $lc = &new HMS_Learning_Community;
        $lc->set_variables();
        $tpl = $this->fill_learning_community_data_display($lc, 'save_learning_community');
        $tpl['ERROR'] = $this->error;
        $tpl['TITLE'] = "Add a Learning Community";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_learning_community_data.tpl');
        return $final;
    }
    
    function fill_learning_community_data_display($object = NULL, $op = NULL)
    {        
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        
        if(isset($object->community_name)) {
            $form->addText('community_name', $object->community_name);
        } else {
            $form->addText('community_name');
        }
    
        $form->addHidden('module', 'hms');
        $form->addHidden('op', $op);
        if(isset($object->id)) {
            $form->addHidden('id', $object->id);
        }
        $form->addSubmit('submit', _('Save Learning Community'));

        $tpl = $form->getTemplate();
        return $tpl;
    }

    function fill_floor_data_display($object = NULL, $op = NULL)
    {
        if(!Current_User::authorized('add_floor') ||
           !Current_User::authorized('edit_floor')) {
            $content = "BAD BAD PERSON!<br />";
            $content .= "This event has been logged.";
            return $content;
        }

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('id', $_REQUEST['hall']);
        $db->addColumn("hall_name");
        $name = $db->select('one');
        unset($db);

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_("No"), _("Yes")));
        if(isset($object->is_online)) {
            $form->setMatch('is_online', $object->get_is_online());
        } else {
            $form->setMatch('is_online', 2);
        }

        $form->addRadio('gender_type', array(0, 1, 2));
        $form->setLabel('gender_type', array(_("Female"), _("Male"), _("Coed")));
        if(isset($object->gender_type)) {
            $form->setMatch('gender_type', $object->get_gender_type());
        } else {
            $form->setMatch('gender_type', '3');
        }

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'floor');
        $form->addHidden('op', $op);
        $form->addHidden('authkey', Current_User::getAuthKey());
        $form->addHidden('is_new_floor', $object->get_is_new_floor());
        if(isset($object->id)) {
            $form->addHidden('id', $object->id);
            $form->addHidden('floor_number', $object->floor_number);
            $form->addHidden('building', $object->building);
            $form->addHidden('number_rooms', $object->number_rooms);
            $form->addHidden('capacity_per_room', $object->capacity_per_room);
            $form->addHidden('deleted', '0');
        }
        $form->addSubmit('submit', _('Save Floor'));

        $tpl = $form->getTemplate();
        return $tpl;
    }

    function add_residence_hall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Building.php');
        $hall = &new HMS_Building;
        $hall->set_is_new_building(TRUE);
        $tpl = $this->fill_hall_data_display($hall, 'save_residence_hall');
        $tpl['ERROR'] = $this->error;
        $tpl['TITLE'] = "Add a Residence Hall";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_hall_data.tpl');
        return $final;
    }
    
    function display_login_screen()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('asu_username');
        $form->addPassword('password');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hms');
        $form->addHidden('op', 'login');
        $form->addSubmit('submit', _('Login'));

        $tpl = $form->getTemplate();
        $tpl['ERROR'] = $this->get_error_msg();
        $final = PHPWS_Template::process($tpl, 'hms', 'misc/login.tpl');
        return $final;
    }

    function fill_hall_data_display($object = NULL, $op = NULL)
    {   
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        
        if(isset($object->hall_name)) {
            $form->addText('hall_name', $object->hall_name);
        } else {
            $form->addText('hall_name');
        }
   
        /*
        $db = &new PHPWS_DB('hms_hall_communities');
        $comms = $db->select();
        foreach($comms as $comm) {
            $communities[$comm['id']] = $comm['community_name'];
        }
        $form->addDropBox('community', $communities);
        if(isset($object->community)) {
            $form->setMatch('community', $object->community);
        }
        */

        $floors = array('1'=>"1",
                        '2'=>"2",
                        '3'=>"3",
                        '4'=>"4",
                        '5'=>"5",
                        '6'=>"6",
                        '7'=>"7",
                        '8'=>"8",
                        '9'=>"9",
                        '10'=>"10");
        $form->addDropBox('number_floors', $floors);
        if(isset($object->number_floors)) {
            $form->setMatch('number_floors', $object->number_floors);
        }
       
        for($i = 1; $i < 20; $i++) {
            $rooms[$i] = $i;
        }
       
        $form->addDropBox('rooms_per_floor', $rooms);
        if(isset($object->rooms_per_floor)) {
            $form->setMatch('rooms_per_floor', $object->rooms_per_floor);
        } else {
            $form->setMatch('rooms_per_floor', '15');
        }

        $form->addDropBox('capacity_per_room', array(0=>'0', 1=>'1', 2=>'2', 3=>'3', 4=>'4'));
        if(isset($object->capacity_per_room)) {
            $form->setMatch('capacity_per_room', $object->capacity_per_room);
        } else {
            $form->setMatch('capacity_per_room', 2);
        }

        $db = &new PHPWS_DB('hms_pricing_tiers');
        $prices = $db->select();
        foreach($prices as $price) {
            $pricing[$price['id']] = "$" . $price['tier_value'];
        }
        
        $form->addDropBox('pricing_tier', $pricing);
        if(isset($object->pricing_tier)) {
            $form->setMatch('pricing_tier', $object->pricing_tier);
        } else {
            $form->setMatch('pricing_tier', '3');
        }
       

        $form->addRadio('gender_type', array(0, 1, 2));
        $form->setLabel('gender_type', array(_("Female"), _("Male"), _("Coed")));
        if(isset($object->gender_type)) {
            $form->setMatch('gender_type', $object->gender_type);
        } else {
            $form->setMatch('gender_type', 2);
        }

        $form->addRadio('air_conditioned', array(0,1));
        $form->setLabel('air_conditioned', array(_("No"), _("Yes")));
        if(isset($object->air_conditioned)) {
            $form->setMatch('air_conditioned', $object->air_conditioned);
        } else {
            $form->setMatch('air_conditioned', 0);
        }

      
        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_("No"), _("Yes")));
        if(isset($object->is_online)) {
            $form->setMatch('is_online', $object->is_online);
        } else {
            $form->setMatch('is_online', 1);
        }

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', $op);
        if(isset($object->id)) {
            $form->addHidden('id', $object->id);
        }

        if($object->get_is_new_building() == TRUE) {
            $form->addHidden('is_new_building', TRUE);
        }

        $form->addSubmit('submit', _('Save Hall'));

        $tpl = $form->getTemplate();
        return $tpl;
    }

    function fill_student_data($error = NULL)
    {
        if(isset($_REQUEST['id'])) {
            $db = &new PHPWS_DB('hms_student');
            $db->addWhere('id', $_REQUEST['id']);
            $student = &new HMS_Student;
            $student_id = $db->loadObject($student);
            if($student_id == NULL || $student_id == FALSE) {
                return "Error: Student could not be loaded from the database.";
            }
        }

        $form = &new PHPWS_Form;

        $form->addText('first_name');
        $form->setSize('first_name', 20);
        $form->addText('middle_name');
        $form->setSize('middle_name', 20);
        $form->addText('last_name');
        $form->setSize('last_name', 20);

        $form->addText('asu_username');
        $form->setSize('asu_username', 6);

        $form->addDropBox('gender', array('0'=>'Female', '1'=>'Male'));
       
        if($student) {
            $form->setValue('first_name', $student->get_first_name());
            $form->setValue('middle_name', $student->get_middle_name());
            $form->setValue('last_name', $student->get_last_name());
            $form->setValue('asu_username', $student->get_asu_username());
            $form->setMatch('gender', $student->get_gender());
            $form->addHidden('id', $student->get_id());
        }
       
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'save_student');
        $form->addSubmit('submit', _('Save Student'));

        $tpl = $form->getTemplate();
        if($error) $tpl['ERROR'] = $error;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/student_data.tpl');
        return $final;
    }

    function enter_student_search_data($error = NULL)
    {
        $form = &new PHPWS_Form;
        $form->addText('last_name');
        $form->addText('first_name');
        $form->addText('asu_username');
        $form->setSize('last_name', 15);
        $form->setSize('first_name', 15);
        $form->setSize('asu_username', 7);
        $form->addSubmit('submit', _('Submit'));

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'get_matching_students');

        $tpl = $form->getTemplate();
        if(isset($error)) {
            $tpl['ERROR'] = $error;
        }
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/student_search.tpl');
        return $final;
    }

    function show_deadlines($message)
    {
        $months = array('1'=>'January',
                        '2'=>'February',
                        '3'=>'March',
                        '4'=>'April',
                        '5'=>'May',
                        '6'=>'June',
                        '7'=>'July',
                        '8'=>'August',
                        '9'=>'September',
                        '10'=>'October',
                        '11'=>'November',
                        '12'=>'December');
        
        for($d = 1; $d <= 31; $d++) {
            $days[$d] = $d;
        }

        $years = array(date('Y')=>date('Y'), date('Y') + 1=>date('Y') + 1);
        
        $form = &new PHPWS_Form;
        $form->addDropBox('student_login_begin_month', $months);
        $form->addDropBox('student_login_begin_day', $days);
        $form->addDropBox('student_login_begin_year', $years);
        
        $form->addDropBox('student_login_end_month', $months);
        $form->addDropBox('student_login_end_day', $days);
        $form->addDropBox('student_login_end_year', $years);
    
        $form->addDropBox('submit_questionnaire_begin_month', $months);
        $form->addDropBox('submit_questionnaire_begin_day', $days);
        $form->addDropBox('submit_questionnaire_begin_year', $years);
    
        $form->addDropBox('submit_questionnaire_end_month', $months);
        $form->addDropBox('submit_questionnaire_end_day', $days);
        $form->addDropBox('submit_questionnaire_end_year', $years);
    
        $form->addDropBox('search_questionnaires_begin_month', $months);
        $form->addDropBox('search_questionnaires_begin_day', $days);
        $form->addDropBox('search_questionnaires_begin_year', $years);
    
        $form->addDropBox('search_questionnaires_end_month', $months);
        $form->addDropBox('search_questionnaires_end_day', $days);
        $form->addDropBox('search_questionnaires_end_year', $years);
    
        $form->addDropBox('view_assignment_begin_month', $months);
        $form->addDropBox('view_assignment_begin_day', $days);
        $form->addDropBox('view_assignment_begin_year', $years);
    
        $form->addDropBox('view_assignment_end_month', $months);
        $form->addDropBox('view_assignment_end_day', $days);
        $form->addDropBox('view_assignment_end_year', $years);
    
        $db = &new PHPWS_DB('hms_deadlines');
        $result = $db->select('row');
         
        if($result != NULL) {
            $form->setMatch('student_login_begin_day', date('j',$result['student_login_begin_timestamp']));
            $form->setMatch('student_login_begin_month', date('n',$result['student_login_begin_timestamp']));
            $form->setMatch('student_login_begin_year', date('Y',$result['student_login_begin_timestamp']));
            $form->setMatch('student_login_end_day', date('j',$result['student_login_end_timestamp']));
            $form->setMatch('student_login_end_month', date('n',$result['student_login_end_timestamp']));
            $form->setMatch('student_login_end_year', date('Y',$result['student_login_end_timestamp']));
            $form->setMatch('submit_questionnaire_begin_day', date('j', $result['submit_questionnaire_begin_timestamp']));
            $form->setMatch('submit_questionnaire_begin_month', date('n', $result['submit_questionnaire_begin_timestamp']));
            $form->setMatch('submit_questionnaire_begin_year', date('Y', $result['submit_questionnaire_begin_timestamp']));
            $form->setMatch('submit_questionnaire_end_day', date('j', $result['submit_questionnaire_end_timestamp']));
            $form->setMatch('submit_questionnaire_end_month', date('n', $result['submit_questionnaire_end_timestamp']));
            $form->setMatch('submit_questionnaire_end_year', date('Y', $result['submit_questionnaire_end_timestamp']));
            $form->setMatch('search_questionnaires_begin_day', date('j', $result['search_questionnaires_begin_timestamp']));
            $form->setMatch('search_questionnaires_begin_month', date('n', $result['search_questionnaires_begin_timestamp']));
            $form->setMatch('search_questionnaires_begin_year', date('Y', $result['search_questionnaires_begin_timestamp']));
            $form->setMatch('search_questionnaires_end_day', date('j', $result['search_questionnaires_end_timestamp']));
            $form->setMatch('search_questionnaires_end_month', date('n', $result['search_questionnaires_end_timestamp']));
            $form->setMatch('search_questionnaires_end_year', date('Y', $result['search_questionnaires_end_timestamp']));
            $form->setMatch('view_assignment_begin_day', date('j', $result['view_assignment_begin_timestamp']));
            $form->setMatch('view_assignment_begin_month', date('n', $result['view_assignment_begin_timestamp']));
            $form->setMatch('view_assignment_begin_year', date('Y', $result['view_assignment_begin_timestamp']));
            $form->setMatch('view_assignment_end_day', date('j', $result['view_assignment_end_timestamp']));
            $form->setMatch('view_assignment_end_month', date('n', $result['view_assignment_end_timestamp']));
            $form->setMatch('view_assignment_end_year', date('Y', $result['view_assignment_end_timestamp']));
        }
        
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'maintenance');
        $form->addHidden('op', 'save_deadlines');
        $form->addSubmit('submit', _('Save Deadlines'));
        $tpl = $form->getTemplate();
        
        if(isset($message)) {
            $tpl['ERROR'] = $message;
        }
        
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/deadlines.tpl');
        return $final;
    }

    function begin_questionnaire($message = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addDropBox('student_status', array('1'=>_('New Freshman'),
                                                  '2'=>_('Transfer')));
        
        if(isset($_REQUEST['student_status'])) {
            $form->setMatch('student_status', $_REQUEST['student_status']);
        } else {
            $form->setMatch('student_status', 1);
        }
      
        $form->addDropBox('classification_for_term', array('1'=>_('Freshman'),
                                                           '2'=>_('Sophomore'),
                                                           '3'=>_('Junior'),
                                                           '4'=>_('Senior')));
        if(isset($_REQUEST['classification_for_term'])){
            $form->setMatch('classification_for_term',$_REQUEST['classification_for_term']);
        }else{
            $form->setMatch('classification_for_term', '1');
        }

        $form->addDropBox('gender_type', array('0'=>_('Female'),
                                               '1'=>_('Male')));
        if(isset($_REQUEST['gender_type'])){
            $form->setMatch('gender_type',$_REQUEST['gender_type']);
        }else{
            $form->setMatch('gender_type', '0');
        }

        $form->addDropBox('meal_option', array('1'=>_('Low'),
                                               '2'=>_('Standard'),
                                               '3'=>_('High'),
                                               '4'=>_('Super')));
        if(isset($_REQUEST['meal_option'])){
            $form->setMatch('meal_option',$_REQUEST['meal_option']);
        }else{
            $form->setMatch('meal_option', '1');
        }

        $form->addDropBox('lifestyle_option', array('1'=>_('Single Gender Building'),
                                                    '2'=>_('Co-Ed Building')));
        if(isset($_REQUEST['lifestyle_option'])){
            $form->setMatch('lifestyle_option',$_REQUEST['lifestyle_option']);
        }else{
            $form->setMatch('lifestyle_option', '1');
        }

        $form->addDropBox('preferred_bedtime', array('1'=>_('Early'),
                                                     '2'=>_('Late')));
        if(isset($_REQUEST['preferred_bedtime'])){
            $form->setMatch('preferred_bedtime',$_REQUEST['preferred_bedtime']);
        }else{
            $form->setMatch('preferred_bedtime', '1');
        }

        $form->addDropBox('room_condition', array('1'=>_('Neat'),
                                                  '2'=>_('Cluttered')));
        if(isset($_REQUEST['room_condition'])){
            $form->setMatch('room_condition',$_REQUEST['room_condition']);
        }else{
            $form->setMatch('room_condition', '1');
        }

        
        $form->addRadio('rlc_interest', array(0, 1));
        $form->setLabel('rlc_interest', array(_("No"), _("Yes")));
        if(isset($_REQUEST['rlc_interest'])){
            $form->setMatch('rlc_interest',$_REQUEST['rlc_interest']);
        }else{
            $form->setMatch('rlc_interest', '0');
        }

        $form->addDropBox('employed', array(0=>_('No'), 1=>_('Yes'), 2=>_('Prefer not to say')));
        $form->setMatch('employed', 0);
        if(isset($_REQUEST['employed'])){
            $form->setMatch('employed',$_REQUEST['employed']);
        }else{
            $form->setMatch('employed', '0');
        }

        $form->addDropBox('relationship', array(0=>_('No'), 1=>_('Yes'), 2=>_('Prefer not to say')));
        $form->setMatch('relationship', 0);
        if(isset($_REQUEST['relationship'])){
            $form->setMatch('relationship',$_REQUEST['relationship']);
        }else{
            $form->setMatch('relationship', '0');
        }

        $form->addSubmit('submit', _('Submit Application'));
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'review_questionnaire');

        $tpl = $form->getTemplate();
        $tpl['TITLE']   = 'Residence Hall Application';
        $tpl['MESSAGE'] = $message;

        $master['TITLE']   = 'Residence Hall Application';
        $master['QUESTIONNAIRE']  = PHPWS_Template::process($tpl, 'hms', 'student/student_questionnaire.tpl');
        return PHPWS_Template::process($master,'hms','student/student_questionnaire_combined.tpl');
    }

    function display_questionnaire_results()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Questionnaire.php');
        $questionnaire = new HMS_Questionnaire($_SESSION['asu_username']);

        if(!$questionnaire->getID() && !HMS_Form::check_valid_questionnaire_values()) {
            $message = "You have supplied incorrect values for your questionnaire.<br />";
            $message .= "Please fill out the questionnaire again.";
            return HMS_Form::begin_questionnaire($message);
        }

        PHPWS_Core::initCoreClass('Form.php');

        $master['TITLE']   = 'Residence Hall Application';
        if(isset($_REQUEST['student_status'])){
            $message  = "You have supplied the following values.<br />";
            $message .= "Click Submit to continue or Modify to change your selections.<br /><br />";

            $form = &new PHPWS_Form;

            $form->addHidden('classification_for_term', $_REQUEST['classification_for_term']);
            $form->addHidden('student_status',$_REQUEST['student_status']);
            $form->addHidden('gender_type',$_REQUEST['gender_type']);
            $form->addHidden('meal_option',$_REQUEST['meal_option']);
            $form->addHidden('lifestyle_option',$_REQUEST['lifestyle_option']);
            $form->addHidden('preferred_bedtime',$_REQUEST['preferred_bedtime']);
            $form->addHidden('room_condition',$_REQUEST['room_condition']);
            $form->addHidden('relationship',$_REQUEST['relationship']);
            $form->addHidden('employed',$_REQUEST['employed']);
            $form->addHidden('rlc_interest',$_REQUEST['rlc_interest']);
            $form->addHidden('module', 'hms');
            $form->addHidden('type', 'student');
            $form->addHidden('op', 'save_questionnaire');

            $form->addSubmit('submit', _('Submit Application'));

            $tpl = $form->getTemplate();

            $redo_form = & new PHPWS_Form('redo_form');
            $redo_form->addSubmit('submit','Modify Application');
            $redo_form->addHidden('type','student');
            $redo_form->addHidden('op','begin_questionnaire');
            $redo_form->addHidden('classification_for_term', $_REQUEST['classification_for_term']);
            $redo_form->addHidden('student_status',$_REQUEST['student_status']);
            $redo_form->addHidden('gender_type',$_REQUEST['gender_type']);
            $redo_form->addHidden('meal_option',$_REQUEST['meal_option']);
            $redo_form->addHidden('lifestyle_option',$_REQUEST['lifestyle_option']);
            $redo_form->addHidden('preferred_bedtime',$_REQUEST['preferred_bedtime']);
            $redo_form->addHidden('room_condition',$_REQUEST['room_condition']);
            $redo_form->addHidden('relationship',$_REQUEST['relationship']);
            $redo_form->addHidden('employed',$_REQUEST['employed']);
            $redo_form->addHidden('rlc_interest',$_REQUEST['rlc_interest']);
            
            $redo_tpl = $redo_form->getTemplate();

            $tpl['MESSAGE'] = $message;
            $tpl['NEWLINES']= "<br /><br />";
            
            if($_REQUEST['student_status'] == 1) $tpl['STUDENT_STATUS'] = "New Freshman";
            else if ($_REQUEST['student_status'] == 2) $tpl['STUDENT_STATUS'] = "Transfer";

            if($_REQUEST['classification_for_term'] == 1) $tpl['CLASSIFICATION_FOR_TERM'] = "Freshman";
            else if($_REQUEST['classification_for_term'] == 2) $tpl['CLASSIFICATION_FOR_TERM'] = "Sophomore";
            else if($_REQUEST['classification_for_term'] == 3) $tpl['CLASSIFICATION_FOR_TERM'] = "Junior";
            else if($_REQUEST['classification_for_term'] == 4) $tpl['CLASSIFICATION_FOR_TERM'] = "Senior";
            
            if($_REQUEST['gender_type'] == 0) $tpl['GENDER_TYPE'] = "Female";
            else if($_REQUEST['gender_type'] == 1) $tpl['GENDER_TYPE'] = "Male";
            
            if($_REQUEST['meal_option'] == 1) $tpl['MEAL_OPTION'] = "Low";
            else if($_REQUEST['meal_option'] == 2) $tpl['MEAL_OPTION'] = "Medium";
            else if($_REQUEST['meal_option'] == 3) $tpl['MEAL_OPTION'] = "High";
            else if($_REQUEST['meal_option'] == 4) $tpl['MEAL_OPTION'] = "Super";
           
            if($_REQUEST['lifestyle_option'] == 1) $tpl['LIFESTYLE_OPTION'] = "Single Gender";
            else if($_REQUEST['lifestyle_option'] == 2) $tpl['LIFESTYLE_OPTION'] = "Co-Ed";
            
            if($_REQUEST['preferred_bedtime'] == 1) $tpl['PREFERRED_BEDTIME'] = "Early";
            else if($_REQUEST['preferred_bedtime'] == 2) $tpl['PREFERRED_BEDTIME'] = "Late";

            if($_REQUEST['room_condition'] == 1) $tpl['ROOM_CONDITION'] = "Clean";
            else if($_REQUEST['room_condition'] == 2) $tpl['ROOM_CONDITION'] = "Dirty";
            
            if($_REQUEST['relationship'] == 0) $tpl['RELATIONSHIP'] = "No"; 
            else if($_REQUEST['relationship'] == 1) $tpl['RELATIONSHIP'] = "Yes"; 
            else if($_REQUEST['relationship'] == 2) $tpl['RELATIONSHIP'] = "Not Disclosed"; 
            
            if($_REQUEST['employed'] == 0) $tpl['EMPLOYED'] = "No";
            else if($_REQUEST['employed'] == 1) $tpl['EMPLOYED'] = "Yes";
            else if($_REQUEST['employed'] == 2) $tpl['EMPLOYED'] = "Not Disclosed";
             
            if($_REQUEST['rlc_interest'] == 0) $tpl['RLC_INTEREST_1'] = "No";
            else if($_REQUEST['rlc_interest'] == 1) $tpl['RLC_INTEREST_1'] = "Yes";
       
            $master['QUESTIONNAIRE']  = PHPWS_Template::process($tpl, 'hms', 'student/student_questionnaire.tpl');
            $master['REDO'] = PHPWS_Template::process($redo_tpl,'hms','student/student_questionnaire_redo.tpl');
        
            return PHPWS_Template::process($master,'hms','student/student_questionnaire_combined.tpl');
       
        } else {
            
            $tpl['TITLE']   = 'Residence Hall Application';
            if(isset($message)){
                $tpl['MESSAGE'] = $message;
            }
            $tpl['REDO']    = PHPWS_Text::secureLink("Return to Menu", 'hms', array('type'=>'hms', 'op'=>'main'));
            $tpl['NEWLINES']= "<br /><br />";
            
            if($questionnaire->getStudentStatus() == 1) $tpl['STUDENT_STATUS'] = "New Freshman";
            else if ($questionnaire->getStudentStatus() == 2) $tpl['STUDENT_STATUS'] = "Transfer";

            if($questionnaire->getTermClassification() == 1) $tpl['CLASSIFICATION_FOR_TERM'] = "Freshman";
            else if($questionnaire->getTermClassification() == 2) $tpl['CLASSIFICATION_FOR_TERM'] = "Sophomore";
            else if($questionnaire->getTermClassification() == 3) $tpl['CLASSIFICATION_FOR_TERM'] = "Junior";
            else if($questionnaire->getTermClassification() == 4) $tpl['CLASSIFICATION_FOR_TERM'] = "Senior";
            
            if($questionnaire->getGender() == 0) $tpl['GENDER_TYPE'] = "Female";
            else if($questionnaire->getGender() == 1) $tpl['GENDER_TYPE'] = "Male";
            
            if($questionnaire->getMealOption() == 1) $tpl['MEAL_OPTION'] = "Low";
            else if($questionnaire->getMealOption() == 2) $tpl['MEAL_OPTION'] = "Medium";
            else if($questionnaire->getMealOption() == 3) $tpl['MEAL_OPTION'] = "High";
            else if($questionnaire->getMealOption() == 4) $tpl['MEAL_OPTION'] = "Super";
           
            if($questionnaire->getLifestyle() == 1) $tpl['LIFESTYLE_OPTION'] = "Single Gender";
            else if($questionnaire->getLifestyle() == 2) $tpl['LIFESTYLE_OPTION'] = "Co-Ed";
            
            if($questionnaire->getPreferredBedtime() == 1) $tpl['PREFERRED_BEDTIME'] = "Early";
            else if($questionnaire->getPreferredBedtime() == 2) $tpl['PREFERRED_BEDTIME'] = "Late";

            if($questionnaire->getRoomCondition() == 1) $tpl['ROOM_CONDITION'] = "Clean";
            else if($questionnaire->getRoomCondition() == 2) $tpl['ROOM_CONDITION'] = "Dirty";
            
            if($questionnaire->getRelationship() == 0) $tpl['RELATIONSHIP'] = "No"; 
            else if($questionnaire->getRelationship() == 1) $tpl['RELATIONSHIP'] = "Yes"; 
            else if($questionnaire->getRelationship() == 2) $tpl['RELATIONSHIP'] = "Not Disclosed"; 
            
            if($questionnaire->getEmployed() == 0) $tpl['EMPLOYED'] = "No";
            else if($questionnaire->getEmployed() == 1) $tpl['EMPLOYED'] = "Yes";
            else if($questionnaire->getEmployed() == 2) $tpl['EMPLOYED'] = "Not Disclosed";
             
            if($questionnaire->getRlcInterest() == 0) $tpl['RLC_INTEREST_1'] = "No";
            else if($questionnaire->getRlcInterest() == 1) $tpl['RLC_INTEREST_1'] = "Yes";
       
            $master['QUESTIONNAIRE']  = PHPWS_Template::process($tpl, 'hms', 'student/student_questionnaire.tpl');
            return PHPWS_Template::process($master,'hms','student/student_questionnaire_combined.tpl');
        }
        
    }

    function check_valid_questionnaire_values()
    {
        return (is_numeric($_REQUEST['student_status']) &&
                is_numeric($_REQUEST['classification_for_term']) &&
                is_numeric($_REQUEST['gender_type']) &&
                is_numeric($_REQUEST['meal_option']) &&
                is_numeric($_REQUEST['lifestyle_option']) &&
                is_numeric($_REQUEST['preferred_bedtime']) &&
                is_numeric($_REQUEST['room_condition']) &&
                is_numeric($_REQUEST['relationship']) &&
                is_numeric($_REQUEST['employed']) &&
                is_numeric($_REQUEST['rlc_interest']));
    }

    function questionnaire_search_form()
    {
        $form = &new PHPWS_Form();
        $form->setAction('index.php?module=hms&type=student&op=questionnaire_search');

        $form->addText('asu_username');
        $form->setLabel('asu_username','ASU Username: ');

        $form->addSubmit('Search');

        $tags = array();
        $form->mergeTemplate($tags);
        $tags = $form->getTemplate();

        return PHPWS_Template::process($tags,'hms','student/questionnaire_search.tpl');
    }

    function edit_suite($error)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');

        if($_REQUEST['op'] == "save_new_suite") {
            $suite_id = HMS_Room::get_suite_number($_REQUEST['room_id_zero']);
        } else if(!is_null($_REQUEST['suite'])) {
            $suite_id = $_REQUEST['suite'];
        } else {
            $suite_id = NULL;
        }

        if($suite_id != NULL) {
            PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
            
            $suite = &new HMS_Suite($suite_id);
             
            $room_list = HMS_Room::get_rooms_on_floor($suite->get_room_id_zero());
            $floor = HMS_Room::get_floor_number($suite->get_room_id_zero());
            $hall_name = HMS_Room::get_hall_name_from_floor_id($suite->get_room_id_zero());
            $room_list['0'] = "Select Room ";

            $form = new PHPWS_Form();
            $form->addDropBox('room_id_one', $room_list);
            $form->addDropBox('room_id_two', $room_list);
            $form->addDropBox('room_id_three', $room_list);
            
            $form->setMatch('room_id_one', $suite->get_room_id_one());            
            if($suite->get_room_id_two()) {
                $form->setMatch('room_id_two', $suite->get_room_id_two());            
            } else {
                $form->setMatch('room_id_two', '0');
            }

            if($suite->get_room_id_three()) {
                $form->setMatch('room_id_three', $suite->get_room_id_three());            
            } else {
                $form->setMatch('room_id_three', '0');
            }
            
            $form->addHidden('room_id_zero', $suite->get_room_id_zero());
            $form->addHidden('type', 'suite');
            $form->addHidden('op', 'save_suite');
            $form->addHidden('floor', $floor);
            $form->addHidden('suite', $suite_id);
            $form->addSubmit('submit', _('Save Suite'));

            $tpl = $form->getTemplate();
            $tpl['ROOM_ID_ZERO']    = HMS_Room::get_room_number($suite->get_room_id_zero());

        } else {
            if(isset($_REQUEST['room_id_zero'])) $_REQUEST['room'] = $_REQUEST['room_id_zero'];

            $room_list = HMS_Room::get_rooms_on_floor($_REQUEST['room']);
            $floor = HMS_Room::get_floor_number($_REQUEST['room']);
            $hall_name = HMS_Room::get_hall_name_from_floor_id($_REQUEST['room']);
            $room_list[0] = "Select Room ";
        
            $form = new PHPWS_Form();

            $form->addDropBox('room_id_one', $room_list);
            $form->addDropBox('room_id_two', $room_list);
            $form->addDropBox('room_id_three', $room_list);
            
            $form->setMatch('room_id_one', '0');
            $form->setMatch('room_id_two', '0');
            $form->setMatch('room_id_three', '0');
            
            $form->addHidden('room_id_zero', $_REQUEST['room']);
            $form->addHidden('type', 'suite');
            $form->addHidden('op', 'save_new_suite');
            $form->addHidden('floor', $floor);
            $form->addSubmit('submit', _('Save Suite'));

            $tpl = $form->getTemplate();
            $tpl['ROOM_ID_ZERO']    = $room_list[$_REQUEST['room']];
        } 
      
        $tpl['FLOOR_NUMBER']    = $floor;
        $tpl['HALL_NAME']       = $hall_name;
        $tpl['ERROR']           = $error;

        $content = PHPWS_Template::process($tpl, 'hms', 'admin/display_suite_data.tpl');
        return $content;
    }

    function show_primary_admin_panel()
    {
        $residence_halls = array("Residence Halls");

        # TO_DO - Populate $room_types variable with an array from database;
        $room_types = array("Room Types");

        $meal_plans = array(0 => "Low", 1 => "Standard", 2 => "High", 3 => "Super");

        $months = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "June",
                        7 => "July", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");

        $days = array();
        for($i=1; $i <= 31; $i++){
            $days[$i] = $i;
        }

        $year = date('Y') - 1 ;
        $years = array($year++,$year++,$year++);

        # Create the lookup form
        $lookup_form = &new PHPWS_Form('student_lookup');

        $lookup_form->addText('term');
        $lookup_form->setSize('term',4);
        $lookup_form->setMaxSize('term','3');
        $lookup_form->setTab('term',1);
        $lookup_form->setLabel('term','Term: ');

        $lookup_form->addText('student_id');
        $lookup_form->setLabel('student_id','ID #: ');
        $lookup_form->setSize('student_id',10);
        $lookup_form->setMaxSize('student_id',9);
        $lookup_form->setTab('student_id',2);

        $lookup_form->addDropBox('residence_hall_lookup',$residence_halls);
        $lookup_form->setLabel('residence_hall_lookup','Hall: ');
        $lookup_form->setTab('residence_hall_lookup',3);

        $lookup_form->addText('room_num_lookup');
        $lookup_form->setLabel('room_num_lookup','RM #: ');
        $lookup_form->setSize('room_num_lookup',4);
        $lookup_form->setMaxSize('room_num_lookup',4);
        $lookup_form->setTab('room_num_lookup',4);

        $bed_nums = array(1 => "1", 2 => "2", 3 => "3", 4 => "4");
        $lookup_form->addDropBox('bed_num_lookup',$bed_nums);
        $lookup_form->setLabel('bed_num_lookup','Bed #: ');
        $lookup_form->setTab('bed_num_lookup',5);

        $lookup_form->addSubmit('lookup_submit','Submit');
        $lookup_form->setTab('lookup_submit',6);

        # Create the display form
        $display_form = & new PHPWS_Form('display_form');

        # Personal Information
        $display_form->addText('first_name');
        $display_form->setLabel('first_name','First Name: ');
        $display_form->setSize('first_name',15);
        $display_form->setMaxSize('first_name',25);
        $display_form->setTab('first_name',7);

        $display_form->addText('last_name');
        $display_form->setLabel('last_name','Last Name: ');
        $display_form->setSize('last_name',15);
        $display_form->setMaxSize('last_name',25);
        $display_form->setTab('last_name',8);

        $display_form->addText('middle_initial');
        $display_form->setLabel('middle_initial','Middle Initial: ');
        $display_form->setSize('middle_initial',1);
        $display_form->setMaxSize('middle_initial',1);
        $display_form->setTab('middle_initial',9);

        $display_form->addText('email');
        $display_form->setLabel('email','Email Address: ');
        $display_form->setSize('email',30);
        $display_form->setMaxSize('email',50);
        $display_form->setTab('email',10);

        $display_form->addText('cell_phone');
        $display_form->setLabel('cell_phone','Cell Phone #: ');
        $display_form->setSize('cell_phone',13);
        $display_form->setMaxSize('cell_phone',13);
        $display_form->setTab('cell_phone',11);

        $display_form->addDropBox('dob_month', $months);
        $display_form->setLabel('dob_month','Date of Birth: ');
        $display_form->setTab('dob_month',12);

        $display_form->addDropBox('dob_day',$days);
        $display_form->setTab('dob_day',13);

        $display_form->addDropBox('dob_year',$years);
        $display_form->setTab('dob_year',14);

        $display_form->addRadio('class_status', array(1,2,3,4,5));
        $display_form->setLabel('class_status',array('', 'FR','SO','JR','SR','GR'));
        $display_form->setTab('class_status',15);

        $display_form->addRadio('student_type',array('freshmen','returning','transfer'));
        $display_form->setLabel('student_type',array('Freshmen','Returning','Transfer'));
        $display_form->setTab('student_type',16);

        $display_form->addRadio('gender',array('male','female'));
        $display_form->setLabel('gender',array('Male','Female'));
        $display_form->setTab('gender',17);

        $display_form->addDropBox('application_received_month',$months);
        $display_form->setLabel('application_received_month','Application Received: ');
        $display_form->setTab('application_received_month',18);

        $display_form->addDropBox('application_received_day',$days);
        $display_form->setTab('application_received_day',19);

        $display_form->addDropBox('application_received_year',$years);
        $display_form->setTab('application_received_year',20);

        # Assignment Information
        $display_form->addDropBox('assign_residence_hall',$residence_halls);
        $display_form->setLabel('assign_residence_hall','Residence Hall: ');
        $display_form->setTab('assign_residence_hall',21);

        $display_form->addText('assign_floor');
        $display_form->setLabel('assign_floor','Floor: ');
        $display_form->setSize('assign_floor',2);
        $display_form->setMaxSize('assign_floor',2);
        $display_form->setTab('assign_floor',22);

        $display_form->addText('assign_room_num');
        $display_form->setLabel('assign_room_num','Room #: ');
        $display_form->setSize('assign_room_num',4);
        $display_form->setMaxSize('assign_room_num',3);
        $display_form->setTab('assign_room_num',23);

        $display_form->addText('assign_bed_num');
        $display_form->setLabel('assign_bed_num','Bed #: ');
        $display_form->setSize('assign_bed_num',2);
        $display_form->setMaxSize('assign_bed_num',2);
        $display_form->setTab('assign_bed_num',24);

        $display_form->addText('assign_phone_num');
        $display_form->setLabel('assign_phone_num','Room Phone #: ');
        $display_form->setSize('assign_phone_num', 13);
        $display_form->setMaxSize('assign_phone_num',13);
        $display_form->setTab('assign_phone_num',25);

        $display_form->addDropBox('assign_room_type',$room_types);
        $display_form->setLabel('assign_room_type','Room Type: ');
        $display_form->setTab('assign_room_type',26);

        $display_form->addDropBox('assign_meal_option',$meal_plans);
        $display_form->setLabel('assign_meal_option','Meal Option: ');
        $display_form->setTab('assign_meal_option',27);

        $display_form->addText('assigned_by');
        $display_form->setLabel('assigned_by','Assigned by: ');
        $display_form->setSize('assigned_by',20);
        $display_form->setMaxSize('assigned_by',30);
        $display_form->setTab('assigned_by',28);

        $display_form->addDropBox('assign_month',$months);
        $display_form->setLabel('assign_month','Assignment Date: ');
        $display_form->setTab('assign_month',29);

        $display_form->addDropBox('assign_day',$days);
        $display_form->setTab('assign_day',30);

        $display_form->addDropBox('assign_year',$years);
        $display_form->setTab('assign_year',31);

        # Preference Information
        $display_form->addRadio('pref_neatness',array(1,0));
        $display_form->setLabel('pref_neatness',array("Neat", "Cluttered"));
        $display_form->setTab('pref_neatness',33);
        $template['PREF_NEATNESS_LBL'] = "Room Condition: ";

        $display_form->addRadio('pref_bedtime',array(1,0));
        $display_form->setLabel('pref_bedtime',array("Early", "Late"));
        $display_form->setTab('pref_bedtime',34);
        $template['PREF_BEDTIME_LBL'] = "Bed time: ";

        $display_form->addRadio('pref_lifestyle',array(1,0));
        $display_form->setLabel('pref_lifestyle',array("Co-ed", "Single"));
        $display_form->setTab('pref_lifestyle',35);
        $template['PREF_LIFESTYLE_LBL'] = "Lifestyle:";

        # Roommate Information
        $display_form->addText('roomate_name');
        $display_form->setLabel('roomate_name',"Name: ");
        $display_form->setSize('roomate_name',20);
        $display_form->setMaxSize('roomate_name',50);
        $display_form->setTab('roomate_name',43);

        $display_form->addText('roomate_id');
        $display_form->setLabel('roomate_id','ID #: ');
        $display_form->setSize('roomate_id',10);
        $display_form->setMaxSize('roomate_id',9);
        $display_form->setTab('roomate_id',44);

        $display_form->addText('roomate_home_phone');
        $display_form->setLabel('roomate_home_phone','Home Phone #: ');
        $display_form->setSize('roomate_home_phone',13);
        $display_form->setMaxSize('roomate_home_phone',13);
        $display_form->setTab('roomate_home_phone',45);

        $display_form->addText('paired_by');
        $display_form->SetLabel('paired_by','Paired by: ');
        $display_form->setSize('paired_by',25);
        $display_form->setMaxSize('paired_by',50);
        $display_form->setTab('paired_by',46);


        # Deposit Information
        $display_form->addDropBox('deposit_month',$months);
        $display_form->setLabel('deposit_month', 'Date: ');
        $display_form->setTab('deposit_month',36);

        $display_form->addDropBox('deposit_day',$days);
        $display_form->setTab('deposit_day',37);

        $display_form->addDropBox('deposit_year',$years);
        $display_form->setTab('deposit_year',38);

        $display_form->addText('deposit_amount');
        $display_form->setLabel('deposit_amount','Amount: ');
        $display_form->setSize('deposit_amount',8);
        $display_form->setMaxSize('deposit_amount',9);
        $display_form->setTab('deposit_amount',39);

        $display_form->addCheck('waiver_check','1');
        $display_form->setLabel('waiver_check','Waiver: ');
        $display_form->setTab('waiver_check',40);

        $display_form->addRadio('forfeiture',array('refund','credit','forfeit'));
        $display_form->setLabel('forfeiture',array('Refund','Credit', 'Forfeit'));
        $display_form->setTab('forfeiture',41);

        # Withdrawal Information
        $display_form->addRadio('withdrawal',array('registrar','admissions','student','academic_ineligible','noshow','automatic_release','contract_release'));
        $display_form->setLabel('withdrawal',array('Registrars','Admissions','Student','Academic Ineligible','No-show','Automatic Release','Contract Release'));
        $display_form->setTab('withdrawal',42);

        # Merge the forms into the template
        $lookup_form->mergeTemplate($template);
        $template = $lookup_form->getTemplate();

        $display_form->mergeTemplate($template);
        $template = $display_form->getTemplate();

        return PHPWS_Template::process($template,'hms','admin/main_admin_panel.tpl');
    }

    # Displays the RLC application form
    function show_rlc_application_form_page1($message = NULL)
    {
        $template = array();
        
        $rlc_form = & new PHPWS_Form();
        $rlc_form->addHidden('type', 'student');
        $rlc_form->addHidden('op','rlc_application_page1_submit');

        # 1. About You Section
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        $template['MESSAGE'] = $message;
        
        $template['APPLENET_USERNAME']       = $_SESSION['asu_username'];
        $template['APPLENET_USERNAME_LABEL'] = 'Applenet User Name: ';

        $template['FIRST_NAME']        = 'First'; # HMS_SOAP::get_first_name($_SESSION['asu_username']);
        $template['FIRST_NAME_LABEL']  = 'First Name: ';
        
        $template['MIDDLE_NAME']       = 'Middle'; # HMS_SOAP::get_middle_name($_SESSION['asu_username']);
        $template['MIDDLE_NAME_LABEL'] = 'Middle Name: ';
        
        $template['LAST_NAME']         = 'Last'; # HMS_SOAP::get_last_name($_SESSION['asu_username']);
        $template['LAST_NAME_LABEL']   = 'Last Name: ';

        # 2. Rank Your RLC Choices

        $db = &new PHPWS_DB('hms_learning_communities');
        $rlc_choices = $db->select('assoc');

        $rlc_form->addDropBox('rlc_first_choice', $rlc_choices);
        $rlc_form->setLabel('rlc_first_choice','First Choice: ');
        if(isset($_REQUEST['rlc_first_choice'])){
            $rlc_form->setMatch('rlc_first_choice', $_REQUEST['rlc_first_choice']);
        }
        
        $rlc_form->addDropBox('rlc_second_choice', $rlc_choices);
        $rlc_form->setLabel('rlc_second_choice','Second Choice: ');
        if(isset($_REQUEST['rlc_second_choice'])){
            $rlc_form->setMatch('rlc_second_choice', $_REQUEST['rlc_second_choice']);
        }
        
        $rlc_form->addDropBox('rlc_third_choice', $rlc_choices);
        $rlc_form->setLabel('rlc_third_choice','Third Choice: ');
        if(isset($_REQUEST['rlc_third_choice'])){
            $rlc_form->setMatch('rlc_third_choice', $_REQUEST['rlc_third_choice']);
        }

        # 3. About Your Choices

        if(isset($_REQUEST['why_specific_communities'])){
            $rlc_form->addTextarea('why_specific_communities',$_REQUEST['why_specific_communities']);
        }else{
            $rlc_form->addTextarea('why_specific_communities');
        }
        $rlc_form->setLabel('why_specific_communities',
                            'Why are you interested in the specific communities you have chosen?');
        $rlc_form->setMaxSize('why_specific_communities',500);

        if(isset($_REQUEST['strengths_weaknesses'])){
            $rlc_form->addTextarea('strengths_weaknesses', $_REQUEST['strengths_weaknesses']);
        }else{
            $rlc_form->addTextarea('strengths_weaknesses');
        }
        $rlc_form->setLabel('strengths_weaknesses',
                            'What are your strengths and in what areas would you like to improve?');
        $rlc_form->setMaxSize('strengths_weaknesses',500);

        $rlc_form->addSubmit('submit', 'Continue'); 
    
        $rlc_form->mergeTemplate($template);
        $template = $rlc_form->getTemplate();

        return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
    }

    /*
     * Validates the first page of the rlc application form
     * Requires:    first, middle and last name are set
     *              rlc choices are set and are numeric
     *              text fields are set
     */               
    function validate_rlc_application_page1(){
        return  (isset($_REQUEST['applenet_username']) &&
                isset($_REQUEST['first_name']) &&
                isset($_REQUEST['middle_name']) &&
                isset($_REQUEST['last_name']) &&
                isset($_REQUEST['rlc_first_choice']) &&
                isset($_REQUEST['rlc_second_choice']) &&
                isset($_REQUEST['rlc_third_choice']) &&
                is_numeric($_REQUEST['rlc_first_choice']) &&
                is_numeric($_REQUEST['rlc_second_choice']) &&
                is_numeric($_REQUEST['rlc_third_choice']) &&
                isset($_REQUEST['why_specific_communities']) &&
                isset($_REQUEST['strengths_weaknesses'])
                );
    }
};
?>
