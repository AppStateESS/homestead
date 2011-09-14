<?php

class AssignmentPairing {

    private $student1;
    private $student2;

    private $gender;
    private $lifestyle;

    private $bed1;
    private $bed2;

    private $earliestTime;

    public function __construct(Student $student1, Student $student2, $lifestyle, $earliestTime)
    {
        $this->student1 = $student1;
        $this->student2 = $student2;
        $this->lifestyle = $lifestyle;
        $this->gender = $student1->getGender();

        $this->earliestTime = $earliestTime;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getLifestyle()
    {
        return $this->lifestyle;
    }

    public function get($username)
    {
        if($this->student1->getUsername() == $username) {
            return $this->student1;
        } else if($this->student2->getUsername() == $username) {
            return $this->student2;
        } else {
            return null;
        }
    }

    public function __toString()
    {
        return $this->student1->getUsername() . '+' . $this->student2->getUsername();
    }

    public function getStudent1()
    {
        return $this->student1;
    }

    public function getStudent2()
    {
        return $this->student2;
    }

    public function getBed1()
    {
        return $this->bed1;
    }

    public function setBed1($bed1)
    {
        $this->bed1 = $bed1;
    }

    public function getBed2()
    {
        return $this->bed2;
    }

    public function setBed2($bed2)
    {
        $this->bed2 = $bed2;
    }

    public function isAssigned()
    {
        return isset($bed1) || isset($bed2);
    }

    public function getEarliestAppTimestamp(){
        return $this->earliestTime;
    }
}

?>
