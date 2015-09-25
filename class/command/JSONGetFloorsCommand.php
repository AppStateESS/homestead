<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class JSONGetFloorsCommand
{

    public function getRequestVars()
    {
        return array('action' => 'JSONGetFloors');
    }

    public function execute(CommandContext $context)
    {
        $pdo = PdoFactory::getPdoInstance();
        $hall_id = (int) $context->get('hallId');

        $prep = $pdo->prepare('select id, floor_number, gender_type from hms_floor where residence_hall_id=? and is_online=1 order by floor_number');
        $prep->execute(array($hall_id));
        $rows = $prep->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return null;
        }
        foreach ($rows as $k => $r) {
            $gender = HMS_Util::formatGender($r['gender_type']);
            $rows[$k]['title'] = $r['floor_number'] . ' - ' . $gender;
        }

        $context->setContent(json_encode($rows));
    }

}
