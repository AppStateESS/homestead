<?php
namespace Homestead\Command;

use Homestead\EmailLogFactory;

class GetEmailLogMessageListCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'GetEmailLogMessageListCommand');
    }

    public function execute(CommandContext $context)
    {
        $bannerId = $context->get('bannerId');

        $messages = EmailLogFactory::getMessageByBannerId($bannerId);

        echo json_encode($messages);
        exit;
    }
}

?>
