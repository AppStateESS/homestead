<?php


class ShowCheckoutStartCommand extends Command {

    public function getRequestVars()
    {
        return array (
                'action' => 'ShowCheckoutStart'
        );
    }

    public function execute(CommandContext $context)
    {
        // Check permissions
        if (!Current_User::allow('hms', 'checkin')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to checkin students.');
        }

        $term = Term::getCurrentTerm();

        // Check role-based permissions for list of hall or all halls
        // TODO (for now just listing all halls)

        PHPWS_Core::initModClass('hms', 'ResidenceHallFactory.php');
        $halls = ResidenceHallFactory::getHallNamesAssoc($term);

        if (!isset($halls) || count($halls) < 1) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'No residence halls are setup for this term, so the check-in cannot be accessed.');
            $context->goBack();
        }

        PHPWS_Core::initModClass('hms', 'CheckOutStartView.php');
        $view = new CheckoutStartView($halls, $term);

        $context->setContent($view->show());
    }
}
?>