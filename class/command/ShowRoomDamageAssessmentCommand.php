<?php

class ShowRoomDamageAssessmentCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowRoomDamageAssessment');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'damage_assessment')){
            throw new PermissionException('You do not have permission to perform room damage assessment.');
        }

        $tpl = array();

        $tpl['TERM'] = Term::getSelectedTerm();

        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'damageAssessment');

        $context->setContent(PHPWS_Template::process($tpl, 'hms', 'admin/roomDamageAssessment.tpl'));
    }
}
