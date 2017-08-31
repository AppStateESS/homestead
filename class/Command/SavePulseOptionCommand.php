<?php

namespace Homestead\Command;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class SavePulseOptionCommand extends Command
{

    public function getRequestVars()
    {
        $request = \Server::getCurrentRequest();
        return $request->getVars();
    }

    public function execute(CommandContext $context)
    {
        $request = \Server::getCurrentRequest();
        $vars = $request->getVars();
        extract($vars['vars']);
        if (!isset($schedule_type)) {
            throw new \Exception('Unknown schedule type requested');
        }

        if ($cmd == 'add') {
            switch ($schedule_type) {
                case 'autoassign':
                    $this->addAutoAssignSchedule();
                    break;

                case 'reportrunner':
                    $this->addReportRunnerSchedule();
                    break;

                case 'withdrawn':
                    $this->addWithdrawnSchedule();
                    break;

                case 'nightly_cache':
                    $this->addNightlyCacheSchedule();
                    break;

                default:
                    throw new \Exception('Unknown schedule type requested');
            }
        } elseif ($cmd == 'remove') {
            switch ($schedule_type) {
                case 'autoassign':
                    $this->dropAutoAssignSchedule();
                    break;

                case 'reportrunner':
                    $this->dropReportRunnerSchedule();
                    break;

                case 'withdrawn':
                    $this->dropWithdrawnSchedule();
                    break;

                case 'nightly_cache':
                    $this->dropNightlyCacheSchedule();
                    break;

                default:
                    throw new \Exception('Unknown schedule type requested');
            }
        } else {
            throw new \Exception('Unknown schedule type requested');
        }
        \PHPWS_Core::goBack();
    }

    public function dropAutoAssignSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('AutoAssign', 'hms');
        \pulse\PulseFactory::deleteResource($pulse);
    }

    public function addAutoAssignSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('AutoAssign', 'hms');
        if (!empty($pulse)) {
            throw new \Exception('Auto Assign schedule is already present.');
        }
        $ps = pulse\PulseFactory::build();
        $ps->setName('AutoAssign');
        $ps->setModule('hms');
        $ps->setClassName('AutoassignPulse');
        $ps->setClassMethod('execute');
        $ps->setInterim('1');
        $ps->setRequiredFile('mod/hms/class/AutoassignPulse.php');
        pulse\PulseFactory::save($ps);
    }

    public function dropReportRunnerSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('ReportRunner', 'hms');
        \pulse\PulseFactory::deleteResource($pulse);
    }

    public function addReportRunnerSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('ReportRunner', 'hms');
        if (!empty($pulse)) {
            throw new \Exception('Report Runner schedule is already present.');
        }
        $ps = pulse\PulseFactory::build();
        $ps->setName('ReportRunner');
        $ps->setModule('hms');
        $ps->setClassName('ReportRunner');
        $ps->setClassMethod('execute');
        $ps->setInterim('1');
        $ps->setRequiredFile('mod/hms/class/ReportRunner.php');
        pulse\PulseFactory::save($ps);
    }

    public function dropWithdrawnSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('Withdrawn', 'hms');
        \pulse\PulseFactory::deleteResource($pulse);
    }

    public function addWithdrawnSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('Withdrawn', 'hms');
        if (!empty($pulse)) {
            throw new \Exception('Withdrawn schedule is already present.');
        }
        $ps = pulse\PulseFactory::build();
        $ps->setName('Withdrawn');
        $ps->setModule('hms');
        $ps->setClassName('Withdrawn');
        $ps->setClassMethod('execute');
        $ps->setInterim('1');
        $ps->setRequiredFile('mod/hms/class/Withdrawn.php');
        pulse\PulseFactory::save($ps);
    }


    public function dropNightlyCacheSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('NightlyCache', 'hms');
        \pulse\PulseFactory::deleteResource($pulse);
    }

    public function addNightlyCacheSchedule()
    {
        $pulse = \pulse\PulseFactory::getByName('NightlyCache', 'hms');
        if (!empty($pulse)) {
            throw new \Exception('Night Cache schedule is already present.');
        }
        $ps = pulse\PulseFactory::build();
        $ps->setName('NightlyCache');
        $ps->setModule('hms');
        $ps->setClassName('NightlyCache');
        $ps->setClassMethod('execute');
        $ps->setExecuteAfter(mktime(24, 0, 0));
        $ps->setInterim('1440');
        $ps->setRequiredFile('mod/hms/class/NightlyCache.php');
        pulse\PulseFactory::save($ps);
    }

}
