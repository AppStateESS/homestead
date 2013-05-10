<?php


class Package {
    
    private $id;

    private $carrier; // Carrier name (e.g. UPS, FedEx)
    private $trackingNumber;  // Carrier's tracking number
    private $addressedTo; // Full address of package as given by carrier (optional; if available from carrier)
    private $addressedPhone; // Phone number given by carrier (optional, if available)
    
    private $recipientBannerId; // Student's Banner ID
    private $receivedOn; // Datestamp this package was received
    private $receivedBy; // Person who did the receiving
    private $packageDesk; // PackageDesk ID where this package was received
    
    private $pickedUpOn; // Datestamp this package was picked up by recipeient
    private $releasedBy; // Person who scanned the package at pickup
    
    /**
     * Creates a new Package.
     * 
     * @param string $trackingNumber
     * @param string $carrier
     * @param string $addressedTo
     * @param string $addressedPhone
     * @param Student $recipient
     * @param string $receivedBy
     * @param PackageDesk $packageDesk
     */
    public function __construct($trackingNumber, $carrier, $addressedTo, $addressedPhone, Student $recipient, $receivedBy, PackageDesk $packageDesk)
    {
        // Info from carrier
        $this->trackingNumber = $trackingNumber;
        $this->carrier        = $carrier;
        $this->addressedTo    = $addressedTo;
        $this->addressedPhone = $addressedPhone;
        
        // Receipt Info
        $this->recipientBannerId = $student->getBannerId();
        $this->receivedBy = $receivedBy;
        $this->receivedOn = time();
        $this->packageDesk = $packageDesk->getId();
        
        // Pickup info
        $this->pickedUpOn  = null;
        $this->releasedBy  = null;
    }
}
?>