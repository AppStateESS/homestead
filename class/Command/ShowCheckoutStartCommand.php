<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\ResidenceHallFactory;
use \Homestead\CheckoutStartView;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

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
        if (!\Current_User::allow('hms', 'checkin')) {
            throw new PermissionException('You do not have permission to checkin students.');
        }

        $term = Term::getSelectedTerm();

        // Check role-based permissions for list of hall or all halls
        // TODO (for now just listing all halls)

        $halls = ResidenceHallFactory::getHallNamesAssoc($term);

        if (!isset($halls) || count($halls) < 1) {
            \NQ::simple('hms', NotificationView::ERROR, 'No residence halls are setup for this term, so the check-in cannot be accessed.');
            $context->goBack();
        }

        $view = new CheckoutStartView($halls, $term);

        $context->setContent($view->show());
    }
}
