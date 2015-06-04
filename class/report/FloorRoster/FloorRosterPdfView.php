<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

PHPWS_Core::initModClass('hms', 'ReportPdfView.php');
if (!defined('WKPDF_PATH')) {
    define('WKPDF_PATH', PHPWS_SOURCE_DIR . 'mod/hms/vendor/ioki/wkhtmltopdf-amd64-centos6/bin/');
}
if (!defined('USE_XVFB')) {
    define('USE_XVFB', false);
    define('XVFB_PATH', '');
}

class FloorRosterPdfView extends ReportPdfView
{

    public function __construct(Report $report)
    {
        parent::__construct($report);
        $this->pdf = new \WKPDF(WKPDF_PATH);
        if (USE_XVFB) {
            $this->pdf->setXVFB(XVFB_PATH);
        }

        $this->pdf->set_orientation('Landscape');
    }

    public function render()
    {
        $hall = null;
        $tpl = new PHPWS_Template('hms');
        $tpl->setFile('admin/reports/FloorRoster.tpl');

        $rows = & $this->report->rows;

        foreach ($rows as $hall_name => $hall) {

            foreach ($hall as $row) {
                $row['bedroom_label'] = strtoupper($row['bedroom_label']);

                $tpl->setCurrentBlock('room-rows');
                $tpl->setData($row);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('hall-rows');
            $tpl->setData(array('HALL' => $hall_name));
            $tpl->parseCurrentBlock();
        }

        $content = $tpl->get();
        $this->pdf->set_html($content);
        $this->pdf->render();
    }

    public function getPdfContent()
    {
        return $this->pdf->output(WKPDF::$PDF_ASSTRING, '');
    }

}


