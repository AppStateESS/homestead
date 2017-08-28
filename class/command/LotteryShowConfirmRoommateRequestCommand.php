<?php

namespace Homestead\command;

use \Homestead\Command;

class LotteryShowConfirmRoommateRequestCommand extends Command {

    private $requestId;
    private $mealPlan;

    // TODO: check for this function being used elsewhere and rename it
    public function setRoommateRequestId($id){
        $this->requestId = $id;
    }

    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryShowConfirmRoommateRequest');
        $vars['roommateRequestId'] = $this->requestId;
        $vars['mealPlan']   = $this->mealPlan;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'ContractFactory.php');

        $term = \PHPWS_Settings::get('hms', 'lottery_term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $contract = ContractFactory::getContractByStudentTerm($student, $term);

        if($contract !== false){
            $contract->updateEnvelope();
        }

        if($contract === false || $contract->getEnvelopeStatus() !== 'completed'){
            // If they haven't agreed, redirect to the agreement
            $event = $context->get('event');
            if(is_null($event) || !isset($event) || ($event != 'signing_complete' && $event != 'viewing_complete'))
            {
                $returnCmd = CommandFactory::getCommand('LotteryShowConfirmRoommateRequest');
                $returnCmd->setRoommateRequestId($context->get('roommateRequestId'));
                $returnCmd->setMealPlan($context->get('meal_plan'));
                //var_dump($returnCmd->getURI());exit;

                $agreementCmd = CommandFactory::getCommand('ShowTermsAgreement');
                $agreementCmd->setTerm($term);
                $agreementCmd->setAgreedCommand($returnCmd);
                $agreementCmd->redirect();
            }else if($event === 'signing_complete'){
                HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CONTRACT_STUDENT_SIGN_EMBEDDED, UserStatus::getUsername(), "Student signed contract for $term through the embedded signing process");
            }
        }


        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryConfirmRoommateRequestView.php');

        $request = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('roommateRequestId'));
        $mealPlan = $context->get('meal_plan');

        $view = new LotteryConfirmRoommateRequestView($request, $term, $mealPlan);
        $context->setContent($view->show());
    }

    public function setTerm($term)
    {
        // Dummy method so that ShowTermsAgreementCommand can redirect here. This is a hack.
    }
}
