<?php
PHPWS_Core::initModClass('hms', 'HMS_Item.php');

/**
 * Learning Community objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Learning_Community extends HMS_Item
{
    var $id=NULL;
    var $community_name=NULL;
    var $abbreviation;
    var $capacity;
    var $hide;
    var $error="";
    //A string containing a character for each allowed student type, maxLen() == 16;
    var $allowed_student_types;

    public function getDb()
    {
        return new PHPWS_DB('hms_learning_communities');
    }

    public function set_error_msg($msg)
    {
        $this->error .= $msg;
    }

    public function get_error_msg()
    {
        return $this->error;
    }

    public function set_id($id)
    {
        $this->id = $id;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function set_community_name($name)
    {
        $this->community_name = $name;
    }

    public function get_community_name()
    {
        return $this->community_name;
    }

    public function set_abbreviation($abb)
    {
        $this->abbreviation = $abb;
    }

    public function get_abbreviation()
    {
        return $this->abbreviation;
    }

    public function set_capacity($cap)
    {
        $this->capacity = $cap;
    }

    public function get_capacity()
    {
        return $this->capacity;
    }

    public function set_variables()
    {
        if(isset($_REQUEST['id']) && $_REQUEST['id'] != NULL) $this->set_id($_REQUEST['id']);
        $this->set_community_name($_REQUEST['community_name']);
        $this->set_abbreviation($_REQUEST['abbreviation']);
        $this->set_capacity($_REQUEST['capacity']);
    }

    public function save_learning_community()
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/premission_denied.tpl');
        }

        $rlc = new HMS_Learning_Community();
        $rlc->set_variables();

        $db = & new PHPWS_DB('hms_learning_communities');
        
        if($rlc->get_id() != NULL) {
            $db->addWhere('id', $rlc->get_id());
            $success = $db->saveObject($rlc);
        } else {
            $db->addValue('community_name', $rlc->get_community_name());
            $db->addValue('abbreviation', $rlc->get_abbreviation());
            $db->addValue('capacity', $rlc->get_capacity());
            $success = $db->insert();
        }
        
        if(PEAR::isError($success)) {
            $msg = '<font color="red"><b>There was a problem saving the ' . $rlc->get_community_name() . ' Learning Community</b></font>';
        } else {
            $msg    = "The Residential Learning Community " . $rlc->get_community_name() . " was saved successfully!";
        }
        
        $final  = HMS_Learning_Community::add_learning_community($msg);

        return $final;
    }

    /**
      * Show the interface for editing a learning community, users can change
      * the name and/or the capacity of the rlc.
      *
      * @param string $message An error message to display above the form
      * @return string The html of the interface
      */
    public function show_edit_learning_community($message = null)
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/premission_denied.tpl');
        }

        $tpl = array();
        $tpl['MESSAGE'] = ''.$message;

        $form = new PHPWS_Form('edit_rlc');
        
        $form->addSelect('rlc', HMS_Learning_Community::getRLCList());
        $form->setLabel('rlc', 'Learning Community');
        $form->setExtra('rlc', 'onChange=getVals(this.value)');

        $form->addText('name');
        $form->setLabel('name', 'New Learning Community Name');
        $form->setSize('name', 32);

        $form->addText('abbv');
        $form->setLabel('abbv', 'New Learning Community Abbreviation');
        $form->setSize('abbv', 16);

        $form->addText('capacity');
        $form->setLabel('capacity', 'Capacity');
        $form->setSize('capacity', 4);

        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'do_edit_learning_community');
        $form->addSubmit('save_changes', 'Save Changes');

        $form->mergeTemplate($tpl);

        javascript('/jquery/');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/edit_learning_community.tpl');
    }

    /**
      * Modify the attributes of a learning community.
      *
      * @param int $id The id of the learning community to edit
      * @param string $name The new name of the learning community
      * @param string $abbv The new abbreviation for the learning community
      * @param int $capacity The new capacity of the learning community
      * @return mixed $success true for success error message for failure
      */
    public function edit_learning_community($id, $name, $abbv, $capacity)
    {
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $id);

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            return 'Error looking up the rlc in the database.';
        }

        $db->reset();
        $db->addWhere('id', $id);
        $db->addValue('community_name', $name);
        $db->addValue('abbreviation', $abbv);
        $db->addValue('capacity', $capacity);

        $result = $db->update();

        if(!PHPWS_Error::logIfError($result)){
            return true;
        }

        return 'Error updating the RLC.';
    }

    public function do_edit_learning_community()
    {
        if(isset($_REQUEST['rlc']) && isset($_REQUEST['name']) 
            && isset($_REQUEST['abbv']) && isset($_REQUEST['capacity']))
        {
            if(!is_numeric($_REQUEST['rlc']))
            {
                return HMS_Learning_Community::show_edit_learning_community('The RLC ID must be numeric');
            }

            if(!is_numeric($_REQUEST['capacity']))
            {
                return HMS_Learning_Community::show_edit_learning_community('The capacity must be numeric');
            }

            if(!(strlen($_REQUEST['name']) <= 32))
            {
                return HMS_Learning_Community::show_edit_learning_community('The RLC name must be less than 32 characters long');
            }

            if(!(strlen($_REQUEST['abbv']) <= 16))

            {
                return HMS_Learning_Community::show_edit_learning_community('The RLC abbreviation must be less than 16 characters long');
            }

            $result = HMS_Learning_Community::edit_learning_community($_REQUEST['rlc'], $_REQUEST['name'], $_REQUEST['abbv'], $_REQUEST['capacity']);

            if($result !== TRUE)
                return HMS_Learning_Community::show_edit_learning_community($result);

            else 
                return HMS_Learning_Community::show_edit_learning_community();
        }
    }

    /**
      * Get a JSON encoded view of the learning community.
      *
      * @param int $id The id of the learning community to return
      * @return json JSON encoded object
      */
    public function JSONLearningCommunity($id)
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            die();
        }
        if(is_numeric($id)){
            $db = new PHPWS_DB('hms_learning_communities');
            $db->addWhere('id', $id);
            $result = $db->select();

            if(!PHPWS_Error::logIfError($result)){
                return json_encode($result);
            }
        }

        return json_encode(array('error' => null));
    }
    
    /*
     * Returns a HMS_Form that prompts the user for the name of the RLC to add
     */
    public function add_learning_community($msg = NULL)
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/premission_denied.tpl');
        }
    
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $tpl = HMS_Form::fill_learning_community_data_display();
        $tpl['TITLE'] = "Add a Learning Community";
        $tpl['MESSAGE'] = $msg;
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_learning_community_data.tpl');
        return $final;
    }
   
    /*
     * Returns a HMS_Form that allows the user to select a RLC to delete
     */
    public function select_learning_community_for_delete()
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

    /*
     * Returns a HMS_Form that allows the user to confirm deletion of a RLC
     */
    public function confirm_delete_learning_community()
    {
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id', $_REQUEST['lcs']);
        $result = $db->select('one');
      
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'delete_learning_community');
        $form->addHidden('community_name', $result);
        $form->addHidden('id', $_REQUEST['lcs']);
        $form->addSubmit('delete', _('Delete Community'));
        $form->addSubmit('save', _('Keep this Community'));
        
        $tpl = $form->getTemplate();

        $tpl['RLC']     = $result;
        $tpl['TITLE']   = "Confirm Deletion";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/confirm_learning_community_delete.tpl');

        return $final;
    }

    /*
     * Actually deletes a learning community
     */
    public function delete_learning_community()
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/premission_denied.tpl');
        }

        if(!isset($_REQUEST['delete']) || $_REQUEST['delete'] != "Delete Community") {
            return HMS_Learning_Community::select_learning_community_for_delete();
        }

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $_REQUEST['id']);
        $db->addWhere('community_name', $_REQUEST['community_name']);
        $result = $db->delete();

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $count = $db->select('count');
       
        if($count == NULL) {
            $msg = "You have deleted the last residential learning community.";
            return HMS_Learning_Community::add_learning_community($msg);
        }

        return HMS_Learning_Community::select_learning_community_for_delete();
    }
  
    /*
     * Let admins get a roster for a particular learning community
     */
    public function search_by_rlc()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->addDropBox('rlc', HMS_Learning_Community::getRLCList());
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'view_by_rlc');
        $form->addSubmit('submit', _('Search!'));

        $tags = $form->getTemplate();
        $tags['TITLE'] = "RLC Search";
        
        $final = PHPWS_Template::processTemplate($tags, 'hms', 'admin/search_by_rlc.tpl');
        return $final;
    } 

    /*
     * Actually display the roster for the rlc specified in search_by_rlc
     */
    public function view_by_rlc($rlc_id = NULL, $success_msg = NULL, $error_msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php'); 

        // If the rlc_id wasn't passed in, get it from the request
        if(!isset($rlc_id)){
            $rlc_id = $_REQUEST['rlc'];
        }

        if(isset($success_msg)){
            $tpl['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tpl['ERROR_MSG'] = $error_msg;
        }

        $tpl['RLC_PAGER'] = HMS_RLC_Assignment::view_by_rlc_pager($rlc_id);
        $tpl['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to previous'), 'hms', array('type'=>'rlc', 'op'=>'search_by_rlc'));

        return PHPWS_Template::processTemplate($tpl, 'hms', 'admin/rlc_roster.tpl');
    }

    /*
     * Verify that the user actually wants to remove this student from an RLC
     */
    public function confirm_remove_from_rlc()
    {
        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'hms_assignment_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_communities', 'rlc_id', 'id');
        $db->addColumn('hms_learning_communities.community_name');
        $db->addColumn('hms_learning_communities.id', NULL, 'community_id');
        $db->addColumn('hms_learning_community_applications.user_id');
        $db->addWhere('hms_learning_community_assignment.id', $_REQUEST['id']);
        $result = $db->select('row');

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return HMS_Learning_Community::view_by_rlc($_REQUEST['rlc'], null, 'Database error.');
        }
        
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'perform_remove_from_rlc');
        $form->addHidden('id', $_REQUEST['id']);
        $form->addHidden('rlc', $_REQUEST['rlc']);
        $form->addSubmit('remove', _('Remove from RLC and Re-Activate Application'));
        $form->addSubmit('cancel', _('Do Nothing'));

        $tpl = $form->getTemplate();
        $tpl['NAME'] = HMS_SOAP::get_name($result['user_id']);
        $tpl['RLC'] = $result['community_name'];

        return PHPWS_Template::process($tpl, 'hms', 'admin/confirm_remove_from_rlc.tpl');
    }

    /*
     * Actually remove a user from an RLC
     */
    public function perform_remove_from_rlc()
    {
        if(!isset($_REQUEST['remove']) || $_REQUEST['remove'] != "Remove from RLC and Re-Activate Application" || !isset($_REQUEST['id'])) {
            return HMS_Learning_Community::view_by_rlc();
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('hms_assignment_id', $_REQUEST['id']);
        $db->addValue('hms_assignment_id', null);
        $result = $db->update();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return HMS_Learning_Community::view_by_rlc($_REQUEST['rlc'], null, 'Database error.');
        }

        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('id', $_REQUEST['id']);
        $db->delete();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return HMS_Learning_Community::view_by_rlc($_REQUEST['rlc'], null, 'Database error.');
        }
        
        return HMS_Learning_Community::view_by_rlc($_REQUEST['rlc'], 'Deleted.');
    }
    
    /**
     * Returns an associative array containing the list of RLC abbreviations keyed by their id.
     */
    public function getRLCListAbbr($student_type = NULL)
    {
        $db = &new PHPWS_DB('hms_learning_communities');

        $db->addColumn('id');
        $db->addColumn('abbreviation');
        if(!is_null($student_type) && strlen($student_type) == 1)
            $db->addColumn('allowed_student_types', "%{$student_type}%", 'ilike');

        $result = $db->select('assoc');
        return $result;
    }

    /**
     * Returns an associative array containing the list of RLCs using their full names, keyed by their id.
     */
    public function getRLCList($hidden = NULL, $student_type = NULL)
    {
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        if(!is_null($student_type) && strlen($student_type) == 1)
            $db->addWhere('allowed_student_types', "%{$student_type}%", 'ilike');

        if($hidden === FALSE){
            $db->addWhere('hide', 0);
        }

        $rlc_choices = $db->select('assoc');

        if(PEAR::isError($rlc_choices)){
            #PHPWS_Error::log();
        }

        return $rlc_choices;
    }

    /*
     * Main public function for RLC maintenance
     */
    public function main()
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') 
            && !Current_User::allow('hms', 'view_rlc_applications')
            && !Current_User::allow('hms', 'approve_rlc_applications')
            && !Current_User::allow('hms', 'view_rlc_members') )
        {
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op'])
        {
            case 'add_learning_community':
                return HMS_Learning_Community::add_learning_community();
                break;
            case 'save_learning_community':
                return HMS_Learning_Community::save_learning_community();
                break;
            case 'edit_learning_community':
                return HMS_Learning_Community::show_edit_learning_community();
                break;
            case 'do_edit_learning_community':
                return HMS_Learning_Community::do_edit_learning_community();
                break;
            case 'get_json':
                print(HMS_Learning_Community::JSONLearningCommunity($_REQUEST['id']));
                die();
                break;
            case 'select_learning_community_for_delete':
                return HMS_Learning_Community::select_learning_community_for_delete();
                break;
            case 'delete_learning_community':
                return HMS_Learning_Community::delete_learning_community();
                break;
            case 'confirm_delete_learning_community':
                return HMS_Learning_Community::confirm_delete_learning_community();
                break;
            case 'assign_applicants_to_rlcs':
                return HMS_Learning_Community::assign_applicants_to_rlcs();
                break;
            case 'show_view_denied':
                return HMS_Learning_Community::show_view_denied();
                break;
            case 'view_rlc_assignments':
                return HMS_Learning_Community::view_rlc_assignments();
                break;
            case 'view_rlc_application':
                PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
                return HMS_RLC_Application::view_rlc_application($_REQUEST['username']);
                break;
            case 'rlc_assignments_submit':
                return HMS_Learning_Community::rlc_assignments_submit();
                break;
            case 'rlc_application_export':
                return HMS_Learning_Community::rlc_application_export();
                break;
            case 'search_by_rlc':
                return HMS_Learning_Community::search_by_rlc();
                break;
            case 'view_by_rlc':
                return HMS_Learning_Community::view_by_rlc();
                break;
            case 'confirm_remove_from_rlc':
                return HMS_Learning_Community::confirm_remove_from_rlc();
                break;
            case 'perform_remove_from_rlc':
                return HMS_Learning_Community::perform_remove_from_rlc();
                break;
            case 'deny_rlc_application':
                PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
                return HMS_RLC_Application::deny_rlc_application();
                break;
            case 'un_deny_rlc_application':
                PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
                return HMS_RLC_Application::un_deny_rlc_application();
                break;
            default:
                return "unknown RLC op: {$_REQUEST['op']} <br />";
                break;
        }
    }

    /*
     * Validates submission of the first page of the rlc application form.
     * If ok, shows the second page of the application form.
     * Otherwise, displays page one again with an error message.
     */
     //TODO: move this to HMS_RLC_Application
    public function rlc_application_page1_submit()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');

        # Check for invalid input on page 1
        $message = HMS_RLC_Application::validate_rlc_application_page1();
        if($message !== TRUE){
            # Show page one again with error message
            return HMS_RLC_Application::show_rlc_application_form_page1($message);
        }else{
            return HMS_RLC_Application::show_rlc_application_form_page2();
        }
    }

    //TODO: add comments and move this to HMS_RLC_Application
    public function rlc_application_page2_submit()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        
        $template = array();

        # Check for invalid input on page 2
        $message = HMS_RLC_Application::validate_rlc_application_page2();
        if($message !== TRUE){
            # Show page two again with error message
            return HMS_RLC_Application::show_rlc_application_form_page2($message);
        }else{

            # Save the data to the database
            $result = HMS_RLC_Application::check_for_application($_SESSION['asu_username']);

            # Check to make sure an RLC application doesn't already exist
            if(!(PEAR::isError($result)) && $result !== FALSE){
                $template['MESSAGE'] = "Sorry, you have already submitted an RLC Application.";
                return PHPWS_Template::process($template, 'hms','student/student_success_failure_message.tpl');
            }
            
            $result = HMS_RLC_Application::save_application();

            # Check for an error
            if(PEAR::isError($result)){
                $template['MESSAGE'] = "Sorry, there was an error working with the database. Your application could not be saved.";
            }else{
                $template['SUCCESS'] = "Your application was submitted successfully.";
                $template['SUCCESS'] .= "<br /><br />";
                $template['SUCCESS'] .= PHPWS_Text::secureLink(_('Back to Main Menu'), 'hms', array('type'=>'student','op'=>'main'));
            }
            
            return PHPWS_Template::process($template, 'hms', 'student/rlc_signup_confirmation.tpl');
        }

    }

    public function assign_applicants_to_rlcs($success_msg = NULL, $error_msg = NULL)
    {
        if( !Current_User::allow('hms', 'view_rlc_applications') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $tags = array();
        $tags['TITLE'] = 'RLC Assignments - ' . Term::toString(Term::getSelectedTerm());
        $tags['SUMMARY']           = HMS_Learning_Community::display_rlc_assignment_summary();
        $tags['DROPDOWN']          = PHPWS_Template::process(HMS_RLC_Application::getDropDown(), 'hms', 'admin/dropdown_template.tpl');
        $tags['ASSIGNMENTS_PAGER'] = HMS_RLC_Application::rlc_application_admin_pager();

        if(isset($success_msg)){
            $tags['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tags['ERROR_MSG'] = $error_msg;
        }

        $export_form = &new PHPWS_Form('export_form');
        $export_form->addHidden('type','rlc');
        $export_form->addHidden('op','rlc_application_export');
        
        $export_form->addDropBox('rlc_list',HMS_Learning_Community::getRLCListAbbr());
        $export_form->addSubmit('submit');
        
        $export_form->mergeTemplate($tags);
        $tags = $export_form->getTemplate();
        
        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }

    public function display_rlc_assignment_summary()
    {
        $template = array();

        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addColumn('capacity');
        $db->addColumn('id');
        $communities = $db->select();

        if(!$communities) {
            $template['no_communities'] = _('No communities have been enterred.');
            return PHPWS_Template::process($template, 'hms',
                    'admin/make_new_rlc_assignments_summary.tpl');
        }

        $count = 0;
        $total_assignments = 0;
        $total_available = 0;

        foreach($communities as $community) {
            $db = &new PHPWS_DB('hms_learning_community_assignment');
            $db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
            $db->addWhere('rlc_id', $community['id']);
            $db->addWhere('gender', MALE);
            $db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
            $male = $db->select('count');
            
            $db->resetWhere();
            $db->addWhere('rlc_id', $community['id']);
            $db->addWhere('gender', FEMALE);
            $db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
            $female = $db->select('count');

            if($male   == NULL) $male   = 0;
            if($female == NULL) $female = 0;
            $assigned = $male + $female;
            
            $template['headings'][$count]['HEADING']       = $community['community_name'];
           
            $template['assignments'][$count]['ASSIGNMENT'] = "$assigned ($male/$female)";
            $total_assignments += $assigned;
            
            $template['available'][$count]['AVAILABLE']    = $community['capacity'];
            $total_available += $community['capacity'];
            
            $template['remaining'][$count]['REMAINING']    = $community['capacity'] - $assigned;
            $count++;
        }

        $template['TOTAL_ASSIGNMENTS'] = $total_assignments;
        $template['TOTAL_AVAILABLE'] = $total_available;
        $template['TOTAL_REMAINING'] = $total_available - $total_assignments;

        return PHPWS_Template::process($template, 'hms',
                'admin/make_new_rlc_assignments_summary.tpl');
    }

/** HMS_Forms did not contain show_assign_rlc_members_to_rooms() so this
    couldn't have been in use.
    public function assign_rlc_members_to_rooms()
    {
        PHPWS_Core::initModClass('hms','HMS_Forms.php');

        return HMS_Form::show_assign_rlc_members_to_rooms();
    }
*/
    public function view_rlc_assignments()
    {
        if( !Current_User::allow('hms', 'view_rlc_members') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms','HMS_RLC_Assignment.php');

        return HMS_RLC_Assignment::rlc_assignment_admin_pager();
    }

    public function rlc_assignments_submit()
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        $errors = array();

        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');

        $app = new PHPWS_DB('hms_learning_community_applications');
        $ass = new PHPWS_DB('hms_learning_community_assignment');

        # Foreach rlc assignment made
        # $app_id is the 'id' column in the 'learning_community_applications' table, tells which student we're assigning
        # $rlc_id is the 'id' column in the 'learning_communitites' table, and refers to the RLC selected for the student
        foreach($_REQUEST['final_rlc'] as $app_id => $rlc_id){
            
            $app->reset();
            $ass->reset();
            
            # Lookup the student's RLC application (so we can have their username)
            $app->addWhere('id', $app_id);
            $application = $app->select('row');
           
            # Insert a new assignment in the 'learning_community_assignment' table
            $ass->addValue('rlc_id',            $rlc_id);
            $ass->addValue('gender',            HMS_SOAP::get_gender($application['user_id'], TRUE));
            $ass->addValue('assigned_by',  Current_User::getUsername());
            $ass_id = $ass->insert();

            # Log the assignment
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($application['user_id'], ACTIVITY_ASSIGN_TO_RLC, Current_User::getUsername(), "New Assignment");

            # Update the RLC application with the assignment id
            $app->reset();
            $app->addValue('hms_assignment_id', $ass_id);
            $app->addWhere('id', $app_id);
            $app->update();
        }

        PHPWS_Core::goBack();
        return;
    }

    
    /**
     * Exports the pending RLC applications into a CSV file.
     * Looks in $_REQUEST for which RLC to export.
     */
    public function rlc_application_export()
    {
        if( !Current_User::allow('hms', 'view_rlc_applications') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $term = Term::getSelectedTerm();

        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-applications-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","roommate","email","second_choice","third_choice","major","application_date","denied"' . "\n";

        // get the userlist
        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->addColumn('user_id');
        $db->addColumn('rlc_second_choice_id');
        $db->addColumn('rlc_third_choice_id');
        $db->addColumn('date_submitted');
        $db->addWhere('rlc_first_choice_id', $_REQUEST['rlc_list']);
        $db->addWhere('term', Term::getSelectedTerm());
        $db->addOrder('denied asc');
        //$db->addWhere('denied', 0); // Only show non-denied applications
        $users = $db->select();


        foreach($users as $user) {
            PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
            $roomie = NULL;

            $roomie = HMS_Roommate::has_confirmed_roommate($user, $term) ? HMS_Roommate::get_Confirmed_roommate($user, $term) : NULL;
            if($roomie == NULL) {
                $roomie = HMS_Roommate::has_roommate_request($user, $term) ? HMS_Roommate::get_unconfirmed_roommate($user, $term) . ' *pending* ' : NULL;
            }

            $sinfo = HMS_SOAP::get_student_info($user['user_id']);
            $buffer .= '"' . $sinfo->last_name . '",';
            $buffer .= '"' . $sinfo->first_name . '",';
            $buffer .= '"' . $sinfo->middle_name . '",';
            $buffer .= '"' . $sinfo->gender . '",';
            if($roomie != NULL) {
                $buffer .= '"' . HMS_SOAP::get_full_name($roomie) . '",';
            } else {
                $buffer .= '"",';
            }
            $buffer .= '"' . $user['user_id'] . '@appstate.edu' . '",';
            
            if(isset($user['rlc_second_choice_id'])) {
                $db = new PHPWS_DB('hms_learning_communities');
                $db->addColumn('community_name');
                $db->addWhere('id', $user['rlc_second_choice_id']);
                $result = $db->select('one');
                if(!PHPWS_Error::logIfError($result)) {
                    $buffer .= '"' . $result . '",';
                }
            } else {
                $buffer .= '"",';
            }
            
            if(isset($user['rlc_third_choice_id'])) {
                $db = new PHPWS_DB('hms_learning_communities');
                $db->addColumn('community_name');
                $db->addWhere('id', $user['rlc_third_choice_id']);
                $result = $db->select('one');
                if(!PHPWS_Error::logIfError($result)) {
                    $buffer .= '"' . $result . '",';
                }
            } else {
                $buffer .= '"",';
            }

            //Major for this user, N/A for now
            $buffer .= '"N/A",';

            //Application Date
            if(isset($user['date_submitted'])){
                PHPWS_Core::initModClass('hms', 'HMS_Util.php');
                $buffer .= '"' . HMS_Util::get_long_date($user['date_submitted']) . '",';
            } else {
                $buffer .= '"Error with the submission Date",';
            }

            //Denied
            $buffer .= (isset($user['denied']) && $user['denied'] == 1) ? '"yes"' : '"no"';
            $buffer .= "\n";
        }

        //HERES THE QUERY:
        //select hms_learning_community_applications.user_id, date_submitted, rlc_first_choice.abbreviation as first_choice, rlc_second_choice.abbreviation as second_choice, rlc_third_choice.abbreviation as third_choice FROM (SELECT hms_learning_community_applications.user_id, hms_learning_communities.abbreviation FROM hms_learning_communities,hms_learning_community_applications WHERE hms_learning_communities.id = hms_learning_community_applications.rlc_first_choice_id) as rlc_first_choice, (SELECT hms_learning_community_applications.user_id, hms_learning_communities.abbreviation FROM hms_learning_communities,hms_learning_community_applications WHERE hms_learning_communities.id = hms_learning_community_applications.rlc_second_choice_id) as rlc_second_choice, (SELECT hms_learning_community_applications.user_id, hms_learning_communities.abbreviation FROM hms_learning_communities,hms_learning_community_applications WHERE hms_learning_communities.id = hms_learning_community_applications.rlc_third_choice_id) as rlc_third_choice, hms_learning_community_applications WHERE rlc_first_choice.user_id = hms_learning_community_applications.user_id AND rlc_second_choice.user_id = hms_learning_community_applications.user_id AND rlc_third_choice.user_id = hms_learning_community_applications.user_id;
       
        //Download file
        if(ob_get_contents())
            print('Some data has already been output, can\'t send file');
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
            header('Content-Type: application/force-download');
        else
            header('Content-Type: application/octet-stream');
        if(headers_sent())
            print('Some data has already been output to browser, can\'t send file');
        header('Content-Length: '.strlen($buffer));
        header('Content-disposition: attachment; filename="'.$filename.'"');
        echo $buffer;
        die();
    }

    /**
     * Exports the completed RLC assignments.
     */
    public function rlc_assignment_export()
    {
        if( !Current_User::allow('hms', 'view_rlc_applications') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-assignments-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","email"' . "\n";
        
        // get the list of assignments
        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addColumn('user_id');
        $db->addWhere('hms_learning_community_assignment.rlc_id',$_REQUEST['rlc_list']); # select assignments only for the given RLC
        $users = $db->select();

        foreach($users as $user){
            $sinfo = HMS_SOAP::get_student_info($user['user_id']);
            $buffer .= '"' . $sinfo->last_name . '",';
            $buffer .= '"' . $sinfo->first_name . '",';
            $buffer .= '"' . $sinfo->middle_name . '",';
            $buffer .= '"' . $sinfo->gender . '",';
            $buffer .= '"' . $user['user_id'] . '@appstate.edu' . '"' . "\n";
        }
        
        //Download file
        if(ob_get_contents())
            print('Some data has already been output, can\'t send file');
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
            header('Content-Type: application/force-download');
        else
            header('Content-Type: application/octet-stream');
        if(headers_sent())
            print('Some data has already been output to browser, can\'t send file');
        header('Content-Length: '.strlen($buffer));
        header('Content-disposition: attachment; filename="'.$filename.'"');
        echo $buffer;
        die();
    }

    /**
     * Shows an interface listing the denied RLC applications
     */
    public function show_view_denied($success_msg = NULL, $error_msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $tpl = array();

        $tpl['TITLE'] = "Denied RLC Applications - " . Term::toString(Term::getSelectedTerm());
        $tpl['DENIED_PAGER'] = HMS_RLC_Application::denied_pager();

        if(isset($success_msg)){
            $tpl['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tpl['ERROR_MSG'] = $error_msg;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/view_denied_rlc_applications.tpl');
    }

    public function rowTags(){
        return array('ACTIONS' => "<a href=\"index.php?module=hms&action=ShowAddRlc&id={$this->id}\">Edit</a>");
    }

    public function allowStudentType($student_type){
        if(!is_string($student_type) 
            || strlen($student_type) != 1 
            || stripos($this->allowed_student_types, $student_type) === false
        ){
            return false;
        }

        return true;
    }
}
?>
