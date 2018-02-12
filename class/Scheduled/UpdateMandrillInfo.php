<?php

namespace Homestead\Scheduled;

use \Homestead\PdoFactory;

class UpdateMandrillInfo {

  public static function cliExec()
  {
      \PHPWS_Core::initModClass('users', 'Users.php');
      \PHPWS_Core::initModClass('users', 'Current_User.php');

      $userId = \PHPWS_DB::getOne("SELECT id FROM users WHERE username = 'jbooker'");

      $user = new \PHPWS_User($userId);

      // Uncomment for production on branches
      $user->auth_script = 'local.php';
      $user->auth_name = 'local';

      //$user->login();
      $user->setLogged(true);

      \Current_User::loadAuthorization($user);
      //\Current_User::init($user->id);
      $_SESSION['User'] = $user;

      $obj = new UpdateMandrillInfo();
      $obj->execute();
  }

  public function execute()
  {
      $apiKey = "wcdHwGg1BIg5POxYQuEUXw";

      $infoUrl = "https://mandrillapp.com/api/1.0/messages/info.json";
      $contentUrl = "https://mandrillapp.com/api/1.0/messages/content.json";


      try {
        // Create curl connection
        $curl = curl_init();

        // Open the connection to the DB.
        $db = PdoFactory::getPdoInstance();

        // Grab msg ids from email log
        $mandrillIDs = $this->getMsgIds();


        foreach($mandrillIDs as $id)
        {
          // Grab Info about emails from Mandrill
          curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => $infoUrl . "?key=" . $apiKey . "&id=" . $id['message_id']));
          $infoResultJson = curl_exec($curl);
          $infoResult = json_decode($infoResultJson, true);

          if (isset($infoResult['code'])){
              continue;
          }

      //    //Grab the Content from emails - Mandrill
      //    curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => $contentUrl. "?key=" . $apiKey . "&id=" . $id['message_id']));
      //    $contentResult = json_decode(curl_exec($curl), true);

          // Annoying query.
          $query = "UPDATE hms_email_log
                    SET opened = :opened,
                        link_clicked = :link_clicked,
                        email_content = (CASE
                           WHEN email_content IS NOT NULL THEN email_content
                           ELSE :content
                          END)
                    WHERE message_id = :mid;";

          $stmt = $db->prepare($query);

          $stmt->execute(array('opened' => $infoResult['opens'],
                  'link_clicked' => $infoResult['clicks'],
                  'content' => $infoResultJson,
                  'mid' => $id['message_id']
          ));
        }

        // Close PDO and Curl Connections

        curl_close($curl);
        $stmt = null;
        $db = null;

      } catch(Mandrill_Error $e){
        // Mandrill errors are thrown as exceptions
        echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
        // A mandrill error occurred: Mandrill_Unknown_Message - No message exists with the id 'McyuzyCS5M3bubeGPP-XVA'
        throw $e;
      }
  }

  private function getMsgIds()
  {
      // Open the connection to the DB.
      $db = PdoFactory::getPdoInstance();

      // Determine ID for older data in database or if the emails have not been opened.
      $query = "SELECT message_id FROM hms_email_log WHERE email_content IS NULL OR opened = 0"; // AND time_made > time() - 60*60*24*30 30 days....

      $stmt = $db->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
}
