<?php

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
    function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Sets the bed ID to pre-populate the assignment interface with.
     * @param unknown $id
     */
    function setBedId($id)
    {
        $this->bedId = $id;
    }

    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    function getRequestVars()
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
    function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign students.');
        }


        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'AssignStudentView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

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
            } catch (InvalidArgumentException $e) {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
                $cmd = CommandFactory::getCommand('ShowAssignStudent');
                $cmd->redirect();
            } catch (StudentNotFoundException $e) {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
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

?>
