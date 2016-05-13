<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'PdoFactory.php');

/**
 * Handles retrieving the Rlc Applicants for a given set of filters.
 * @package hms
 * @author Chris Detsch
 *
 */

class AjaxGetRLCApplicationCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetRLCApplication');
    }

    public function execute(CommandContext $context)
    {
        $applicationId  = $context->get('applicationId');
        $term               = Term::getSelectedTerm();

        $application = HMS_RLC_Application::getApplicationById($applicationId, $term);


        $output = array();

        $student = StudentFactory::getStudentByUsername($application->getUsername(), $term);

        $firstChoice  = RlcFactory::getRlcById($application->getFirstChoice());
        if($application->getSecondChoice() != null)
        {
            $secondChoice = RlcFactory::getRlcById($application->getSecondChoice());
            if($application->getThirdChoice() != null)
            {
                $thirdChoice  = RlcFactory::getRlcById($application->getThirdChoice());
            }
        }

        $output['name']                 = $student->getName();
        $node['app_date']               = date('d-M-y', $application->getDateSubmitted());
        $output['specificCommQuestion'] = $application->getWhySpecificCommunities();
        $output['strenthsWeaknesses']   = $application->getStrengthsWeaknesses();
        $output['firstChoice']          = $firstChoice->getName();
        $output['firstChoiceAnswer']    = $application->getRLCQuestion0();
        if($application->getSecondChoice() != null)
        {
            $output['secondChoice']       = $secondChoice->getName();
            $output['secondChoiceAnswer'] = $application->getRLCQuestion1();
            if($application->getThirdChoice() != null)
            {
                $output['thirdChoice']       = $thirdChoice->getName();
                $output['thirdChoiceAnswer'] = $application->getRLCQuestion2();
            }
        }

        echo json_encode($output);
        exit;
    }
}
