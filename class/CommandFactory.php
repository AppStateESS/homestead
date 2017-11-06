<?php

namespace Homestead;

use \Homestead\Exception\CommandNotFoundException;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CommandFactory {
    private static $dir = 'Command';

    public static function getCommand(string $action)
    {
        $class = self::staticInit($action);
        $class = '\\Homestead\\Command\\' . $class;
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
            throw new \InvalidArgumentException("Illegal characters in command {$action}");
        }

        $class = $action.'Command';

        return $class;
    }
}
