<?php
/********************************************************************************/
/*   Overrides the getDB function and does nothing else.  Allows us to test the */
/* class without accidentally doing anything to the database.                   */
/*                                                                              */
/* @author Daniel West <dwest at tux dot appstate dot edu>                      */
/* @package mod                                                                 */
/* @subpackage hms                                                              */
/********************************************************************************/
require_once('simpletest/unit_tester.php');
require_once('simpletest/mock_objects.php');

Mock::generate('PHPWS_DB');

PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

class HMS_RLC_Application_Test extends HMS_RLC_Application {
    function getDB($param){
        $db = new MockPHPWS_DB();
        $db->setReturnValue('addWhere', null, array(42));
        $db->setReturnValue('addValue', null, array('*', '*'));
        $db->setReturnValue('insert', null);
        $db->setReturnValue('update', null);

        /* For testCheck_for_Application */
        $db->setReturnValue('addWhere', null, array('user_id', '*', '*'));
        $db->setReturnValue('select', array(0 => 'result_0', 1 => 'result_1'), array('row'));

        /* For testDeny_RLC_Application */
        return $db;
    }

    function save(){
        return true;
    }
}
?>
