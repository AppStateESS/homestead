<?php

namespace Homestead\Command;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class JSONGetHallsCommand
{

    public function getRequestVars()
    {
        return array('action' => 'JSONGetHalls');
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getSelectedTerm();
        $pdo = PdoFactory::getPdoInstance();
        $prep = $pdo->prepare("select id, hall_name as title from hms_residence_hall where term=? and is_online=1 order by hall_name");
        $prep->execute(array($term));
        $halls = $prep->fetchAll(\PDO::FETCH_ASSOC);
        $context->setContent(json_encode($halls));
    }

}
