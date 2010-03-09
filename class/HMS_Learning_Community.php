<?php
PHPWS_Core::initModClass('hms', 'HMS_Item.php');

/**
 * Learning Community objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Learning_Community extends HMS_Item
{
    public $id=NULL;
    public $community_name=NULL;
    public $abbreviation;
    public $capacity;
    public $hide;
    public $error="";

    public $allowed_student_types; //A string containing a character for each allowed student type, maxLen() == 16;
    public $extra_info; // A text field, show to the student when the RLC is selected

    public function __construct($id = 0)
    {
        $this->construct($id);
    }

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

            if(PHPWS_Error::logIfError($result)){
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($result->toString());
            }

            return json_encode($result);
        }
    }

    /**
     * Returns an associative array containing the list of RLC abbreviations keyed by their id.
     */
    public function getRLCListAbbr($student_type = NULL)
    {
        $db = new PHPWS_DB('hms_learning_communities');

        $db->addColumn('id');
        $db->addColumn('abbreviation');
        if(!is_null($student_type) && strlen($student_type) == 1)
        $db->addColumn('allowed_student_types', "%{$student_type}%", 'ilike');

        $result = $db->select('assoc');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /**
     * Returns an associative array containing the list of RLCs using their full names, keyed by their id.
     */
    public function getRLCList($hidden = NULL, $student_type = NULL)
    {
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        if(!is_null($student_type) && strlen($student_type) == 1)
        $db->addWhere('allowed_student_types', "%{$student_type}%", 'ilike');

        if($hidden === FALSE){
            $db->addWhere('hide', 0);
        }

        $rlc_choices = $db->select('assoc');

        if(PHPWS_Error::logIfError($rlc_choices)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($rlc_choices->toString());
        }

        return $rlc_choices;
    }

    //TODO: move this....
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

    //TODO move this...
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

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-applications-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","roommate","email","second_choice","third_choice","major","application_date","denied"' . "\n";

        // get the userlist
        $db = new PHPWS_DB('hms_learning_community_applications');
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

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-assignments-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","email"' . "\n";

        // get the list of assignments
        $db = new PHPWS_DB('hms_learning_community_assignment');
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
}
?>
