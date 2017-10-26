<?php

/**
 * Homestead Autoloader
 * Looks for hms classes from the \Homestead... namespace and tries to load
 * them out of the /src... directory.
 *
 * @author Cydney Caldwell
 */
 class HomesteadAutoLoader{
     public static function HomesteadLoader($class_name){
         // Class name must start with the 'Homestead\' namespace. If not, we pass and hope another autoloader can help
         if(substr($class_name, 0, strlen('Homestead\\')) !== 'Homestead\\'){
             return false;
         }
         $file_path = PHPWS_SOURCE_DIR . str_replace('\\', '/', str_replace('Homestead\\', 'mod/hms/class/', $class_name)) . '.php';
         if (is_readable($file_path)) {
             require_once $file_path;
             return true;
         } else {
             return false;
         }
     }
}
