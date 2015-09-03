<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetRoomDamageTypesCommand extends Command {


    public function getRequestVars(){
        return array('action'=>'AjaxGetRoomDamageTypes');
    }

    public function execute(CommandContext $context)
    {

      $damageTypes = DamageTypeFactory::getDamageTypeAssoc();

      $types = array();

      $t = 0;

      foreach($damageTypes as $attribs)
      {
        $types[$t] = $attribs;
        $t++;
      }

      $categories = array();

      foreach($damageTypes as $node)
      {
        if(!in_array($node['category'], $categories))
        {
          array_push($categories, $node['category']);
        }
      }

      $i = 0;

      foreach ($categories as $category)
      {
        $result[$i]['category'] = $category;
        $result[$i]['DamageTypes'] = array();
        $i++;
      }

      $resultSize = count($result);

      $i = 0;

      foreach ($types as $node)
      {
        $category = $node['category'];
        for($i; $i < $resultSize; $i++)
        {
          if($result[$i]['category'] == $category)
          {
            array_push($result[$i]['DamageTypes'], $node);
          }
        }
        $i = 0;
      }

      echo json_encode($result);
      exit;
    }
}
