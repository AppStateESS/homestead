<?php

namespace Homestead;

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class SearchByRlcView extends View {

    public function show(){

        $tpl = array();
        $tpl['LEARNING_COMMUNITIES'] = array();
        $tpl['TITLE'] = "Learning Communities";

        $term = Term::getSelectedTerm();
        $rlcs = RlcFactory::getRlcList($term);

        $keys = array_keys($rlcs);

        foreach($keys as $rlcId) {
            $cmd = CommandFactory::getCommand('ShowSearchByRlc');
            $node = array('URL' => $cmd->getURI().'&rlc='.$rlcId, 'RLC_NAME' => $rlcs[$rlcId]);
            $tpl['LEARNING_COMMUNITIES'][] = $node;
        }

        $final = \PHPWS_Template::processTemplate($tpl, 'hms', 'admin/search_by_rlc.tpl');
        return $final;
    }
}
