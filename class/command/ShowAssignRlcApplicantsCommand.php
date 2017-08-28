<?php

namespace Homestead\command;

use \Homestead\Command;

PHPWS_Core::initModClass('hms', 'RlcAssignmentView.php');

/**
 * Command/controller for showing the view where an admin can assign students to RLCs.
 *
 * @author Jeremy Booker
 * @package HMS
*/
class ShowAssignRlcApplicantsCommand extends Command {

    /**
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        $vars = array('action'=>'ShowAssignRlcApplicants');

        return $vars;
    }

    /**
     * @see Command::execute()
     */
    public function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !\Current_User::allow('hms', 'view_rlc_applications')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC applications.');
        }

        $community = null;

        $id = $context->get('rlc');

        // If an id was passed in, then try to load that community
        if (isset($id) && $id != 0) {
            PHPWS_Core::initModClass('hms', 'RlcFactory.php');
            $community = RlcFactory::getRlcById($id);
        }

        $studentType = $context->get('student_type');
        if (isset($studentType) && $studentType == '0'){
            $studentType = null;
        }

        $term = Term::getSelectedTerm();

        $view = new RlcAssignmentView($term, $community, $studentType);
        $context->setContent($view->show());
    }
}
