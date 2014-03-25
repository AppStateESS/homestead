<?php

PHPWS_Core::initModClass('hms', 'DamageTypeFactory.php');

class RoomDamageAssessmentView extends hms\View{

    public function __construct()
    {
    }

    public function show()
    {
        $vars = array();

        $vars['DAMAGE_TYPES'] = json_encode(DamageTypeFactory::getDamageTypeAssoc());

        // TODO: abstract this out to a higher level class
        $http = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] ? 'https:' : 'http:';
        $vars['JAVASCRIPT_BASE'] = PHPWS_SOURCE_HTTP . 'mod/hms/javascript';

        $vars['TERM'] = Term::getSelectedTerm();

        javascript('jquery');

        // Load header for Angular Frontend
        javascriptMod('hms', 'AngularFrontend', $vars);

        $rawfile = PHPWS_SOURCE_HTTP . 'mod/hms/templates/Angular/damage-charge.html';
        return '<div data-ng-app="hmsAngularApp"><div data-ng-include="\''.$rawfile.'\'"></div></div>';
    }
}

?>
