
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
        $db->addOrder('hall_name ASC');
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

    function show_assignments_by_floor($msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        // check the hall/floor combo is good
        $db = &new PHPWS_DB('hms_floor');
        $db->addValue('id');
        $db->addWhere('building', $_REQUEST['halls']);
        $db->addWhere('floor_number', $_REQUEST['floors']);
        $db->addWhere('deleted', '1', '!=');
        $id = $db->select('one');
        if(!is_numeric($id)) {
            $error = "That is not a valid Hall/Floor combination.<br />";
            return HMS_Assignment::get_hall_floor($error);
        }
        
        // get the hall name
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hall_name');
        $db->addWhere('id', $_REQUEST['halls']);
        $hall = $db->select('one');

        // get the room number
        $rooms_sql  = "SELECT hms_room.room_number, hms_room.displayed_room_number, hms_room.id ";
        $rooms_sql .= "FROM hms_room, hms_floor ";
        $rooms_sql .= "WHERE hms_room.floor_id = hms_floor.id ";
        $rooms_sql .= "AND hms_floor.floor_number = " . $_REQUEST['floors'] . " ";
        $rooms_sql .= "AND hms_floor.building = " . $_REQUEST['halls'] . " ";
        
        if($_REQUEST['room_range'] == '0125') {
            $rooms_sql .= "AND int4(hms_room.room_number) <=  " . $_REQUEST['floors'] . "25 ";
        } else if ($_REQUEST['room_range'] == '2650') {
            $rooms_sql .= "AND int4(hms_room.room_number) > " . $_REQUEST['floors'] . "25 ";
            $rooms_sql .= "AND int4(hms_room.room_number) <= " . $_REQUEST['floors'] . "50 ";
        } else if ($_REQUEST['room_range'] == '5175') {
            $rooms_sql .= "AND int4(hms_room.room_number) > " . $_REQUEST['floors'] . "51 ";
            $rooms_sql .= "AND int4(hms_room.room_number) <= " . $_REQUEST['floors'] . "75 ";
        }

        $rooms_sql .= "AND hms_room.deleted = 0 ";
        $rooms_sql .= "ORDER BY hms_room.room_number ASC;";

        $db = &new PHPWS_DB();
        $db->setSQLQuery($rooms_sql);
        $rooms_raw = $db->select();

        if(PEAR::isError($rooms_raw)) {
            return HMS_Form::get_hall_floor("There was an error selecting that room range. Please try again or contact ESS.<br />");
        }

        if(sizeof($rooms_raw) < 1) {
            return HMS_Form::get_hall_floor("That room range does not exist for that Hall/Floor combination.<br />");
        }

        $body = '';
        
        foreach($rooms_raw as $aroom) {
            // iterate through the rooms, building the form as necessary

            $db = &new PHPWS_DB('hms_beds');
            $db->addColumn('id');
            $db->addColumn('bed_letter');
            $db->addColumn('hms_bedrooms.bedroom_letter');
            $db->addWhere('bedroom_id', 'hms_bedrooms.id');
            $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
            $db->addWhere('hms_room.id', $aroom['id']);
            $db->addWhere('hms_beds.deleted', '0');
            $beds = $db->select();

            if($beds != NULL && $beds != FALSE) {
                $body .= "<tr><th>Room Number: &nbsp;&nbsp;" . $aroom['room_number'] . "&nbsp;&nbsp;</th><th>Displayed: " . $aroom['displayed_room_number'] . "</th></tr>";

                foreach($beds as $abed) {
                    $tags['BED_NAME'] = $abed['bed_letter'];
                    $tags['BEDROOM_ID'] = $abed['bedroom_letter'];
                    $bed_id = "bed__" . $abed['id']; 
                    $edit_bed_id = "bed_" . $abed['id'];
                    $meal_option_id = "meal_option_" . $abed['id'];

                    $username = HMS_Assignment::get_asu_username($abed['id']);
                    $meal_option = HMS_Assignment::get_meal_option('bed_id', $abed['id']);

                    if(isset($_REQUEST[$bed_id]) && $_REQUEST[$bed_id] != NULL) {
                        $tags['BED_ID'] = "<input type=\"text\" name=\"bed_ " . $abed['id']  . "\" id=\"bed_id\" value=\"" . $_REQUEST[$bed_id] . "\" />";
                        $tags['MEAL_PLAN'] = "<select name=\"meal_option_" . $abed['id'] ."\">";
                       
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 0) $tags['MEAL_PLAN'] .= "<option selected value=\"0\">Low</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"0\">Low</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 1) $tags['MEAL_PLAN'] .= "<option selected value=\"1\">Standard</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"1\">Standard</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 2) $tags['MEAL_PLAN'] .= "<option selected value=\"2\">High</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"2\">High</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 3) $tags['MEAL_PLAN'] .= "<option selected value=\"2\">Super</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"3\">Super</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 4) $tags['MEAL_PLAN'] .= "<option selected value=\"4\">None</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"4\">None</option>";
                
                        $tags['MEAL_PLAN'] .= "</select>";
                    } else if(isset($_REQUEST[$edit_bed_id])) {
                        $tags['BED_ID'] = "<input type=\"text\" name=\"bed_ " . $abed['id']  . "\" id=\"bed_id\" value=\"" . $_REQUEST[$edit_bed_id] . "\" />";
                        $tags['MEAL_PLAN'] = "<select name=\"meal_option_" . $abed['id'] ."\">";
                       
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 0) $tags['MEAL_PLAN'] .= "<option selected value=\"0\">Low</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"0\">Low</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 1) $tags['MEAL_PLAN'] .= "<option selected value=\"1\">Standard</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"1\">Standard</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 2) $tags['MEAL_PLAN'] .= "<option selected value=\"2\">High</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"2\">High</option>";
                        
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 3) $tags['MEAL_PLAN'] .= "<option selected value=\"2\">Super</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"3\">Super</option>";
                
                        if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 4) $tags['MEAL_PLAN'] .= "<option selected value=\"4\">None</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"4\">None</option>";
                
                        $tags['MEAL_PLAN'] .= "</select>";
                    } else {
                        $tags['BED_ID'] = "<input type=\"text\" name=\"bed_ " . $abed['id']  . "\" id=\"bed_id\" value=\"" . $username . "\" />";
                        $tags['MEAL_PLAN'] = "<select name=\"meal_option_" . $abed['id'] ."\">";
                       
                        if($meal_option == 0) $tags['MEAL_PLAN'] .= "<option selected value=\"0\">Low</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"0\">Low</option>";
                        
                        if($meal_option == 1) $tags['MEAL_PLAN'] .= "<option selected value=\"1\">Standard</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"1\">Standard</option>";
                        
                        if($meal_option == 2) $tags['MEAL_PLAN'] .= "<option selected value=\"2\">High</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"2\">High</option>";
                        
                        if($meal_option == 3) $tags['MEAL_PLAN'] .= "<option selected value=\"3\">Super</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"3\">Super</option>";
                        
                        if($meal_option == 4) $tags['MEAL_PLAN'] .= "<option selected value=\"4\">None</option>";
                        else $tags['MEAL_PLAN'] .= "<option value=\"4\">None</option>";
                
                        $tags['MEAL_PLAN'] .= "</select>";
                    }
                    
                    
                    $body .= PHPWS_Template::processTemplate($tags, 'hms', 'admin/bed_and_id.tpl');
                }
            }
        }
        
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('halls', $_REQUEST['halls']);
        $form->addHidden('floors', $_REQUEST['floors']);
        $form->addHidden('op', 'verify_assign_floor');
        $form->addSubmit('submit', _('Submit Floor'));

        $tags = $form->getTemplate();
        $tags['TITLE']      = "Assign Students";
        $tags['HALL']       = '<a href="./index.php?module=hms&type=hall&op=view_residence_hall&halls=' . $_REQUEST['halls'] . '">' . $hall . '</a>';
        $tags['FLOOR']      = $_REQUEST['floors'];
        $tags['BODY']       = $body;
        $tags['MESSAGE']    = $msg;
        $final = PHPWS_Template::processTemplate($tags, 'hms', 'admin/assign_floor.tpl');
        return $final; 
    }

    function verify_assign_floor()
    {
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Building.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $body = '';
        reset($_REQUEST);
        while(list($key, $uid) = each($_REQUEST))
        {
            if(substr($key,0,4) == "bed_") {
                if(substr($key, 4, 1) == "_") {
                    $bid = substr($key, 5);
                } else {
                    $bid = substr($key, 4);
                }
                if($uid == NULL) continue;
       
                // check for valid username
                $valid_username = HMS_SOAP::is_valid_student($uid);
                if(!$valid_username) {
                    $error = "$uid is not a valid student. Please remove them from the list.<br /><br />";
                    return HMS_Form::show_assignments_by_floor($error);
                }
            
                // check to see if the room's already assigned
                $assigned = HMS_Assignment::is_bed_assigned($bid);
                if($assigned) {
                    $curr_occupant = HMS_Assignment::get_asu_username($bid);
                    
                    // check to see if the person being assigned is the current occupant
                    if(strcasecmp($curr_occupant, $uid) != 0) {
                        $error = "$uid can not be assigned because $curr_occupant is already in that room. Please remove them.<br /><br />";
                        return HMS_Form::show_assignments_by_floor($error);
                    }

                }

                // check to see if the current user is currently assigned
                $assigned = HMS_Assignment::is_user_assigned($uid);
                if($assigned) {
                    $curr_bed_id = HMS_Assignment::get_bed_id('asu_username', $uid);
                    if($curr_bed_id != $bid) {
                        $error = "$uid can not be assigned because they are assigned elsewhere. Please remove their room assignment first.<br /><br />";
                        return HMS_Form::show_assignments_by_floor($error);
                    }
                }

                // check room/person compatibility
                $db = &new PHPWS_DB('hms_room');
                $db->addColumn('gender_type');
                $db->addWhere('hms_beds.id', $bid);
                $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
                $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
                $db->addWhere('hms_beds.deleted', '0');
                $db->addWhere('hms_bedrooms.deleted', '0');
                $db->addWhere('hms_room.deleted', '0');
                $room_gender = $db->select('one');

                $user_gender = HMS_SOAP::get_gender($uid, TRUE);
            
                if($room_gender != $user_gender) {
                    $error = "$uid can not be assigned because their gender is not compatible with the room gender. Please change the room gender.<br /><br />";
                    return HMS_Form::show_assignments_by_floor($error);
                }

                // see if the person has a roommate
                // if a roommate exists, make sure they are going into the same room
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                if(HMS_Roommate::has_roommates($uid)) {
                    /* from here we have to do some additional parsing.
                       we need to check the db to get all the bed ids associated with a room,
                         make sure they're included in $_REQUEST and that it's only their roommates
                         that are placed in those beds.
                       on the same note we need to ascertain that each of their roommates is included and that
                         no roommate is not in a bed in the room*/
                    $error = "$uid has roommates. Please write code to handle that.<br /><br />";
                    return HMS_Form::show_assignments_by_floor($error);
                }
            
                // if we get here we know we're pretty safe to go ahead and let them assign the student 
                $db = &new PHPWS_DB('hms_room');
                $db->addColumn('room_number');
                $db->addColumn('hms_bedrooms.bedroom_letter');
                $db->addColumn('hms_beds.bed_letter');
                $db->addColumn('hms_residence_hall.hall_name');
                $db->addWhere('hms_beds.id', $bid);
                $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
                $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
                $db->addWhere('hms_room.floor_id', 'hms_floor.id');
                $db->addWhere('hms_floor.floor_number', $_REQUEST['floors']);
                $db->addWhere('hms_floor.building', 'hms_residence_hall.id');
                $db->addWhere('hms_residence_hall.id', $_REQUEST['halls']);
                $response = $db->select('row');

                $tags['BED_NAME'] = $response['bed_letter'];
                $tags['ROOM_LABEL'] = "Room ";
                $tags['ROOM_NUM']   = $response['room_number'] . " &nbsp;&nbsp;&nbsp;&nbsp;";
                $tags['BEDROOM_ID'] = $response['bedroom_letter'] . "&nbsp;&nbsp;&nbsp";
                $bed_id = "bed__" . $bid; 
                $tags['BED_ID'] = "<input type=\"text\" readonly name=\"bed_$bid\" id=\"phpws_form_bed_id\" value=\"$uid\" />";
                
                $tags['MEAL_PLAN'] = "<select name=\"meal_option_" . $bid ."\">";
              
                $meal_option_id = "meal_option_" . $bid;

                if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 0) $tags['MEAL_PLAN'] .= "<option selected value=\"0\">Low</option>";
                else $tags['MEAL_PLAN'] .= "<option value=\"0\">Low</option>";
                
                if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 1) $tags['MEAL_PLAN'] .= "<option selected value=\"1\">Standard</option>";
                else $tags['MEAL_PLAN'] .= "<option value=\"1\">Standard</option>";
                
                if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 2) $tags['MEAL_PLAN'] .= "<option selected value=\"2\">High</option>";
                else $tags['MEAL_PLAN'] .= "<option value=\"2\">High</option>";
                
                if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 3) $tags['MEAL_PLAN'] .= "<option selected value=\"3\">Super</option>";
                else $tags['MEAL_PLAN'] .= "<option value=\"3\">Super</option>";
                
                if(isset($_REQUEST[$meal_option_id]) && $_REQUEST[$meal_option_id] == 4) $tags['MEAL_PLAN'] .= "<option selected value=\"4\">None</option>";
                else $tags['MEAL_PLAN'] .= "<option value=\"4\">None</option>";
                
                $tags['MEAL_PLAN'] .= "</select>";
                    
                $body .= PHPWS_Template::processTemplate($tags, 'hms', 'admin/bed_and_id.tpl');
            }
        }
        
        $hall_name = HMS_Building::get_hall_name('id', $_REQUEST['halls']);
        
        $form = &new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('halls', $_REQUEST['halls']);
        $form->addHidden('floors', $_REQUEST['floors']);
        $form->addHidden('op', 'assign_floor');
        $form->addSubmit('submit', _('Submit Assignments'));
        $form->addSubmit('cancel', _('Cancel Assignments'));
        $form->addSubmit('edit', _('Edit Assignments'));

        $tags = $form->getTemplate();
        $tags['TITLE']      = "Assignment Verification";
        $tags['HALL']       = $hall_name;
        $tags['FLOOR']      = $_REQUEST['floors'];
        $tags['BODY']       = $body;
        $final = PHPWS_Template::processTemplate($tags, 'hms', 'admin/assign_floor.tpl');
        return $final; 
    }

    function select_residence_hall_for_overview()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addOrder('hall_name ASC');
        $allhalls = $db->select();
        
        if($allhalls == NULL) {
            $tpl['TITLE'] = "Error!";
            $tpl['CONTENT'] = "You must add a Residence Hall before you can view it!<br />";
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
        $form->addHidden('op', 'view_residence_hall');
        $form->addSubmit('submit', _('View Hall'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Hall to View";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        return $final;
    }

    function display_room_for_add()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hall_name');
        $db->addWhere('id', $_REQUEST['hall']);
        $hall_name = $db->select('one');

        $floor_number = $_REQUEST['floor'];
   
        $db = &new PHPWS_DB('hms_room');
        $sql = "select max(room_number) from hms_room where building_id = " . $_REQUEST['hall'] . " AND floor_number = " . $_REQUEST['floor'] . " AND deleted = 0 ";
        $db->setSQLQuery($sql);
        $room_number = $db->select('one');
        $room_number++;
       
        $db = &new PHPWS_DB('hms_floor');
        $db->addColumn('id');
        $db->addWhere('building', $_REQUEST['hall']);
        $db->addWhere('floor_number', $floor_number);
        $db->addWhere('deleted', '0');
        $floor_id = $db->select('one');

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_("No"), _("Yes") ));
        $form->setMatch('is_online', '1');

        $form->addRadio('gender_type', array(0, 1));
        $form->setLabel('gender_type', array(_("Female"), _("Male")));
        $form->setMatch('gender_type', '0');
      
        $form->addRadio('freshman_reserved', array(0, 1));
        $form->setLabel('freshman_reserved', array(_("No"), _("Yes")));
        $form->setMatch('freshman_reserved', '0');

        $bedrooms = array('1'=>'1',
                          '2'=>'2',
                          '3'=>'3',
                          '4'=>'4');
        $form->addDropBox('bedrooms_per_room', $bedrooms);
        $form->setMatch('bedrooms_per_room', '1');

        $form->addDropBox('beds_per_bedroom', $bedrooms);
        $form->setMatch('beds_per_bedroom', '2');

        $db = &new PHPWS_DB('hms_pricing_tiers');
        $prices = $db->select();

        foreach($prices as $price) {
            $pricing[$price['id']] = "$" . $price['tier_value'];
        }
        
        $form->addDropBox('pricing_tier', $pricing);
        $form->setMatch('pricing_tier', '1');
 
        $form->addRadio('is_medical', array(0, 1));
        $form->setLabel('is_medical', array(_("No"), _("Yes")));
        $form->setMatch('is_medical', '0');

        $form->addRadio('is_reserved', array(0, 1));
        $form->setLabel('is_reserved', array(_("No"), _("Yes")));
        $form->setMatch('is_reserved', '0');

        $form->addRadio('ra_room', array(0, 1));
        $form->setLabel('ra_room', array(_("No"), _("Yes")));
        $form->setMatch('ra_room', '0');

        $form->addRadio('private_room', array(0, 1));
        $form->setLabel('private_room', array(_("No"), _("Yes")));
        $form->setMatch('private_room', '0');

        $form->addRadio('is_lobby', array(0, 1));
        $form->setLabel('is_lobby', array(_("No"), _("Yes")));
        $form->setMatch('is_lobby', '0');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'add_room');
        $form->addHidden('building_id', $_REQUEST['hall']);
        $form->addHidden('floor_id', $floor_id);
        $form->addHidden('floor_number', $floor_number);
        $form->addHidden('room_number', $room_number);

        $form->addSubmit('submit', _('Add Room'));

        $tpl                        = $form->getTemplate();
        $tpl['TITLE']               = "Add a Room";
        $tpl['HALL_NAME']           = $hall_name;
        $tpl['FLOOR_NUMBER']        = $floor_number;
        $tpl['ROOM_NUMBER']         = $room_number;

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
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
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'confirm_delete_learning_community');
        $form->addSubmit('submit', _('Delete Community'));
        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Select a Community to Delete";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/select_learning_community.tpl');
        return $final;
    }

    function add_floor()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('deleted', '0');
        $db->addWhere('id', $_REQUEST['halls']);
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
      
        $form->addRadio('freshman_reserved', array(0, 1));
        $form->setLabel('freshman_reserved', array(_("No"), _("Yes")));
        $form->setMatch('freshman_reserved', '0');
      
        $db = &new PHPWS_DB('hms_pricing_tiers');
        $prices = $db->select();

        foreach($prices as $price) {
            $pricing[$price['id']] = "$" . $price['tier_value'];
        }
        
        $form->addDropBox('pricing_tier', $pricing);
        $form->setMatch('pricing_tier', '1');
        $form->addCheckBox('use_pricing_tier');

        $form->addHidden('building', $hall['id']);
        $db = new PHPWS_DB('hms_floor');
        $db->addColumn('floor_number');
        $db->addWhere('building', $hall['id']);
        $db->addWhere('deleted', '1', '!=');
        $results = $db->select();
        $floor_number = 1;
        foreach($results as $result) {
            if($result['floor_number'] > $floor_number) $floor_number = $result['floor_number'];
        }
        $form->addHidden('floor_number', $floor_number + 1);
        $form->addHidden('number_rooms', $hall['rooms_per_floor']);
        $form->addHidden('bedrooms_per_room', $hall['bedrooms_per_room']);
        $form->addHidden('beds_per_bedroom', $hall['beds_per_bedroom']);
        
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
        $tpl['BEDROOMS_PER_ROOM']   = $hall['bedrooms_per_room'];
        $tpl['BEDS_PER_BEDROOM']    = $hall['beds_per_bedroom'];

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/add_floor.tpl');
        return $final;
    }

    function add_learning_community($msg)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $tpl = HMS_Form::fill_learning_community_data_display();
        $tpl['TITLE'] = "Add a Learning Community";
        $tpl['MESSAGE'] = $msg;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_learning_community_data.tpl');
        return $final;
    }
    
    function fill_learning_community_data_display($object = NULL)
    {        
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        
        if(isset($object->community_name)) {
            $form->addText('community_name', $object->community_name);
        } else {
            $form->addText('community_name');
        }

        if(isset($object->abbreviation)) {
            $form->addText('abbreviation', $object->abbreviation);
        } else {
            $form->addText('abbreviation');
        }
        $form->setSize('abbreviation', 5);

        if(isset($object->capacity)) {
            $form->addText('capacity', $object->capacity);
        } else {
            $form->addText('capacity');
        }
        $form->setSize('capacity', 5);

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $names = $db->select();

        $community = '';
        if($names != NULL) {
            $community .= "The following Learning Communities exist:<br /><br />";
            foreach($names as $name) {
                $community .= $name['community_name'] . "<br />";
            }
        }

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'save_learning_community');
        if(isset($object->id)) {
            $form->addHidden('id', $object->id);
        }
        $form->addSubmit('submit', _('Save Learning Community'));

        $tpl = $form->getTemplate();
        $tpl['COMMUNITY'] = $community;
        return $tpl;
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
        $welcome  = "Welcome to the Housing Management System.<br /><br />";
        $welcome .= "There are multiple parts to this process. These are:<br />";
        $welcome .= " - Logging in<br />";
        $welcome .= " - Agreeing to the Housing License Contract<br />";
        $welcome .= " - Completing a Housing Application<br />";
        $welcome .= " - Completing the Residential Learning Community Application if you wish to participate in a RLC<br />";
        $welcome .= " - Completing the *OPTIONAL* student profile<br /><br />";
        $welcome .= "Please note that once you complete the Housing Application you do not have to fill out anything else provided at this website.<br /><br />";
      
        $welcome .= "<br /><br />";
        $welcome .= "<b>If you are experiencing problems please read <a href=\"./index.php?module=webpage&id=1\" target=\"_blank\">this page</a>.";
        $welcome .= "<br /><br />";

        $values = array('ADDITIONAL'=>'The Housing Management System will <strong>not</strong> work without cookies.  Please read about <a href="http://www.google.com/cookies.html" target="_blank">how to enable cookies</a>.');
        $tpl['COOKIE_WARNING'] = Layout::getJavascript('cookietest', $values);
        $tpl['WELCOME'] = $welcome;
        $tpl['ERROR']   = $this->get_error_msg();
        $final = PHPWS_Template::process($tpl, 'hms', 'misc/login.tpl');
        return $final;
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
};
?>
