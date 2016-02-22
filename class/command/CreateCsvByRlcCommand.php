<?php


class CreateCsvByRlcCommand extends Command {

    public function getRequestVars()
    {
        return array (
                'action' => 'CreateCsvByRlc'
        );
    }

    public function execute(CommandContext $context)
    {
        $input = $_REQUEST['id'];
        $term = Term::getSelectedTerm();
        $memberList = RlcMembershipFactory::getRlcMembersById($input, $term);

        $output = fopen('php://output', 'w');

        fputcsv($output, array('Name', 'Banner Id', 'Gender', 'Student Type',
                                'Username', 'Status', 'Assignment', 'Roommate'));

        foreach($memberList as $member)
        {
            $username = $member['username'];

            $student = StudentFactory::getStudentByUsername($username, $term);

            $name = $student->getName();
            $bannerId = $student->getBannerId();
            $gender = $student->getPrintableGenderAbbreviation();
            $studentType = $student->getPrintableType();

            $rlcAssign = HMS_RLC_Assignment::getAssignmentByUsername($username, $term);

            $state = $rlcAssign->getStateName();

            $stateDisplay = '';

            if($state == 'confirmed'){
                $stateDisplay = $state;
            }else if($state == 'declined'){
                $stateDisplay = $state;
            }else if($state == 'new'){
                $stateDisplay = 'not invited';
            }else if($state == 'invited'){
                $stateDisplay = 'pending';
            }else if($state == 'selfselect-invite'){
                $stateDisplay = 'self-select available';
            }else if($state == 'selfselect-assigned'){
                $stateDisplay = 'self-selected';
            }
            $status = $stateDisplay;

            $roomAssign = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), Term::getSelectedTerm());


            $assignment = 'n/a';

            if(isset($roomAssign)){
                $assignment = $roomAssign->where_am_i();
            }

            $allRoommates = HMS_Roommate::get_all_roommates($username, $term);
            $roommates = 'N/A'; // Default text

            if(sizeof($allRoommates) > 1) {
                // Don't show all the roommates
                $roommates = "Multiple Requests";
            }
            elseif(sizeof($allRoommates) == 1) {
                // Get other roommate
                $otherGuy = StudentFactory::getStudentByUsername($allRoommates[0]->get_other_guy($username), $term);
                $roommates = $otherGuy->getProfileLink();
                // If roommate is pending then show little status message
                if(!$allRoommates[0]->confirmed) {
                    $roommates .= " (Pending)";
                }
            }

            $row = array($name, $bannerId, $gender, $studentType, $username, $status, $assignment, $roommates);

            fputcsv($output, $row);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

        echo fgetcsv($output);
        exit;
    }
}
