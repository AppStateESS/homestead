<?php


class StartCheckoutSubmitCommand extends Command {

    public function getRequestVars()
    {
        return array (
                'action' => 'StartCheckoutSubmit'
        );
    }

    public function execute(CommandContext $context)
    {
        // Check permissions
        if (!Current_User::allow('hms', 'checkin')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to checkin students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $term = Term::getCurrentTerm();

        $bannerId = $context->get('banner_id');
        $hallId = $context->get('residence_hall_hidden');

        $errorCmd = CommandFactory::getCommand('ShowCheckoutStart'); // TODO

        if (!isset($bannerId) || is_null($bannerId) || $bannerId == '') {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing Banner ID.');
            $errorCmd->redirect();
        }

        if (!isset($hallId)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing residence hall ID.');
            $errorCmd->redirect();
        }

        // Everything checks out, so redirect to the form
        $cmd = CommandFactory::getCommand('ShowCheckoutForm'); // TODO
        $cmd->setBannerId($bannerId);
        $cmd->setHallId($hallId);
        $cmd->redirect();
    }
}


