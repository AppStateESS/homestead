<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Bed extends HMS_Item {
    
    var $room_id            = 0;
    var $bed_letter         = null;
    var $banner_id          = null;
    var $phone_number       = null;
    var $bedroom_label      = null;
    var $ra_bed             = null;
    var $_curr_assignment   = null;

    /**
     * Previous assignments (ie deleted) will be here after loading
     * the current assignment
     * @var array
     */
    var $_prev_assignment = array();

    /**
     * Holds the parent room object of this bed.
     */
    var $_room;

    function HMS_Bed($id = 0)
    {
        $this->construct($id, 'hms_bed');
        //test($this);
    }

    function copy($to_term, $room_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_beds, making a copy of this bed<br>";
        
        $new_bed = clone($this);
        $new_bed->reset();
        $new_bed->term    = $to_term;
        $new_bed->room_id = $room_id;
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

        //if (!$result || PHPWS_Error::logIfError($result)) {
        if(PEAR::isError($result)){
            PHPWS_Error::logIfError($result);
            return false;
        } else if($result == null){
            return true;
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

    function loadRoom()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $result = new HMS_Room($this->room_id);
        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        $this->_room = & $result;
        return true;
    }

    function get_parent()
    {
        if(!$this->loadRoom()){
            return false;
        }

        return $this->_room;
    }

    function get_number_of_assignees()
    {
        $this->loadAssignment();
        return (bool)$this->_curr_assignment ? 1 : 0;
    }

    function get_assignee()
    {
        if(!$this->loadAssignment()){
            return false;
        }

        return new HMS_Student($this->_curr_assignment->asu_username);
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

    /**
     * Returns a link. If the bed is assigned, the link is to the
     * student info screen. Otherwise, the link the link is to the
     * assign student screen.
     */
    function get_assigned_to_link()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
        if($this->loadAssignment() === false){
            $tpl['ERROR_MSG'] = 'There was an error loading the assignmnet. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }
        
        if(isset($this->_curr_assignment)){
            return PHPWS_Text::secureLink(HMS_SOAP::get_full_name($this->_curr_assignment->asu_username),'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$this->_curr_assignment->asu_username))
                                    . ' '
                                    . PHPWS_Text::secureLink('(re-assign)', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$this->_curr_assignment->asu_username));
        }else{
            # TODO: make this a link to assign a student to this
            # particular bed
            return PHPWS_Text::secureLink('&lt;unassigned&gt;', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'bed_id'=>$this->id));
        }

    }

    function getPagerByRoomTags()
    {
        $tags['BEDROOM']        = $this->bedroom_label;
        $tags['BED_LETTER']     = PHPWS_Text::secureLink($this->bed_letter, 'hms', array('type'=>'bed', 'op'=>'show_edit_bed', 'bed'=>$this->id));
        $tags['ASSIGNED_TO']    = $this->get_assigned_to_link();
        $tags['RA']             = $this->ra_bed ? 'Yes' : 'No';

        return $tags;
    }

    /******************
     * Static Methods *
     ******************/

    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'select_bed_to_edit':
                return HMS_Bed::show_select_bed('Edit Bed', 'bed', 'show_edit_bed');
                break;
            case 'show_edit_bed':
                return HMS_Bed::show_edit_bed();
                break;
            case 'edit_bed':
                return HMS_Bed::edit_bed();
                break;
            default:
                echo "undefined bed op: {$_REQUEST['op']}";
                break;
        }
    }
     
    function get_all_empty_beds($init = FALSE)
    {
        $sql = "
            SELECT
                hms_bed.*,
                hms_room.gender_type,
                hms_residence_hall.banner_building_code
            FROM 
                hms_bed
                JOIN hms_room           ON hms_bed.room_id         = hms_room.id
                JOIN hms_floor          ON hms_room.floor_id           = hms_floor.id
                JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                LEFT OUTER JOIN (
                    SELECT * FROM hms_assignment WHERE deleted = 0
                ) AS a_prime ON hms_bed.id = a_prime.bed_id
            WHERE
                hms_bed.deleted              = 0 AND
                hms_room.deleted             = 0 AND
                hms_room.is_online           = 1 AND
                hms_room.is_reserved         = 0 AND
                hms_room.is_medical          = 0 AND
                hms_room.ra_room             = 0 AND
                hms_room.private_room        = 0 AND
                hms_floor.deleted            = 0 AND
                hms_floor.is_online          = 1 AND
                hms_residence_hall.deleted   = 0 AND
                hms_residence_hall.is_online = 1 AND
                a_prime.asu_username IS NULL
            ";

        $results = PHPWS_DB::getAll($sql);

        $beds = array();
        $beds['0'] = array();
        $beds['1'] = array();
        foreach($results as $result) {
            $bed = new HMS_Bed();
            $bed->id = $result['id'];
            $bed->banner_id = $result['banner_id'];

            // Is there a better way to do this?  Hell yes.  Unfortunately the only
            // one I can think of is WAY THE HELL less efficient.  We MUST work on
            // our object-relational mapping sometime.  TODO!!!
            $hack['bed'] = $bed;
            $hack['hall'] = $result['banner_building_code'];

            $beds[$result['gender_type']][] = $hack;
        }

        return $beds;
    }
    

    function edit_bed()
    {
        # Create the bed object given the bed_id
        $bed = new HMS_Bed($_REQUEST['bed_id']);
        if(!$bed){
            return show_select_bed('Edit Bed', 'bed', 'show_edit_bed', null, 'Error: The selected bed does not exist!');
        }

        $bed->bedroom_label = $_REQUEST['bedroom_label'];
        $bed->phone_number  = $_REQUEST['phone_number'];
        $bed->banner_id     = $_REQUEST['banner_id'];
        
        if(isset($_REQUEST['ra_bed'])){
            $bed->ra_bed = 1;
        }else{
            $bed->ra_bed = 0;
        }

        $result = $bed->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Bed::show_edit_bed($bed->id, NULL, 'Error: There was a problem while saving the bed. No changes were made');
        }

        return HMS_Bed::show_edit_bed($bed->id, 'Bed updated successfully.');
    }

    
    /*********************
     * Static UI Methods *
     *********************/
     
    function show_select_bed($title, $type, $op, $success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_bed');

        $tpl = array();

        # Setup the title and color of the title bar
        $tpl['TITLE'] = $title;
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Get the halls for the selected term
        $halls = HMS_Residence_Hall::get_halls_array(HMS_Term::get_selected_term());

        # Show an error if there are no halls for the current term
        if($halls == NULL){
            $tpl['ERROR_MSG'] = 'Error: No halls exist for the selected term. Please create a hall first.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/select_bed.tpl');
        }

        $halls[0] = 'Select...';

        $tpl['MESSAGE'] = 'Please select a bed: ';

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

        $form->addDropBox('room', array(0 => ''));
        $form->setLabel('room', 'Room: ');
        $form->setExtra('room', 'disabled onChange="handle_room_change()"');

        $form->addDropBox('bed', array(0 => ''));
        $form->setLabel('bed', 'Bed: ');
        $form->setExtra('bed', 'disabled onChange="handle_bed_change()"');

        $form->addSubmit('submit', 'Select');
        $form->setExtra('submit', 'disabled');

        # Use the type and op that was passed in
        $form->addHidden('module', 'hms');
        $form->addHidden('type', $type);
        $form->addHidden('op', $op);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/select_bed.tpl');
    }

    function show_edit_bed($bed_id = NULL, $success = null, $error = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        # Determine the bed id. If the passed in variable is NULL,
        # use the request.
        if(!isset($bed_id)){
            $bed_id = $_REQUEST['bed'];
        }

        # Setup the title and color of the title bar
        $tpl['TITLE'] = 'Edit Bed';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Create the room object given the room_id
        $bed = new HMS_Bed($bed_id);
        if(!$bed){
            return HMS_Bed::show_select_bed('Edit B', 'bed', 'show_edit_bed', NULL, 'Error: The selected bed does not exist!'); 
        }

        # Create the room object
        $room = $bed->get_parent();
        if(!$room){
            $tpl['ERROR_MSG'] = 'There was an error getting the room object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        # Create the floor object
        $floor = $room->get_parent();
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        $hall = $floor->get_parent();
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$floor->id));
        $tpl['ROOM_NUMBER']         = PHPWS_Text::secureLink($room->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$room->id));
        $tpl['BED_LETTER']          = $bed->bed_letter;

        $tpl['ASSIGNED_TO'] = $bed->get_assigned_to_link();
        
        $form = new PHPWS_Form();

        $form->addText('bedroom_label', $bed->bedroom_label);
        
        $form->addText('phone_number', $bed->phone_number);
        $form->setMaxSize('phone_number', 4);
        $form->setSize('phone_number', 5);
        
        $form->addText('banner_id', $bed->banner_id);

        $form->addCheckBox('ra_bed', 1);

        if($bed->ra_bed == 1){
            $form->setExtra('ra_bed', 'checked');
        }
        //$form->setMatch('ra_bed', $bed->ra_bed);

        $form->addSubmit('submit', 'Submit');
        
        $form->addHidden('module', 'hms');        
        $form->addHidden('type', 'bed');
        $form->addHidden('op', 'edit_bed');
        $form->addHidden('bed_id', $bed->id);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
    }

    function bed_pager_by_room($room_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_bed', 'HMS_Bed');
        $pager->db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        
        $pager->addWhere('hms_room.id', $room_id);
        $pager->addWhere('hms_bed.deleted', 0);
        $pager->addWhere('hms_room.deleted', 0);

        $page_tags['TABLE_TITLE']       = 'Beds in this room:';
        $page_tags['BEDROOM_LABEL']     = 'Bedroom';
        $page_tags['BED_LETTER_LABEL']  = 'Bed';
        $page_tags['ASSIGNED_TO_LABEL'] = 'Assigned to';
        $page_tags['RA_LABEL']          = 'RA bed';

        $pager->setModule('hms');
        $pager->setTemplate('admin/bed_pager_by_room.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No beds found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerByRoomTags');
        $pager->addPageTags($page_tags);
       
        return $pager->get();
    }
}

?>
