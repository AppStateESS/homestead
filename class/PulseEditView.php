<?php

namespace Homestead;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PulseEditView extends View
{

    public function show()
    {

        $autoassign = $this->AutoassignScheduleSet();

        $vars = array();

        if ($autoassign) {
            $vars['autoassign'] = 'Auto Assign schedule set for ' . $autoassign->getExecuteAfter('%Y/%m/%d %l:%M %P');
            $vars['autoassign_remove'] = 1;
        } else {
            $vars['autoassign'] = 'Auto Assign has not been created.';
            $vars['autoassign_create'] = 1;
        }

        $reportrunner = $this->ReportRunnerScheduleSet();
        if ($reportrunner) {
            $vars['reportrunner'] = 'Report Runner schedule set for ' . $reportrunner->getExecuteAfter('%Y/%m/%d %l:%M %P');
            $vars['reportrunner_remove'] = 1;
        } else {
            $vars['reportrunner'] = 'Report Runner has not been created.';
            $vars['reportrunner_create'] = 1;
        }

        $withdrawn = $this->WithdrawnSearchEmailScheduleSet();
        if ($withdrawn) {
            $vars['withdrawn'] = 'Withdrawn schedule set for ' . $withdrawn->getExecuteAfter('%Y/%m/%d %l:%M %P');
            $vars['withdrawn_remove'] = 1;
        } else {
            $vars['withdrawn'] = 'Withdrawn schedule has not been created.';
            $vars['withdrawn_create'] = 1;
        }

        $nightly_cache = $this->NightlyCacheScheduleSet();
        if ($nightly_cache) {
            $vars['nightly_cache'] = 'Nightly cache schedule set for ' . $nightly_cache->getExecuteAfter('%Y/%m/%d %l:%M %P');
            $vars['nightly_cache_remove'] = 1;
        } else {
            $vars['nightly_cache'] = 'Nightly cache has not been created.';
            $vars['nightly_cache_create'] = 1;
        }

        $tpl = new \Template($vars);
        $tpl->setModuleTemplate('hms', 'admin/pulse/settings.html');
        return $tpl->get();
    }

    /**
     * Returns null if not found.
     * @return \PulseSchedule
     */
    private function AutoassignScheduleSet()
    {
        $result = \pulse\PulseFactory::getByName('Autoassign', 'hms');
        return $result;
    }

    /**
     * Returns null if not found.
     * @return \PulseSchedule
     */
    private function ReportRunnerScheduleSet()
    {
        $result = \pulse\PulseFactory::getByName('ReportRunner', 'hms');
        return $result;
    }

    /**
     * Returns null if not found.
     * @return \PulseSchedule
     */
    private function WithdrawnSearchEmailScheduleSet()
    {
        $result = \pulse\PulseFactory::getByName('WithdrawlSearch', 'hms');
        return $result;
    }

    /**
     * Returns null if not found.
     * @return \PulseSchedule
     */
    private function NightlyCacheScheduleSet()
    {
        $result = \pulse\PulseFactory::getByName('NightlyCache', 'hms');
        return $result;
    }

}
