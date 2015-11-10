<?php

PHPWS_Core::initCoreClass('DBPager.php');
PHPWS_Core::initModClass('hms', 'Report.php');
PHPWS_Core::initModClass('hms', 'GenericReport.php');

/**
 * ReportHistoryPager
 *
 * A DBPager class that shows the previous completed
 * executions (history) of a report.
 *
 * @author jbooker
 * @package HMS
 */
class ReportHistoryPager extends DBPager {

    private $reportCtrl;

    public function __construct(ReportController $reportCtrl)
    {
        parent::__construct('hms_report', 'GenericReport');

        $this->reportCtrl = $reportCtrl;

        $this->addWhere('report', $this->reportCtrl->getReportClassName());
        $this->addWhere('completed_timestamp', null, 'IS NOT');

        $this->setOrder('completed_timestamp', 'DESC', true);

        $this->setModule('hms');
        $this->setTemplate('admin/reports/reportHistoryPager.tpl');
        $this->setLink('index.php?module=hms');
        $this->setEmptyMessage('No previous reports found.');

        $this->addRowTags('historyPagerRowTags');

        // Increase this limit because, by default, the DBPager limit is too small
        $this->default_limit = 1000;
        $this->limitList = array(1000);
    }
}
