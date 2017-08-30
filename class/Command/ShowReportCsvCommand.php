<?php

namespace Homestead\Command;

use \Homestead\ReportFactory;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

/**
 * ShowReportCsvCommand
 *
 * Command to show (download) a report CSV output
 * to the user's browser.
 *
 * @author jbooker
 * @package HMS
 */
class ShowReportCsvCommand extends Command{

    private $reportId; // ID of the report to show

    /**
     * Sets the report ID to show
     *
     * @param int $id
     */
    public function setReportId($id){
        $this->reportId = $id;
    }

    /**
     * Sets the request variables
     *
     * @throws InvalidArgumentException
     * @return Array array of request variables
     */
    public function getRequestVars()
    {
        if(!isset($this->reportId) || is_null($this->reportId)){
            throw new \InvalidArgumentException('Missing report id.');
        }

        return array('action'=>'ShowReportCsv', 'reportId'=>$this->reportId);
    }

    /**
     * Exec
     *
     * @param CommandContext $context
     * @throws InvalidArgumentException
     */
    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'reports')){
            throw new PermissionException('You do no have permission to run reports.');
        }

        $reportId = $context->get('reportId');

        if(!isset($reportId) || is_null($reportId)){
            throw new \InvalidArgumentException('Missing report id.');
        }

        // Instantiate the report controller with the requested report id
        $report = ReportFactory::getReportById($reportId);

        // Check to make sure the file exists
        if(!file_exists($report->getCsvOutputFilename())){
            \NQ::simple('hms', NotificationView::ERROR, 'Could not open report file.');
            \PHPWS_Error::log('Could not open report file ' . $report->getCsvOutputFilename(), 'hms');
            $reportCmd = CommandFactory::getCommand('ShowReportDetail');
            $reportCmd->setReportClass($report->getClass());
            $reportCmd->redirect();
        }

        $pdf = file_get_contents($report->getCsvOutputFilename());

        // Hoepfully force the browser to open a 'save as' dialogue
        header('Content-Type: text/csv');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Length: '.strlen($pdf));
        header('Content-Disposition: attachment; filename="'.basename($report->getCsvOutputFilename()).'";');

        echo $pdf;

        exit();
    }
}
