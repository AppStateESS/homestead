<?php

PHPWS_Core::initCoreClass('DBPager.php');
PHPWS_Core::initModClass('hms', 'Report.php');

class ReportHistoryPager extends DBPager {
    
    private $reportCtrl;
    
    public function __construct(ReportController $reportCtrl)
    {
        parent::__construct('hms_report', 'Report');
        
        $this->reportCtrl = $reportCtrl;
        
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