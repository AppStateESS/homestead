<?php
/*****************************************************************************/
/*   Interfaces simpletest to phpWebsite and displays the output of the test */
/* results.                                                                  */
/*                                                                           */
/* @author Daniel West <dwest at tux dot appstate dot edu>                   */
/* @package mod                                                              */
/* @subpackage hms                                                           */
/*****************************************************************************/
require_once('test/simpletest/unit_tester.php');
require_once('test/VerboseReporter.php');

PHPWS_Core::initModClass('hms', 'test/HMS_RLC_Application_Test_Suite.php');

class HMS_Test {

    function main($param=null){
        switch($param){
            case null:
                return HMS_Test::menu();
                break;
            case 'all':
                return HMS_Test::all();
                break;
            default:
                return HMS_Test::doTest($param);
                break;
        }
    }

    function menu(){
    }

    function all(){
        $test = &new TestSuite('All Unit Tests');
        $test->addTestCase(new HMS_RLC_Application_Test_Suite());
        $test->run(new VerboseReporter());
        exit();
    }

    function doTest($param){
    }
}
?>
