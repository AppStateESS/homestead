<?php

/**
 * ExecReportSycCommand
 *
 * Command class responsible for starting the
 * synchronous execution of the given report.
 *
 * Can be extended or replaced (using iSyncReport interface)
 *
 * @see iSyncReport
 * @author jbooker
 * @package HMS
 */
class ExecReportSyncCommand extends Command {

    private $reportClass;

    public function setReportClass($class){
        $this->reportClass = $class;
    }

    public function getRequestVars()
    {
        if(!isset($this->reportClass) || is_null($this->reportClass)){
            throw new InvalidArgumentException('Missing report class.');
        }

        return array('action'=>'ExecReportSync', 'reportClass'=>$this->reportClass);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do no have permission to run reports.');
        }

        PHPWS_Core::initModClass('hms', 'ReportFactory.php');

        // Determine which report we're running
        $reportClass = $context->get('reportClass');

        if(!isset($reportClass) || is_null($reportClass)){
            throw new InvalidArgumentException('Missing report class.');
        }

        // Get the proper report controller
        $reportCtrl = ReportFactory::getControllerInstance($reportClass);

        // Initalize a new report
        $reportCtrl->newReport(time());

        // Get the params from the context
        /*
        * The below is a bit of hack. The term should really be taken care of
        * by a setup view, and passed in as part of the context proper. We tack
        * it onto the context here, just to make sure it's available.
        */
        $params = $context->getParams();
        $params['term'] = Term::getSelectedTerm();
        //test(Term::getSelectedTerm());
        //test($params,1);
        $reportCtrl->setParams($params);

        // Save this report so it'll have an ID
        $reportCtrl->saveReport();

        // Generate the report
        $reportCtrl->generateReport();

        // Get the default view command
        $viewCmd = $reportCtrl->getDefaultOutputViewCmd();

        // Rediect to the view command
        $viewCmd->redirect();
    }
}

