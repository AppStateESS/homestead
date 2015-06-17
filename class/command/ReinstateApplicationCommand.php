<?php

class ReinstateApplicationCommand extends Command {

  private $db;
  private $applicationId;
  private $bannerId;
  private $username;

  public function getRequestVars()
  {
      return array('action'=>'ReinstateApplication', 'applicationId'=>$this->applicationId,
                    'bannerId'=>$this->bannerId, 'username'=>$this->username);
  }

  public function execute(CommandContext $context)
  {
      if(!Current_User::allow('hms', 'cancel_housing_application'))
      {
          PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
          throw new PermissionException('You do not have permission to cancel housing applications.');
      }

      // Check for a housing application id
      $this->setAppId($context->get('applicationId'));
      $this->setBannerId($context->get('bannerId'));
      $this->setUsername($context->get('username'));

      if(!isset($this->applicationId) || is_null($this->applicationId))
      {
          throw new InvalidArgumentException('Missing housing application id.');
      }

      $sql = 'update hms_new_application set cancelled = 0, cancelled_by = null, cancelled_reason = null, cancelled_on = null where id = ';
      $sql .= $this->applicationId;

      $this->db = new PHPWS_DB('hms_new_application');

      PHPWS_DB::query($sql);

      HMS_Activity_Log::log_activity($this->username, ACTIVITY_REINSTATE_APPLICATION, UserStatus::getUsername());

      $returnCmd = CommandFactory::getCommand('ShowStudentProfile');
      $returnCmd->setBannerId($this->bannerId);
      $returnCmd->redirect();
  }

  public function setAppId($appId)
  {
    $this->applicationId = $appId;
  }

  public function setBannerId($bannerId)
  {
    $this->bannerId = $bannerId;
  }

  public function setUsername($username)
  {
    $this->username = $username;
  }

}
