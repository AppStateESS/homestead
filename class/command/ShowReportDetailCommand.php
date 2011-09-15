<?php

/**
 * ShowReportDetailCommand
 *
 * Shows the report detail interface for a particular report type.
 *
 * @author jbooker
 * @package HMS
 */

class ShowReportDetailCommand extends Command {

    private $reportClass; // The class of the report to show

    /**
     * Sets the class of the report to show details for.
     *
     * @param String $class
     */
    public function setReportClass($class)
    {
        $this->reportClass = $class;
    }

    /**
     * Returns the array of request vars for this command.
     *
     * @throws InvalidArgumentException
     * @return Array Array of request vars.
     */
    public function getRequestVars()
    {
        if(!isset($this->reportClass) || is_null($this->reportClass)){
            throw new InvalidArgumentException('Missing report class.');
        }

        return array('action'=>'ShowReportDetail', 'reportClass'=>$this->reportClass);
    }

    /**
     * Executes, shows the details for the requested report class.
     *
     * @param CommandContext $context
     * @throws InvalidArgumentException
     */
    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do no have permission to run reports.');
        }

        $class = $context->get('reportClass');

        if(!isset($class) || is_null($class)){
            throw new InvalidArgumentException('Missing report class.');
        }

        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        PHPWS_Core::initModClass('hms', 'ReportDetailView.php');

        $reportCtl = ReportFactory::getControllerInstance($class);
        $view = new ReportDetailView($reportCtl);

        $context->setContent($view->show());
    }
}

?>