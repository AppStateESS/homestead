<?php

class ReApplicationWaitingListFormSubmitCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'ReApplicationWaitingListFormSubmit', 'term' => $this->term);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        
        $term = $context->get('term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
        $errorCmd->setTerm($term);

        $depositAgreed = $context->get('deposit_check');

        if(is_null($depositAgreed)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must check the box indicating you understand the License Contract deposit fees.');
            $errorCmd->redirect();
        }

        // Data sanity checking
        $doNotCall  = $context->get('do_not_call');
        $areaCode   = $context->get('area_code');
        $exchange   = $context->get('exchange');
        $number     = $context->get('number');

        if(is_null($doNotCall)){
            // do not call checkbox was not selected, so check the number
            if(empty($areaCode) || empty($exchange) || empty($number)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
                $errorCmd->redirect();
            }
        }
        
        $mealOption         = $context->get('meal_option');

        $specialNeed = $context->get('special_need');

        if(isset($specialNeed)){
            $onSubmitCmd = CommandFactory::getCommand('OffCampusWaitingListFormSave');
            $onSubmitCmd->loadContext($context);
            $onSubmitCmd->setTerm($term);

            $specialNeedCmd = CommandFactory::getCommand('ShowSpecialNeedsForm');
            $specialNeedCmd->setTerm($term);
            $specialNeedCmd->setVars($context->getParams());
            $specialNeedCmd->setOnSubmitCmd($onSubmitCmd);
            $specialNeedCmd->redirect();
        }else{
            //TODO
            $reviewCmd = CommandFactory::getCommand('OffCampusWaitingListFormSave');
            $reviewCmd->setTerm($term);
            $reviewCmd->loadContext($context);
            $reviewCmd->redirect();
        }
    }
}

?>