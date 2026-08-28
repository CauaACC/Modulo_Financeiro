<?php

class GoogleCalendarioVO {
    private $summary;
    private $location;
    private $description;
    private $startDateTime;
    private $endDateTime;
    private $attendees;

    public function __construct($summary, $location, $description, $startDateTime, $endDateTime, $attendees) {
        $this->summary = $summary;
        $this->location = $location;
        $this->description = $description;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->attendees = $attendees;
    }

    public function getSummary() {
        return $this->summary;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getStartDateTime() {
        return $this->startDateTime;
    }

    public function getEndDateTime() {
        return $this->endDateTime;
    }

    public function getAttendees() {
        return $this->attendees;
    }
}