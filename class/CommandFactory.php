<?php

namespace Homestead;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CommandFactory {
    private static $dir = 'command';

    public static function getCommand($action = 'Default')
    {
        if(is_null($action)) {
            $action = 'Default';
        }

        $class = self::staticInit($action);

        $cmd = new $class();
        return $cmd;
    }

    public static function onAllCommands($obj, $func)
    {
        $dir = self::$dir;

        $files = scandir("{$dir}/");

        foreach($files as $file) {
            $cmd = preg_replace('Command\.php$', '', $file);
            if($cmd == $file) continue;

            $obj->$func($file, $cmd);
        }
    }

    public static function staticInit($action)
    {
        $dir = self::$dir;

        if(preg_match('/\W/', $action)) {
            PHPWS_Core::initModClass('hms', 'exception/IllegalCommandException.php');
            throw new IllegalCommandException("Illegal characters in command {$action}");
        }

        $class = $action.'Command';

        try {
            PHPWS_Core::initModClass('hms', "{$dir}/{$class}.php");
        }catch(Exception $e){
            PHPWS_Core::initModClass('hms', 'exception/CommandNotFoundException.php');
            throw new CommandNotFoundException("Could not initialize {$class}: {$e->getMessage()}");
        }

        return $class;
    }
}
