<?php

namespace Homestead\command;

use \Homestead\Command;

class LotteryDenyRoommateRequestCommand extends Command {

    private $requestId;

    public function setRequestId($id){
        $this->requestId = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'LotteryDenyRoommateRequest', 'requestId'=>$this->requestId);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');

        $requestId = $context->get('requestId');

        $errorCmd = CommandFactory::getCommand('LotteryShowDenyRoommateRequest');
        $errorCmd->setRequestId($requestId);

        # Confirm the captcha
        \PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE);
        if($captcha === FALSE){
            \NQ::simple('hms', NotificationView::ERROR, 'The words you entered were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        # Get the roommate request
        $request = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));

        # Make sure that the logged in user is the same as the confirming the request
        if(UserStatus::getUsername() != $request['asu_username']){
            \NQ::simple('hms', NotificationView::ERROR, 'Invalid roommate request. You can not confirm that roommate request.');
            $errorCmd->redirect();
        }

        # Deny the roommate requst
        try{
            HMS_Lottery::denyRoommateRequest($requestId);
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error denying the roommate request. Please contact University Housing.');
            $errorCmd->redirect();
        }

        # Log that it happened
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_ROOMMATE_DENIED, UserStatus::getUsername(), 'Captcha words: ' . $captcha);

        # Success
        \NQ::simple('hms', NotificationView::SUCCESS, 'The roommate request was successfully declined.');
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }

}
