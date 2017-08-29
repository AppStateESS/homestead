<?php

namespace Homestead\Command;

 

class ShowLotteryInviteResultsCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowLotteryInviteResults');
    }

    public function execute(CommandContext $context)
    {

        $output = $_SESSION['LOTTERY_OUTPUT'];

        $html = "";

        foreach($output as $line){
            $html .= $line ."<br />\n";
        }

        $context->setContent($html);

        unset($_SESSION['LOTTERY_OUTPUT']);
    }
}
