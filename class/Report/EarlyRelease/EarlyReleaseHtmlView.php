<?php

namespace Homestead\Report\EarlyRelease;

  class EarlyReleaseHtmlView extends ReportHtmlView
  {
    protected function render()
    {
      parent::render();

      $this->tpl['TERM'] = Term::toString($this->report->getTerm());

      if($this->report->getTotal())
      {
        $this->tpl['TOTAL'] = $this->report->getTotal();
      }
      else
      {
        $this->tpl['EMPTY_MESSAGE'] = 'No students requesting early release.';
      }

      if($this->report->getTransfersTotal())
      {
        $this->tpl['TRANSFERS_TOTAL'] = $this->report->getTransfersTotal();
      }

      if($this->report->getGradTotal())
      {
        $this->tpl['GRAD_TOTAL'] = $this->report->getGradTotal();
      }

      if($this->report->getTeachingTotal())
      {
        $this->tpl['TEACHING_TOTAL'] = $this->report->getTeachingTotal();
      }

      if($this->report->getInternTotal())
      {
        $this->tpl['INTERN_TOTAL'] = $this->report->getInternTotal();
      }

      if($this->report->getWithdrawTotal())
      {
        $this->tpl['WITHDRAW_TOTAL'] = $this->report->getWithdrawTotal();
      }

      if($this->report->getMarriageTotal())
      {
        $this->tpl['MARRIAGE_TOTAL'] = $this->report->getMarriageTotal();
      }

      if($this->report->getAbroadTotal())
      {
        $this->tpl['ABROAD_TOTAL'] = $this->report->getAbroadTotal();
      }

      if($this->report->getInternationalTotal())
      {
        $this->tpl['INTL_TOTAL'] = $this->report->getInternationalTotal();
      }

      foreach($this->report->getData() as $row)
      {
            $this->tpl['rows'][] = $row;
      }

      return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/EarlyRelease.tpl');
    }
  }
