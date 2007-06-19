<?php

/**
 * Building objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Building
{

    var $id;
    var $hall_name;
    var $number_floors;
    var $rooms_per_floor;
    var $bedrooms_per_room;
    var $beds_per_bedroom;
    var $gender_type;
    var $air_conditioned;
    var $is_online;
    var $is_new_building;
    var $numbering_scheme;
    var $added_by;
    var $added_on;
    var $deleted_by;
    var $deleted_on;
    var $deleted;
    var $updated_by;
    var $updated_on;
    var $error;

    /**
     * Constructor for the HMS_Building class
     * Sets all values to null or false
     */
    function HMS_Building()
    {
        $this->id = NULL;
        $this->hall_name = NULL;
        $this->number_floors = NULL;
        $this->rooms_per_floor = NULL;
        $this->bedrooms_per_room = NULL;
        $this->beds_per_bedroom = NULL;
        $this->gender_type = NULL;
        $this->air_conditioned = NULL;
        $this->is_online = NULL;
        $this->numbering_scheme = NULL;
        $this->deleted = 0;
        unset($this->added_by);
        unset($this->added_on);
        unset($this->deleted_by);
        unset($this->deleted_on);
        unset($this->updated_by);
        unset($this->updated_on);
        $this->is_new_building = FALSE;
        $this->error = "";
    }

    /**
     * Sets the error message for the object
     */
    function set_error($msg)
    {
        $this->error .= $msg;
    }

    /**
     * Returns the error message for the object
     */
    function get_error()
    {
        return $this->error;
    }

    /**
     * Sets the object id
     */
    function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the object id
     */
    function get_id()
    {
        return $this->id;
    }

    /**
     * Sets true if the building is new, false by default
     */
    function set_is_new_building($is_new = FALSE)
    {
        $this->is_new_building = $is_new;
    }

    /**
     * Sets the hall name
     */
    function set_hall_name($name)
    {
        $this->hall_name = $name;
    }

    /**
     * Returns the hall name
     */
    function get_hall_name($col = NULL, $val = NULL)
    {
        if($col == NULL) {
            return $this->hall_name;
        } else {
            $db = &new PHPWS_DB('hms_residence_hall');
            $db->addColumn('hall_name');
            $db->addWhere($col, $val);
            $db->addWhere('deleted', '0');
            $hall_name = $db->select('one');
            return $hall_name;
        }
    }

    /**
     * Sets the number of floors for the hall
     */
    function set_number_floors($number_floors)
    {
        $this->number_floors = $number_floors;
    }

    /**
     * Sets the number of people to be assigned per room (template)
     */
    function set_bedrooms_per_room($capacity)
    {
        $this->bedrooms_per_room = $capacity;
    }
   
    function set_beds_per_bedroom($beds)
    {
        $this->beds_per_bedroom = $beds;
    }

    /**
     * Returns the number of floors for the hall
     */
    function get_number_floors($id = NULL)
    {
        if($id == NULL){
            return $this->number_floors;
        } else {
            $hall_db = &new PHPWS_DB('hms_residence_hall');
            $hall_db->addWhere('id', $id);
            $hall_db->addColumn('number_floors');
            $number_floors = $hall_db->select('one');
            return $number_floors;
        }
    }

    /**
     * Sets how many rooms on each floor (template)
     */
    function set_rooms_per_floor($rooms)
    {
        $this->rooms_per_floor = $rooms;
    }

    /**
     * Returns the number of rooms on a template floor
     */
    function get_rooms_per_floor()
    {
        return $this->rooms_per_floor;
    }

    /**
     * Sets the gender type for the building
     * Should be either 0 (female), 1 (male) or 2 (co-ed)
     */
    function set_gender_type($gender)
    {
        $this->gender_type = $gender;
    }

    /**
     * Sets whether this hall is air conditioned
     */
    function set_air_conditioned($air)
    {
        $this->air_conditioned = $air;
    }
   
    /**
     * Sets whether this hall is available for room assignments
     */
    function set_is_online($online)
    {
        $this->is_online = $online;
    }

    function get_numbering_scheme()
    {
        return $this->numbering_scheme;
    }

    function set_numbering_scheme($scheme)
    {
        $this->numbering_scheme = $scheme;
    }

    /**
     * Returns whether this building is new
     * False indicates this is an edit
     */
    function get_is_new_building()
    {
        return $this->is_new_building;
    }

    /**
     * Sets the user ID of the user that added the hall
     */
    function set_added_by()
    {
        $this->added_by = Current_User::getId();
    }

    /**
     * Gets the user id of the hall creator
     */
    function get_added_by()
    {
        return $this->added_by;
    }

    /**
     * Sets the timestamp when the hall was added
     */
    function set_added_on()
    {
        $this->added_on = time();
    }

    /**
     * Returns the timestamp from when the hall was added
     */
    function get_added_on()
    {
        return $this->added_on;
    }

    /**
     * Sets the user ID of the user that deleted the hall
     */
    function set_deleted_by()
    {
        $this->deleted_by = Current_User::getId();
    }

    /**
     * Returns the user ID of the user that deleted the hall
     */
    function get_deleted_by()
    {
        return $this->deleted_by;
    }

    /**
     * Sets the timestamp when the hall was deleted
     */
    function set_deleted_on()
    {
        $this->deleted_on = time();
    }

    /**
     * Returns the timestamp from when the hall was deleted
     */
    function get_deleted_on()
    {
        return $this->deleted_on;
    }

    /**
     * Sets the user ID of the last user to edit the hall
     */
    function set_updated_by()
    {
        $this->updated_by = Current_User::getId();
    }

    /**
     * Returns the user ID of the last user to edit the hall
     */
    function get_updated_by()
    {
        return $this->updated_by;
    }

    /**
     * Sets the timestamp of the last hall edit
     */
    function set_updated_on()
    {
        $this->updated_on = time();
    }

    /**
     * Returns the timestamp of the last hall edit
     */
    function get_updated_on()
    {
        return $this->updated_on;
    }

    function set_deleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Sets the values for each class variable based on the value passed from the form
     * Type and other sanity checks need to be implemented
     */
    function set_variables()
    {
        if(isset($_REQUEST['id'])) $this->set_id($_REQUEST['id']);
        $this->set_hall_name($_REQUEST['hall_name']);
        $this->set_number_floors($_REQUEST['number_floors']);
        $this->set_rooms_per_floor($_REQUEST['rooms_per_floor']);
        $this->set_gender_type($_REQUEST['gender_type']);
        $this->set_air_conditioned($_REQUEST['air_conditioned']);
        $this->set_is_online($_REQUEST['is_online']);
        $this->set_bedrooms_per_room($_REQUEST['bedrooms_per_room']);
        $this->set_beds_per_bedroom($_REQUEST['beds_per_bedroom']);
        $this->set_numbering_scheme($_REQUEST['numbering_scheme']);
        $this->set_deleted(0);
        if($_REQUEST['is_new_building']) $this->set_is_new_building($_REQUEST['is_new_building']);
    }

    /**
     * Given a hall name, checks the database to see if a hall exists with that name
     */
    function check_building_exists($name)
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('hall_name', $name);
        $db->addWhere('deleted', 0);
        $db->addColumn('id');
        $dupe = $db->select('one');
        return $dupe;
    }

    /**
     * Returns an error message stating that the hall already exists
     * If passed an object, the edit form is populated with that object's values
     * If not passed anything, the edit form is displayed with the defaults selected
     */
    function building_exists_msg($object = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $object->error .= "There was a problem saving this hall!<br />";
        $object->error .= "This hall already exists!<br />";
        if($object == NULL) {
            $tpl = HMS_Form::fill_hall_data_display($this, 'save_residence_hall');
        } else {
            $tpl = HMS_Form::fill_hall_data_display($object, 'save_residence_hall');
        }
        $tpl['TITLE'] = "Error Saving Residence Hall";
        $tpl['ERROR'] = $object->error;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_hall_data.tpl');
        return $final;
    }

    /**
     * Calls the set_updated_by and set_updated_on methods
     * Saves the developer an extra function call when a building is added,
     *   edited or deleted
     */
    function set_updated_by_on($object)
    {
        $object->set_updated_by();
        $object->set_updated_on();
    }

    /**
     * Returns that there was an error saving the hall
     * Needs to email hms-devs@tux.appstate.edu with a basic error report
     */
    function error_saving_hall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $this->error .= "There was a problem saving this hall!<br />";
        $tpl = HMS_Form::fill_hall_data_display($this, 'save_residence_hall');
        $tpl['TITLE'] = "Error Saving Residence Hall";
        $tpl['ERROR'] = $this->error;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_hall_data.tpl');
        return $final;
    }

    /**
     * Saves a residence hall
     * Class method that can not be called statically
     * Will add/remove floors and rooms as necessary to reflect modifications in the hall template
     */
    function save_residence_hall()
    {
        if(Current_User::authorized('hms', 'add_halls') || Current_User::authorized('hms', 'admin')) {
            if($this->get_is_new_building() == TRUE) {
                $dupe = HMS_Building::check_building_exists($this->get_hall_name());
                if($dupe == TRUE) {
                    $final = HMS_Building::building_exists_msg($this);
                    return $final;
                }
            }
            
            $db = & new PHPWS_DB('hms_residence_hall');
            if($this->id) {
                HMS_Building::set_updated_by_on($this);
                $db->addWhere('id', $this->id);
                $current = &new HMS_Building;
                $current->set_id($this->id);
                $current_id = $db->loadObject($current);
                $saved_id = $db->saveObject($this);
            
                if(PEAR::isError($saved_id)) {
                    $final = $this->error_saving_hall();
                    return $final;
                }
               
                if($current_id) {
                    // number of floors changing
                    if($current->number_floors > $this->number_floors) {
                        echo "current greater than the new<br />";
                        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
                        $floors_deleted = HMS_Floor::delete_floors($this->id, $this->number_floors);
                        $rooms_deleted = HMS_Room::delete_rooms_by_floor($this->id, $this->number_floors);
                        // check for errors ^,^^
                    } else if ($current->number_floors < $this->number_floors) {
                        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                        for($i = $current->number_floors + 1; $i <= $this->number_floors; $i++) {
                            $floor = HMS_Building::make_floor_object(NULL, $this, $i);
                            $result = HMS_Floor::save_floor_object($floor, TRUE);
                            if(PEAR::isError($result)) {
                                return $result;
                            }
                        }

                        /* 
                        $db = &new PHPWS_DB('hms_room');
                        $db->addValue('pricing_tier', $_REQUEST['pricing_tier']);
                        $db->addWhere('building_id', $current_id);
                        $db->addWhere('deleted', '0');
                        $result = $db->update();
                        */
                    }

                    // number rooms per floor changes
                    if($current->rooms_per_floor != $this->rooms_per_floor) {
                        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
                        $changed_rooms_floor = HMS_Floor::set_number_rooms($this->rooms_per_floor, $this->id);
                        $changed_rooms_room = HMS_Room::change_rooms_per_floor($this->id, $this->number_floors, $current->rooms_per_floor, $this->rooms_per_floor, $this->is_online, $this->gender_type, $this->bedrooms_per_room, $this->beds_per_bedroom);
                    }

                    // is_online changes
                    if($current->is_online != $this->is_online) {
                        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                        $online_updated = HMS_Floor::set_is_online($this->is_online, NULL, $this->id);
                        if(PEAR::isError($online_updated)) {
                            $final = $this->error_saving_hall();
                            return $final;
                        }
                    }
                    // gender changes
                    if($current->gender_type != $this->gender_type) {
                        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                        $floors_updated = HMS_Floor::set_gender_type($this->gender_type, NULL, $this->id);
                        if(PEAR::isError($floors_update)) {
                            $final = $this->error_saving_hall();
                            return $final;
                        }
                    }

                }
                if(isset($_REQUEST['use_pricing_tier'])) {
                    $db = &new PHPWS_DB('hms_room');
                    $db->addValue('pricing_tier', $_REQUEST['pricing_tier']);
                    $db->addWhere('building_id', $current_id);
                    $db->addWhere('deleted', '0');
                    $good_tiers = $db->update();
                }
            } else {
                HMS_Building::set_added_by_on($this);
                $saved_id = $db->saveObject($this);
                if(PEAR::isError($saved_id)) {
                    $final = $this->error_saving_hall();
                    return $final;
                }
            }
            
            unset($db);

            if($this->get_is_new_building() == TRUE) {
                PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                $ctr = 0;
                for($i = 1; $i <= $this->number_floors; $i++, $ctr++) {
                    // case where it's 1, 2, etc
                    if($this->get_numbering_scheme() == 2 && $ctr == 0) $ctr = 1;

                    $floor = HMS_Building::make_floor_object($saved_id, $this, $ctr);
                    $result = HMS_Floor::save_floor_object($floor, TRUE);
                    if(PEAR::isError($result)) {
                        $final = HMS_Building::error_saving_floor($i);
                        return $final;
                    }
                    if($this->get_numbering_scheme() == 1 && $ctr == 0) $ctr = 1;
                }
            }

            $final = HMS_Building::successful_save_msg();
            return $final;
        } else {
            exit('you can not save residence halls.');
        }
    }

    /**
     * 
     */
    function make_floor_object($bid = NULL, $src, $fnumber)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $floor = &new HMS_Floor;
        if($bid == NULL) {
            $floor->set_building($this->id);
        } else {
            $floor->set_building($bid);
        }
        $floor->set_gender_type($src->gender_type);
        $floor->set_is_online($src->is_online);
        $floor->set_number_rooms($src->rooms_per_floor);
        $floor->set_floor_number($fnumber);
        $floor->set_bedrooms_per_room($src->bedrooms_per_room);
        $floor->set_beds_per_bedroom($src->beds_per_bedroom);
        $floor->set_deleted('0');
        $floor->set_freshman_reserved('0');
        return $floor;
    }

    function set_added_by_on(&$object)
    {
        $object->set_added_by();
        $object->set_added_on();
    }
    
    function error_saving_floor($floor)
    {
        $tpl['TITLE'] = "Error";
        $tpl['CONTENT']  = "Error saving floor number $floor for that Residence Hall.<br />";
        $tpl['CONTENT'] .= "This error has been logged. Please contact Electronic Student Services.<br />";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
        return $final;
    }

    function successful_save_msg()
    {
        $tpl['TITLE'] = "Successful Save!";
        $tpl['CONTENT'] = "Hall was saved successfully!";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
        return $final;
    }

    function delete_hall($bid)
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('id', $bid);
        $db->addValue('deleted_by', Current_User::getId());
        $db->addValue('deleted_on', time());
        $db->addValue('deleted', 1);
        $deleted_hall = $db->update(); 
        if(PEAR::isError($deleted_hall)) {
            PHPWS_Error::log($deleted_hall, 'hms', 'deleted_halls');
        }
        return $deleted_hall;
    }

    function add_residence_hall()
    {
        if(Current_User::allow('hms', 'add_halls') || Current_User::allow('hms', 'admin')) {
            PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
            $hall = &new HMS_Form;
            $content = $hall->add_residence_hall();
            return $content;
        } else {
            exit('you are a bad person that can not add residence halls.');
        }
    }

    function delete_residence_hall()
    {
        if(Current_User::authorized('delete_halls') || Current_User::authorized('hms', 'admin')) {
            PHPWS_Core::initModClass('hms', 'HMS_Building.php');
            PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
            PHPWS_Core::initModClass('hms', 'HMS_Room.php');
            PHPWS_Core::initModClass('hms', 'HMS_Bedroom.php');
            PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

            $deleted_beds = HMS_Bed::delete_beds_by_building($_REQUEST['halls']);
            $deleted_bedrooms = HMS_Bedroom::delete_bedrooms_by_building($_REQUEST['halls']);
            $deleted_rooms = HMS_Room::delete_rooms_by_floor($_REQUEST['halls']);
            $deleted_floors = HMS_Floor::delete_floors($_REQUEST['halls']);
            $deleted_hall = HMS_Building::delete_hall($_REQUEST['halls']);

            if($deleted_hall == TRUE && $deleted_floors == TRUE && $deleted_rooms == TRUE) {
                $tpl['TITLE'] = "Successful Deletion";
                $tpl['CONTENT'] = "Hall, floors, rooms, bedrooms and beds were succcessfully deleted.";
                $content = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            } else {
                $tpl['TITLE'] = "Error";
                $tpl['CONTENT'] = "There was a problem deleting the hall. <br />Please contact Electronic Student Services.";
                $content = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
            }
        } else {
            $content = "You are a BAD BAD PERSON!!!<br />";
        }
        
        return $content;
    }
  
    function edit_residence_hall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $hall = &new HMS_Form;
        $content = $hall->edit_residence_hall();
        return $content;
    }

    function select_residence_hall_for_add_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        return $form->select_residence_hall_for_add_floor();
    }

    function select_residence_hall_for_delete_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        return $form->select_residence_hall_for_delete_floor();
    }
    
    function select_residence_hall_for_delete()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $hall = &new HMS_Form;
        $content = $hall->select_residence_hall_for_delete();
        return $content;
    }

    function select_residence_hall_for_edit()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $hall = &new HMS_Form;
        $content = $hall->select_residence_hall_for_edit();
        return $content;
    }

    function select_residence_hall_for_overview()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $hall = &new HMS_Form;
        $content = $hall->select_residence_hall_for_overview();
        return $content;
    }

    function view_residence_hall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $hall_db = &new PHPWS_DB('hms_residence_hall');
        $hall_db->addColumn('hall_name');
        $hall_db->addWhere('id', $_REQUEST['halls']);
        $building_name = $hall_db->select('one');

        $content = "<h2>Building overview for " . $building_name . "</h2><br /><br />";

        $floors_db = &new PHPWS_DB('hms_floor');
        $floors_db->addColumn('id');
        $floors_db->addColumn('floor_number');
        $floors_db->addWhere('building', $_REQUEST['halls']);
        $floors_db->addWhere('deleted', '0');
        $floors_db->addOrder('floor_number ASC');
        $floors = $floors_db->select(); 

        foreach($floors as $afloor) {
            
            $rooms_db = &new PHPWS_DB('hms_room');
            $rooms_db->addColumn('id');
            $rooms_db->addColumn('room_number');
            $rooms_db->addColumn('displayed_room_number');
            $rooms_db->addWhere('floor_id', $afloor['id']);
            $rooms_db->addWhere('deleted', '0');
            $rooms_db->addOrder('room_number ASC');
            $rooms = $rooms_db->select();
           
            if($rooms == NULL || $rooms == FALSE) continue;

            $content .= '<u><a href="./index.php?module=hms&type=assignment&op=show_assignments_by_floor&halls=' . $_REQUEST['halls'] . '&floors=' . $afloor['floor_number'] . '&room_range=9999"><h3><b></u>Floor ' . $afloor['floor_number'] . '</b></h3></a><br />';

            foreach($rooms as $aroom) {
                $bedrooms_db = &new PHPWS_DB('hms_bedrooms');
                $bedrooms_db->addColumn('id');
                $bedrooms_db->addColumn('bedroom_letter');
                $bedrooms_db->addWhere('room_id', $aroom['id']);
                $bedrooms_db->addWhere('deleted', '0');
                $bedrooms = $bedrooms_db->select();

                if($bedrooms == FALSE || $bedrooms == NULL) continue;

                $content .= "&nbsp;&nbsp;&nbsp;&nbsp;<b>Room " . $aroom['room_number'] . ", Displayed " . $aroom['displayed_room_number'] . "</b><br />";

                foreach($bedrooms as $abedroom) {
                    $beds_db = &new PHPWS_DB('hms_beds');
                    $beds_db->addColumn('id');
                    $beds_db->addColumn('bed_letter');
                    $beds_db->addWhere('bedroom_id', $abedroom['id']);
                    $beds_db->addWhere('deleted', '0');
                    $beds = $beds_db->select();

                    if($beds == FALSE || $beds == NULL) continue;

                    foreach($beds as $abed) {
                        $assignments_db = &new PHPWS_DB('hms_assignment');
                        $assignments_db->addColumn('asu_username');
                        $assignments_db->addWhere('bed_id', $abed['id']);
                        $assignments_db->addWhere('deleted', '0');
                        $asu_username = $assignments_db->select('one');
                        if($asu_username) {
                            $asu_username .= " " . HMS_SOAP::get_full_name($asu_username);
                            $content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bedroom: " . $abedroom['bedroom_letter'] . "&nbsp;&nbsp;&nbsp;&nbsp;Bed: " . $abed['bed_letter'] . "&nbsp;&nbsp;&nbsp;&nbsp;" . $asu_username . "<br />";
                        } else {
                            $content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bedroom: " . $abedroom['bedroom_letter'] . "&nbsp;&nbsp;&nbsp;&nbsp;Bed: " . $abed['bed_letter'] . "&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"gray\">&lt;unassigned&gt;</font><br />";
                        }
//                        $content .= "[floor_id:".$afloor['id'].", room_id:".$aroom['id'].", bedroom_id:".$abedroom['id'].", bed_id:".$abed['id']."<br />";
                    } // end foreach($beds)
                    $content .= "<br />";
                } // end foreach($bedrooms)
            } // end foreach($rooms)
        } // end foreach($floors)
        return $content;
    }

    function add_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $floor = &new HMS_Form;
        return $floor->add_floor();
    }

    function confirm_delete_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $floor = &new HMS_Form;
        return $floor->confirm_delete_floor();
    }

    function save_new_floor()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('id', $_REQUEST['building']);
        $db->addValue('number_floors', $_REQUEST['floor_number']);
        $done = $db->update();

        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $floor = &new HMS_Floor;
        $floor->set_variables();
        $floor->set_gender_type($_REQUEST['gender_type']);
        $content = HMS_Floor::save_floor_object($floor, TRUE);
        return $content;
    }

    function delete_floor()
    {
        if(isset($_REQUEST['cancel'])) {
            PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
            return HMS_Maintenance::show_options();
        }

        if(Current_User::authorized('hms', 'delete_floors') || Current_User::authorized('hms', 'admin')) {
            $db = &new PHPWS_DB('hms_residence_hall');
            $db->addWhere('id', $_REQUEST['hall']);
            
            // set the number of floors in the hall object
            // delete the hall if deleting the last floor
            if($_REQUEST['floor'] == 1) {
                $db->addValue('deleted', 1);
            } else {
                $db->addValue('number_floors', $_REQUEST['floor'] - 1);
            }
            $success = $db->update();

            // delete the floor
            PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
            $success = HMS_Floor::delete_floors($_REQUEST['hall'], $_REQUEST['floor'], TRUE);
            // delete the rooms
            PHPWS_Core::initModClass('hms', 'HMS_Room.php');
            $success = HMS_Room::delete_rooms_by_floor($_REQUEST['hall'], $_REQUEST['floor'], TRUE);

            if($success) {
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $content = "Floor " . $_REQUEST['floor'] . " has been deleted.<br /><br />";
                $content .= PHPWS_Text::secureLink('Return to Maintenance Screen', 'hms', array('type'=>'maintenance', 'op'=>'show_maintenance_options'));
                return $content;
            }
        } else {
            exit('you can not delete floors.');
        }
    }

    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'add_hall':
                return HMS_Building::add_residence_hall();
                break;
            case 'add_floor':
                return HMS_Building::add_floor();
                break;
            case 'confirm_delete_floor':
                return HMS_Building::confirm_delete_floor();
                break;
            case 'delete_floor':
                return HMS_Building::delete_floor();
                break;
            case 'select_residence_hall_for_add_floor':
                return HMS_Building::select_residence_hall_for_add_floor();
                break;
            case 'select_residence_hall_for_delete_floor':
                return HMS_Building::select_residence_hall_for_delete_floor();
                break;
            case 'delete_residence_hall':
                return HMS_Building::delete_residence_hall();
                break;
            case 'edit_residence_hall':
                return HMS_Building::edit_residence_hall();
                break;
            case 'select_residence_hall_for_delete':
                return HMS_Building::select_residence_hall_for_delete();
                break;
            case 'select_residence_hall_for_edit':
                return HMS_Building::select_residence_hall_for_edit();
                break;
            case 'save_residence_hall':
                $hall = &new HMS_Building();
                $hall->set_variables();
                return $hall->save_residence_hall();
                break;
            case 'save_new_floor':
                return HMS_Building::save_new_floor();
                break;
            case 'select_residence_hall_for_overview':
                return HMS_Building::select_residence_hall_for_overview();
                break;
            case 'view_residence_hall':
                return HMS_Building::view_residence_hall();
                break;
            default:
                return "you are using a building function";
                break;
        }
    }
}
?>
