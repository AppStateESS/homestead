<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initCoreClass('DBPager.php');

class ActivityLogPager extends hms\View {

    private $actee;
    private $actor;
    private $notes;
    private $exact;
    private $begin;
    private $end;
    private $activities;
    private $static;
    private $limit;

    private $pager;

    public function __construct($actee = NULL, $actor = NULL, $notes = NULL, $exact = false, $begin = NULL, $end = NULL, Array $activites = NULL, $static = false, $limit = 10)
    {
        $this->actee		= $actee;
        $this->actor		= $actor;
        $this->notes		= $notes;
        $this->exact		= $exact;
        $this->begin		= $begin;
        $this->end			= $end;
        $this->activities	= $activites;
        $this->static		= $static;
        $this->limit		= $limit;

        $this->pager = new DBPager('hms_activity_log','HMS_Activity_Log');
    }


    /**
     * Shows the DBPager for the Activity Log, along with options for limiting what
     * is shown.  If no limits are provided, the log will not be very useful.
     *
     * The static variable lets you switch between a static view of the pager
     * (unsortable with unchangeable limits) that has a link to the main
     * activity log, or the regular dynamic log.
     */
    public function show()
    {
        $pct = ($this->exact == TRUE) ? '' : '%';

        if(!empty($this->actor) && !empty($this->actee) && $this->actor == $this->actee){
            // Both actor and actee were specified, and they match so use an 'OR'
            // to effectively show all entries for the username specified
            $this->pager->db->addWhere('actor', "$pct$this->actor$pct", 'ILIKE', 'OR', 'actor_actee_group');
            $this->pager->db->addWhere('user_id', "$pct$this->actee$pct", 'ILIKE', 'OR', 'actor_actee_group');
            $this->pager->db->setGroupConj('actor_actee_group', 'AND');
        }else if(!empty($this->actor) && !empty($this->actee)){
            // Both actor and actee were specified, but they don't match so use an 'AND'
            // to get just the specific situation we're looking for
            $this->pager->db->addWhere('actor', "$pct$this->actor$pct", 'ILIKE', 'AND', 'actor_actee_group');
            $this->pager->db->addWhere('user_id', "$pct$this->actee$pct", 'ILIKE', 'AND', 'actor_actee_group');
            $this->pager->db->setGroupConj('actor_actee_group', 'AND');
        }else if(!empty($this->actor)){
            $this->pager->db->addWhere('actor', "$pct$this->actor$pct", 'ILIKE');
        }else if(!empty($this->actee)){
            $this->pager->db->addWhere('user_id', "$pct$this->actee$pct", 'ILIKE');
        }

        if(!empty($this->notes))
        $this->pager->db->addWhere('notes', "%$this->notes%", 'ILIKE');

        if($this->begin != $this->end && $this->begin < $this->end) {
            if(!is_null($this->begin))
            $this->pager->db->addWhere('timestamp', $this->begin, '>');

            if(!is_null($this->end))
            $this->pager->db->addWhere('timestamp', $this->end, '<');
        }

        if(!is_null($this->activities) && !empty($this->activities))
        $this->pager->db->addWhere('activity', $this->activities, 'IN');

        $this->pager->setModule('hms');
        $this->pager->setLink('index.php?module=hms');
        $this->pager->setEmptyMessage('No log entries found under the limits provided.');
        $this->pager->addToggle('class="toggle1"');
        $this->pager->addToggle('class="toggle2"');
        $this->pager->addRowTags('getPagerTags');
        $this->pager->setOrder('timestamp', 'desc', TRUE);
        $this->pager->setDefaultLimit($this->limit);

        if($this->static){
            $this->pager->setTemplate('admin/static_activity_log_pager.tpl');
        } else {
            $this->pager->setTemplate('admin/activity_log_pager.tpl');
        }

        return $this->pager->get();
    }
}
