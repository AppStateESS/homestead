<?php


class ExportRlcAppsCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ExportRlcApps');
    }

    // TODO: rewrite this
    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'view_rlc_applications') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view rlc applications');
        }

        $term = Term::getSelectedTerm();

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        if($context->get('communityId')!= 0)
        {
            $db->addWhere('id',$context->get('communityId'));
        }
        $title = $db->select('one');

        $filename = $title . '-applications-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"Last name","First Name","Middle Name","Gender","Roommate","Email","Second Choice","Third Choice","Major","Application Date","Denied"' . "\n";

        // get the userlist
        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addColumn('username');
        $db->addColumn('rlc_second_choice_id');
        $db->addColumn('rlc_third_choice_id');
        $db->addColumn('date_submitted');
        if($context->get('communityId')!= 0)
        {
            $db->addWhere('rlc_first_choice_id', $context->get('communityId'));
        }
        $db->addWhere('term', Term::getSelectedTerm());
        $db->addOrder('denied asc');
        //$db->addWhere('denied', 0); // Only show non-denied applications
        $users = $db->select();


        foreach($users as $user) {
            PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
            $roomie = NULL;

            $roomie = HMS_Roommate::has_confirmed_roommate($user['username'], $term) ? HMS_Roommate::get_Confirmed_roommate($user['username'], $term) : NULL;
            if($roomie == NULL) {
                $roomie = HMS_Roommate::has_roommate_request($user['username'], $term) ? HMS_Roommate::get_unconfirmed_roommate($user['username'], $term) . ' *pending* ' : NULL;
            }

            $student = StudentFactory::getStudentByUsername($user['username'], Term::getSelectedTerm());

            $buffer .= '"' . $student->getLastName() . '",';
            $buffer .= '"' . $student->getFirstName() . '",';
            $buffer .= '"' . $student->getMiddleName() . '",';
            $buffer .= '"' . $student->getPrintableGender() . '",';

            if($roomie != NULL) {
                $buffer .= '"' . $roomie->getFullName() . '",';
            } else {
                $buffer .= '"",';
            }
            $buffer .= '"' . $student->getUsername() . '@appstate.edu' . '",';

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
}
