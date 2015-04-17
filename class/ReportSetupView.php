<?php

/**
 * ReportSetupView
 *
 * Parent class for report setup views. This class is
 * intended to be extended (one or more times) for each report.
 *
 * @author jbooker
 */
class ReportSetupView extends hms\View {

    protected $report;
    protected $datePicker;
    protected $tpl;
    protected $form;

    protected $linkText;
    protected $dialogId;
    protected $useDatePicker;
    protected $runNow;
    protected $formId;

    public function __construct(Report $report){
        $this->report = $report;
        //$this->form = new PHPWS_Form('report-setup-form');
        $this->tpl = array();
        $this->useDatePicker = false;
        $this->runNow = false;
    }

    public function show()
    {
        $params = array();
        $params['REPORT_NAME'] = $this->report->getFriendlyName();
        $params['DIALOG_ID'] = $this->dialogId;
        $params['LINK_ID'] = $this->dialogId . "-link";
        $params['REPORT_CLASS'] = $this->report->getClass();
        $params['RUN_NOW'] = ($this->runNow === true) ? 'true' : 'false';
        $params['FORM_ID'] = $this->formId;

        $js = javascript('modules/hms/reportSetupDialog', $params);

        $this->form = new PHPWS_Form($this->formId);

        $this->tpl['LINK_TEXT'] = $this->linkText;
        $this->tpl['LINK_ID'] = $this->dialogId . "-link";
        $this->tpl['DIALOG_ID'] = $this->dialogId;

        $this->tpl['DIALOG_CONTENTS'] = $this->getDialogContents();

        $this->form->addDropBox('term', Term::getTermsAssoc());

        if($this->useDatePicker){
            $this->form->addText('datePicker');
            $this->form->addText('timePicker');
        }

        $this->form->mergeTemplate($this->tpl);
        $this->tpl = $this->form->getTemplate();

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/SetupDialog.tpl') . $js;
    }

    protected function getDialogContents()
    {
        return "";
    }
    
    public function setLinkText($text)
    {
        $this->linkText = $text;
    }

    public function setDialogId($id)
    {
        $this->dialogId = $id;
    }

    public function useDatePicker($datePicker){
        $this->useDatePicker = $datePicker;
    }
    
    public function setRunNow($run){
        $this->runNow = $run;
    }
    
    public function setFormId($id){
        $this->formId = $id;
    }
}
?>