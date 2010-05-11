<?php

PHPWS_Core::initModClass('hms', 'StudentMenuTermBlock.php');

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