<?php

namespace Homestead;

/**
 * TermSelector.php
 *
 * @author jbooker
 * @package Homestead
 */
class TermSelector extends View {

    public function show()
    {
    	if(UserStatus::isGuest()) {
            return '';
        }

        $terms = Term::getTermsAssoc();

        $current = Term::getCurrentTerm();
        if(isset($terms[$current])){
            $terms[$current] .= ' (Current)';
        }

        $form = new \PHPWS_Form('term_selector');

        $cmd = CommandFactory::getCommand('SelectTerm');
        $cmd->initForm($form);

        $form->addDropBox('term', $terms);

        $tags = $form->getTemplate();

        $currentTerm = Term::getSelectedTerm();
        $tags['TERM_OPTIONS'] = array();

        foreach ($tags['TERM_VALUE'] as $key => $value) {
            $selected = '';
            if($key == $currentTerm) {
            	$selected = 'selected="selected"';
            }
        	$tags['TERM_OPTIONS'][] = array('id'=>$key, 'term'=>$value, 'selected'=>$selected);
        }

        javascript('jquery');
        javascriptMod('hms', 'jqueryCookie');
        javascript('modules/hms/SelectTerm');

        return \PHPWS_Template::process($tags, 'hms', 'admin/SelectTerm.tpl');
    }
}
