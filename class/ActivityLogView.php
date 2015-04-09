<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'ActivityLogPager.php');

class ActivityLogView extends Homestead\View{

    private $actee;
    private $actor;
    private $notes;
    private $exact;
    private $begin;
    private $end;
    private $activities;

    private $pager;
    private $static;
    private $limit;

    public function __construct($actee = NULL, $actor = NULL, $notes = NULL, $exact = false, $begin = NULL, $end = NULL, Array $activities = NULL, $static = false, $limit = 10)
    {
        $this->actee		= $actee;
        $this->actor		= $actor;
        $this->notes		= $notes;
        $this->exact		= $exact;
        $this->begin		= $begin;
        $this->end			= $end;
        $this->activities	= $activities;
        $this->static		= $static;
        $this->limit		= $limit;

        $this->pager = new ActivityLogPager($actee, $actor, $notes, $exact, strtotime($begin), strtotime($end), $activities, $static, $limit);
    }

    public function show()
    {
        $tags = array();

        $tags['CONTENT'] = $this->pager->show();
        $tags['FILTERS'] = ActivityLogView::showFilters($_REQUEST);

        Layout::addPageTitle("Activity Log");

        javascript('jquery_ui');
        javascript('modules/hms/note', array('LINK'=>'activity-log-note'));

        return PHPWS_Template::Process($tags, 'hms', 'admin/activity_log_box.tpl');
    }

    /**
     * Shows filtering options for the log view.  The first argument is usually
     * $_SESSION. The second argument is laid out in the same way, and
     * specifies default values.  If a default value is specified in the second
     * argument, that option will not appear in the filter; this way, if you're
     * in the Student Info thing, you can show the activity log for only that
     * user.
     */
    public static function showFilters($selection = NULL, $defaults = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');

        $submitCmd = CommandFactory::getCommand('ShowActivityLog');

        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        $form->setMethod('get');

        $form->addText('actor');
        $form->setLabel('actor', 'Action Performed By:');
        if(isset($selection['actor']))
        $form->setValue('actor', $selection['actor']);

        $form->addText('actee');
        $form->setLabel('actee', 'Action Affected:');
        if(isset($selection['actee']))
        $form->setValue('actee', $selection['actee']);

        // "exact" flag
        $form->addCheck('exact','yes');
        $form->setMatch('exact','yes');
        $form->setLabel('exact','Exact? ');

        $form->addText('begin', isset($selection['begin']) ? $selection['begin'] : '');
        $form->setClass('begin', 'datepicker');

        $form->addText('end', isset($selection['end']) ? $selection['end'] : '');
        $form->setClass('end', 'datepicker');

        $form->addText('notes');
        $form->setLabel('notes', 'Note:');
        if(isset($selection['notes']))
        $form->setValue('notes', $selection['notes']);

        $activities = HMS_Activity_Log::getActivityMapping();
        foreach($activities as $id => $text) {
            $name = "a$id";
            $form->addCheckbox($name);
            $form->setLabel($name, $text);
            $form->setMatch($name, isset($selection[$name]));
        }

        $form->addSubmit('Refresh');

        $tpl = $form->getTemplate();
        $tpl['BEGIN_LABEL'] = 'After:';
        $tpl['END_LABEL'] = 'Before:';

        javascript('jquery');
        javascript('modules/hms/activity_log');

        return PHPWS_Template::process($tpl, 'hms', 'admin/activity_log_filters.tpl');
    }
}

//?>
