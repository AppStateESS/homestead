<?php

class MandrillMessage {

    private $apiKey;
    private $to;
    private $cc;

    private $fromEmail;
    private $fromName;

    private $subject;
    private $text;
    private $html;

    private $metadata;

    public function __construct(string $apiKey, Array $to, Array $cc, string $fromEmail, string $fromName, string $subject, string $text, string $html, Array $metadata)
    {
        $this->apiKey = $apiKey;
        $this->to = $to;
        $this->cc = $cc;

        $this->fromEmail = $fromEmail;
        $this->fromname = $fromName;

        $this->subject = $subject;
        $this->text = $text;
        $this->html = $html;

        $this->metadata = $metadata;
    }

    public function send(){
        $mandrill = new Mandrill($this->apiKey);
        $result = $mandrill->messages->send($this->getMessageArray());

        return $result;
    }

    private function getMessageArray()
    {
        $toList = array();
        foreach($this->to as $t){
            $toList[] = array(
                'email' => $t['email'],
                'name' => $t['name'],
                'type' => 'to'
            );
        }

        foreach($this->cc as $c){

        }

        $messageArray = array(
            'html' => $this->html,
            'text' => $this->text,
            'subject' => $this->subject,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'to' => $toList,
            'headers' => array('Reply-To' => FROM_ADDRESS),
            'track_opens' => true,
            'track_clicks' => true,
            'metadata' => $this->metadata,


        );
    }
}
