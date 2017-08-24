<?php

namespace Homestead;
\PHPWS_Core::initCoreClass('DBPager.php');

/**
* ReportSchedulePager
*
* A DBPager class that shows the pending/scheduled
* executions of a report.
*
* @author jbooker
* @package HMS
*/
class ReportSchedulePager extends DBPager {

    private $reportCtrl;

    public function __construct(ReportController $reportCtrl)
    {
        parent::__construct('hms_report', 'GenericReport');

        $this->reportCtrl = $reportCtrl;

        $this->addWhere('report', $this->reportCtrl->getReportClassName());
        $this->addWhere('completed_timestamp', null, 'IS');

        $this->setOrder('scheduled_exec_time', 'ASC', true);

        $this->setModule('hms');
        $this->setTemplate('admin/reports/reportSchedulePager.tpl');
        $this->setLink('index.php?module=hms');
        $this->setEmptyMessage('No scheduled reports found.');

        $this->addRowTags('schedulePagerRowTags');
    }
}
