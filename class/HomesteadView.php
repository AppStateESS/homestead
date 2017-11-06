<?php

namespace Homestead;

abstract class HomesteadView {
    private $main;

    protected $notifications;

    public function addNotifications($n)
    {
        $this->notifications = $n;
    }

    public function setMain($content)
    {
        $this->main = $content;
    }

    public function getMain()
    {
        return $this->main;
    }

    public abstract function render();
}
