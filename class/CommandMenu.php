<?php

/**
 * HMS Command Menu
 *
 * Displays a list of links to commands.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CommandMenu extends View
{
    protected $context;
    protected $commands;

    public function __construct()
    {
        $this->commands = array();
    }

    public function addCommandByName($text, $command)
    {
        $this->addCommand($text, CommandFactory::getCommand($command));
    }

    public function addCommand($text, Command $command)
    {
        $this->commands[$text] = $command;
    }

    public function setContext(CommandContext $context)
    {
        $this->context = $context;
    }

    protected function plugCommands(array &$tpl)
    {
        foreach($this->commands as $text=>$command) {

            // Get Menu link from Command Class
            $link = call_user_func(array($command, 'getLink'), $text);

            // Get Current Context
            if(!isset($this->context)) {
                $this->context = new CommandContext();
            }

            // Determine if link command is the active command
            if(is_a($command, $this->context->get('action') . 'Command')) {
                // Add active link
                $tpl['LINK'][]['ACTIVE_LINK'] = $link;
                continue;
            }

            // Add inactive link
            $tpl['LINK'][]['LINK'] = $link;
        }
    }

    public function show()
    {
        $tpl = array();

        $this->plugCommands($tpl);

        return PHPWS_Template::process($tpl, 'hms', 'CommandMenu.tpl');
    }
}
