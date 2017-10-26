<?php

namespace Homestead\Report\RlcRoster;

use \Homestead\ReportHtmlView;
use \Homestead\Term;

class RlcRosterHtmlView extends ReportHtmlView {

  protected function render()
  {
      parent::render();
      $this->tpl['TERM'] = Term::toString($this->report->getTerm());

      $this->tpl['MEMBER_COUNT'] = $this->report->getMemberCount();

      foreach ($this->report->getData() as $row)
      {
          if(empty($row['COMMUNITY']))
          {
            $row['COMMUNITY'] = '<em class="text-muted">No Membership</em>';
          }
          $this->tpl['rows'][] = $row;
      }

      return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/RlcRoster.tpl');

  }

}
