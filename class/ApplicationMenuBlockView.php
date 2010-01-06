<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ApplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $assignment;
    private $application;
    private $roommateRequests;

    public function __construct($term, $startDate, $endDate, HMS_Assignment $assignment = NULL, LotteryApplication $application = NULL, $roommateRequests)
    {
        $this->term               = $term;
        $this->startDate          = $startDate;
        $this->endDate            = $endDate;
        $this->application        = $application;
        $this->assignment         = $assignment;
        $this->roommateRequests   = $roommateRequests;
    }

    public function show()
    {
        $tpl = array();

        if( is_null($this->application) ){
            $appCommand = CommandFactory::getCommand('ShowTermsAgreement');
            $tpl['APP_NOW'] = $appCommand->getLink('Apply now!');
        } else if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->beginDate);
        } else if(time() > $this->endDate){
            $tpl['END_DEADLINE'] = "Application for this term ended on " . HMS_Util::getFriendlyDate($this->startDate);
        } else {
            $appCommand = CommandFactory::getCommand('ShowApplicationView');
            if(!is_null($this->application)){
                $appCommand->setAppId($this->application->id);
            }
            $tpl['VIEW_APP'] = $appCommand->getLink('View your application');
        }

        //TODO roommate requests!!

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/applicationMenuBlock.tpl');
    }
}

?>
