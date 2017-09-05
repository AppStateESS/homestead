<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\Term;
use \Homestead\HMS_Bed;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\HousingApplicationFactory;
use \Homestead\AssignStudentView;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\StudentNotFoundException;

/**
 * Controller for showing the Assign Student view.
 *
 * @author jbooker
 * @package hms
 */
class ShowAssignStudentCommand extends Command {

    private $username;
    private $bedId;

    /**
     * Sets the default username to pre-populate the assignment interface with.
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Sets the bed ID to pre-populate the assignment interface with.
     * @param unknown $id
     */
    public function setBedId($id)
    {
        $this->bedId = $id;
    }

    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        $vars = array();

        $vars['action'] = 'ShowAssignStudent';

        if (isset($this->username)) {
            $vars['username'] = $this->username;
        }

        if (isset($this->bedId)) {
            $vars['bedId'] = $this->bedId;
        }

        return $vars;
    }

    /**
     * (non-PHPdoc)
     * @see Command::execute()
     */
    public function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !\Current_User::allow('hms', 'assignment_maintenance')) {
            throw new PermissionException('You do not have permission to assign students.');
        }

        $username = $context->get('username');
        $bedId = $context->get('bedId');

        $term = Term::getSelectedTerm();

        if (isset($bedId) && !is_null($bedId) && !empty($bedId)) {
            $bed = new HMS_Bed($bedId);
        } else {
            $bed = null;
        }

        if (isset($username)) {
            try {
                $student = StudentFactory::getStudentByUsername($context->get('username'), $term);
            } catch (\InvalidArgumentException $e) {
                \NQ::simple('hms', NotificationView::ERROR, $e->getMessage());
                $cmd = CommandFactory::getCommand('ShowAssignStudent');
                $cmd->redirect();
            } catch (StudentNotFoundException $e) {
                \NQ::simple('hms', NotificationView::ERROR, $e->getMessage());
                $cmd = CommandFactory::getCommand('ShowAssignStudent');
                $cmd->redirect();
            }

            $application = HousingApplicationFactory::getAppByStudent($student, $term);
        } else {
            $student     = null;
            $application = null;
        }

        $assignView = new AssignStudentView($student, $bed, $application);

        $context->setContent($assignView->show());
    }
}
