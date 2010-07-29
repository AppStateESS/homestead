<?php

PHPWS_Core::initModClass('hms', 'StudentMenuTermBlock.php');

define('FEATURE_LOCKED_ICON',   '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>');
define('FEATURE_NOTYET_ICON',   '<img class="status-icon" src="images/mod/hms/tango/appointment-new.png" alt="Locked"/>');
define('FEATURE_OPEN_ICON',     '<img class="status-icon" src="images/mod/hms/tango/go-next.png" alt="Open"/>');
define('FEATURE_COMPLETED_ICON','<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>');

class ReturningMainMenuView extends View {

	private $student;
	private $lotteryTerm;

	public function __construct(Student $student, $lotteryTerm)
	{
		$this->student		= $student;
		$this->lotteryTerm	= $lotteryTerm;
	}

	public function show()
	{
		$tpl = array();

		$springTerm = Term::getNextTerm($this->lotteryTerm);
		$summerTerm1 = Term::getNextTerm(Term::getCurrentTerm());
		$summerTerm2 = Term::getNextTerm($summerTerm1);

		$terms = array($this->lotteryTerm, $summerTerm1, $summerTerm2);

		foreach($terms as $t){
			$termBlock = new StudentMenuTermBlock($this->student, $t);
			$tpl['TERMBLOCK'][] = array('TERMBLOCK_CONTENT'=>$termBlock->show());
		}

        Layout::addPageTitle("Main Menu");

		return PHPWS_Template::process($tpl, 'hms', 'student/returningMenu.tpl');
	}
}
?>