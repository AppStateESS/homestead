<?php

namespace Homestead\Command;

 

/**
 * ShowReportPdfCommand
 *
 * Command to show (download) a report's PDf output
 * to the user's browser.
 *
 * @author jbooker
 * @package HMS
 */
class ShowReportPdfCommand extends Command{

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
     * @throws InvalidArgumentExection
     * @return Array array of request variables
     */
    public function getRequestVars()
    {
        if(!isset($this->reportId) || is_null($this->reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }

        return array('action'=>'ShowReportPdf', 'reportId'=>$this->reportId);
    }

    /**
     * Exec
     *
     * @param CommandContext $context
     * @throws InvalidArgumentExection
     */
    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do no have permission to run reports.');
        }

        $reportId = $context->get('reportId');

        // Check to make sure a report ID was given
        if(!isset($reportId) || is_null($reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }

        // Instantiate the report controller with the requested report id
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        $report = ReportFactory::getReportById($reportId);

        // Check to make sure the file actually exsists
        if(!file_exists($report->getPdfOutputFilename())){
            \NQ::simple('hms', NotificationView::ERROR, 'Could not open report file.');
            \PHPWS_Error::log('Could not open report file ' . $report->getCsvOutputFilename(), 'hms');
            $reportCmd = CommandFactory::getCommand('ShowReportDetail');
            $reportCmd->setReportClass($report->getClass());
            $reportCmd->redirect();
        }

        $pdf = file_get_contents($report->getPdfOutputFilename());

        // Headers to show the PDf. Hopefully opens an embedded PDF viewer.
        header('Content-Type: application/pdf');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Length: '.strlen($pdf));
        header('Content-Disposition: inline; filename="'.basename($report->getPdfOutputFilename()).'";');

        echo $pdf;

        exit();
    }
}
