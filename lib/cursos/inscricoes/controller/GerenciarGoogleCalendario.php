<?php
// composer require google/apiclient

function createGoogleCalendarEvent() {
    $client = new Google_Client();
    $client->setAuthConfig('caminho/para/seu/arquivo-credenciais.json');
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    
    $service = new Google_Service_Calendar($client);

    $event = new Google_Service_Calendar_Event([
        'summary' => 'Reunião Neusa HU',
        'location' => 'Hospital Universitário',
        'description' => 'Reunião importante com Neusa.',
        'start' => [
            'dateTime' => '2024-10-30T14:00:00-03:00',  // Data e horário de início
            'timeZone' => 'America/Sao_Paulo',
        ],
        'end' => [
            'dateTime' => '2024-10-30T15:00:00-03:00',  // Data e horário de fim
            'timeZone' => 'America/Sao_Paulo',
        ],
        'attendees' => [
            ['email' => 'zakacc@gmail.com'],
        ],
    ]);

    $calendarId = 'primary';  // "primary" para usar o calendário principal
    $event = $service->events->insert($calendarId, $event);

    printf("Evento criado: %s\n", $event->htmlLink);
}

createGoogleCalendarEvent();
