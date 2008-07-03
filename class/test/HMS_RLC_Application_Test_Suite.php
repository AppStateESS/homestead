<?php
/***********************************************************/
/*   Tests the HMS_RLC_Application class                   */
/*                                                         */
/* @author Daniel West <dwest at tux dot appstate dot edu> */
/* @package mod                                            */
/* @subpackage hms                                         */
/***********************************************************/
require_once('HMS_RLC_Application_Test.php');

class HMS_RLC_Application_Test_Suite extends UnitTestCase {
    var $application;
    var $initial_session;

    function HMS_RLC_Application_Test_Case(){
        $this->unitTestCase('HMS_RLC_Application Test Suite');
    }

    function setUp(){
        $this->initial_session = $_SESSION;

        $this->application = &new HMS_RLC_Application_Test();
        $this->application->setID(42);
        $this->application->setUserID('lw77517');

        $_SESSION['asu_username']             = 'lw77517';
        $_REQUEST['rlc_first_choice']         = 0;
        $_REQUEST['rlc_second_choice']        = 1;
        $_REQUEST['rlc_third_choice']         = 2;
        $_REQUEST['why_specific_communities'] = 'because';
        $_REQUEST['strengths_weaknesses']     = 'strong/weak in all the right places';
        $_REQUEST['rlc_question_0']           = '42?';
        $_SESSION['application_term']         = '200840';
    }

    function tearDown(){
        $_SESSION = $this->initial_session;
    }

    function testDelete(){
        $this->assertTrue($this->application->delete());
        $this->assertEqual($this->application->id, 0);
    }

    function testSave_Application(){
        $this->assertTrue($this->application->save_application());
        $application = $this->application->save_application(true);  //set the testing flag to get some useful data back
        $this->assertEqual($application->user_id, 'lw77517');
        $this->assertEqual($application->rlc_first_choice_id, 0);
        $this->assertEqual($application->rlc_second_choice_id, 1);
        $this->assertEqual($application->rlc_third_choice_id, 2);
        $this->assertEqual($application->why_specific_communities, 'because');
        $this->assertEqual($application->strengths_weaknesses, 'strong/weak in all the right places');
        $this->assertEqual($application->rlc_question_0, '42?');
        $this->assertNull($application->rlc_question_1);
        $this->assertNull($application->rlc_question_2);
        $this->assertEqual($application->term, '200840');
    }

    function testCheck_for_Application(){
        $this->assertEqual($this->application->check_for_application('lw77517'), array(0 => 'result_0', 1 => 'result_1'));
    }

    function testDeny_RLC_Application(){
        $db = new MockPHPWS_DB();
        $db->expectOnce('addWhere', array('id', 42));
        $db->expectOnce('addValue', array('denied', 1));
        $db->expectOnce('update');

        $_REQUEST['id'] = 42;

        $this->application->deny_rlc_application($db); //#TODO make this check the status of the message returned
    }
}
?>
