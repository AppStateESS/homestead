<?php

namespace Homestead\Report\FloorRoster;

use \Homestead\ReportPdfView;
use \Homestead\Report;
use Dompdf\Dompdf;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */



class FloorRosterPdfView extends ReportPdfView
{

    public function __construct(Report $report)
    {
        parent::__construct($report);
        $this->pdf = new Dompdf();
        $this->pdf->setPaper('A4', 'landscape');
    }

    public function render()
    {
        $hall = null;
        $tpl = new \PHPWS_Template('hms');
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
        $this->pdf->loadHtml($content);
        $this->pdf->render();
    }

    public function getPdfContent()
    {
        return $this->pdf->stream();
    }

}
