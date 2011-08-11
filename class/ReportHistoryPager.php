<?php

PHPWS_Core::initCoreClass('DBPager.php');
PHPWS_Core::initModClass('hms', 'Report.php');
PHPWS_Core::initModClass('hms', 'GenericReport.php');

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
        
        $this->addToggle('class="bgcolor1"');
        $this->addToggle('class="bgcolor2"');
        $this->addRowTags('historyPagerRowTags');
    }
}


?>