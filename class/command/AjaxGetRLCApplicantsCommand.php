<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'PdoFactory.php');

/**
 * Handles retrieving the Rlc Applicants for a given set of filters.
 * @package hms
 * @author Chris Detsch
 *
 */

class AjaxGetRLCApplicantsCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetRLCApplicants');
    }

    public function execute(CommandContext $context)
    {
        $studentTypeFilter  = $context->get('studentTypeFilter');
        $communityFilter    = $context->get('communityFilter');
        $firstChoice        = filter_var($context->get('firstChoice'), FILTER_VALIDATE_BOOLEAN);
        $secondChoice       = filter_var($context->get('secondChoice'), FILTER_VALIDATE_BOOLEAN);
        $thirdChoice        = filter_var($context->get('thirdChoice'), FILTER_VALIDATE_BOOLEAN);
        $term               = Term::getSelectedTerm();

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT hms_learning_community_applications.id,
                         hms_learning_community_applications.username,
                         hms_learning_community_applications.date_submitted,
                         hms_learning_community_applications.rlc_first_choice_id,
                         hms_learning_community_applications.rlc_second_choice_id,
                         hms_learning_community_applications.rlc_third_choice_id
                  FROM hms_learning_community_applications
                  LEFT OUTER JOIN hms_learning_community_assignment
                    ON hms_learning_community_applications.id = hms_learning_community_assignment.application_id
                  WHERE hms_learning_community_assignment.application_id IS NULL
                        AND hms_learning_community_applications.term = :term
                        AND hms_learning_community_applications.denied = 0";

        $params = array('term' => $term);

        if($communityFilter != 0)
        {
            $query .= " AND (";

            if($firstChoice) // Default to first choice
            {
                $query .= "hms_learning_community_applications.rlc_first_choice_id = :rlc";
                if($secondChoice  || $thirdChoice)
                {
                    $query .= " OR ";
                }
            }
            if($secondChoice)
            {

                $query .= "hms_learning_community_applications.rlc_second_choice_id = :rlc";
                if($thirdChoice)
                {
                    $query .= " OR ";
                }
            }
            if($thirdChoice)
            {
                $query .= "hms_learning_community_applications.rlc_third_choice_id = :rlc";
            }

            if(!($firstChoice || $secondChoice || $thirdChoice))
            {
                $query .= "hms_learning_community_applications.rlc_first_choice_id = :rlc
                           OR hms_learning_community_applications.rlc_second_choice_id = :rlc
                           OR hms_learning_community_applications.rlc_third_choice_id = :rlc";
            }

            $query .= ")";

            $params['rlc'] = $communityFilter;
        }

        if($studentTypeFilter != '0')
        {
            if ($studentTypeFilter == TYPE_FRESHMEN) {
                $params['sType'] = "freshmen";
            } else if ($studentTypeFilter == TYPE_CONTINUING) {
                $params['sType'] = "returning";
            }

            $query .= " AND hms_learning_community_applications.application_type = :sType";
        }


        $stmt = $db->prepare($query);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = array();
        $communities = RlcFactory::getRlcList($term);

        foreach ($results as $applicant)
        {
            $node                   = array();
            $student                = StudentFactory::getStudentByUsername($applicant['username'], $term);
            $node['app_id']         = $applicant['id'];
            $node['first_name']     = $student->getFirstName();
            $node['last_name']      = $student->getLastName();
            $node['name']           = $student->getName();
            $node['bannerId']       = $student->getBannerId();
            $node['first_choice']   = $communities[$applicant['rlc_first_choice_id']];
            if(isset($applicant['rlc_second_choice_id']))
            {
                $node['second_choice']  = $communities[$applicant['rlc_second_choice_id']];
                if(isset($applicant['rlc_third_choice_id']))
                {
                    $node['third_choice']   = $communities[$applicant['rlc_third_choice_id']];
                }
            }
            $node['gender']         = $student->getGender() ? 'Male':'Female';
            $node['app_date']       = date('d-M-y', $applicant['date_submitted']);
            $node['unix_date']      = $applicant['date_submitted'];
            $output[]               = $node;
        }

        echo json_encode($output);
        exit;
    }
}
