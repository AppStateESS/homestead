<?php

class AssignByFloorView extends hms\View
{
    public function __construct()
    {
    }

    public function show()
    {
        $tpl = array();

        $tpl['SOURCE_HTTP'] = PHPWS_SOURCE_HTTP;
        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'assignByFloor');

        return \PHPWS_Template::process($tpl, 'hms', 'admin/AssignByFloor.tpl');
    }

}
