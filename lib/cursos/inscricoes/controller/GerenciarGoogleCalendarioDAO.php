<?php

require_once 'vendor/autoload.php';
require_once 'GoogleCalendarioVO.php';

class GoogleCalendarioDAO extends DAO {
    private $client;
    private $service;

    public function __construct() {
        parent::__construct();
        $this->client = new Google_Client();
        $this->client->setAuthConfig('caminho/para/seu/arquivo-credenciais.json');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->service = new Google_Service_Calendar($this->client);
    }

    public function createEvent(GoogleCalendarioVO $eventVO, $debug = false) {
        try {
            $event = new Google_Service_Calendar_Event([
                'summary' => $eventVO->getSummary(),
                'location' => $eventVO->getLocation(),
                'description' => $eventVO->getDescription(),
                'start' => [
                    'dateTime' => $eventVO->getStartDateTime(),
                    'timeZone' => 'America/Sao_Paulo',
                ],
                'end' => [
                    'dateTime' => $eventVO->getEndDateTime(),
                    'timeZone' => 'America/Sao_Paulo',
                ],
                'attendees' => array_map(function($email) {
                    return ['email' => $email];
                }, $eventVO->getAttendees()),
            ]);

            $calendarId = 'primary';
            $event = $this->service->events->insert($calendarId, $event);

            // Gravar informações no banco de dados
            $this->saveEventToDatabase($event);

            return $event->getId();
        } catch (Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function deleteEvent($eventId, $debug = false) {
        try {
            $calendarId = 'primary';
            $this->service->events->delete($calendarId, $eventId);

            // Remover informações do banco de dados
            $this->deleteEventFromDatabase($eventId);

            return true;
        } catch (Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    private function saveEventToDatabase($event) {
        $sql = "INSERT INTO eventos_google_calendar (event_id, summary, location, description, start_date_time, end_date_time, attendees) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $event->getId(),
            $event->getSummary(),
            $event->getLocation(),
            $event->getDescription(),
            $event->getStart()->dateTime,
            $event->getEnd()->dateTime,
            json_encode($event->getAttendees())
        ];

        $this->conex->Execute($sql, $params);
    }

    private function deleteEventFromDatabase($eventId) {
        $sql = "DELETE FROM eventos_google_calendar WHERE event_id = ?";
        $params = [$eventId];

        $this->conex->Execute($sql, $params);
    }
}