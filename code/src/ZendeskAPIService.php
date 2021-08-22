<?php
namespace project;
use project\APIService;
use project\TicketDTO;
use GuzzleHttp\Exception\GuzzleException;
/*include_once 'APIService.php';*/

class ZendeskAPIService extends APIService
{
    private $helpDesk;

    public function __construct(bool $isToken, string $subdomain, string $username, string $password)
    {
        parent::__construct($isToken, $subdomain, $username, $password);
        $this->helpDesk = '.zendesk.com/api/';
    }


    public function generateURL($param)
    {
        return $this->protocol . $this->subdomain . $this->helpDesk . $this->version . $param . '.json';
    }

    public function migrateToZendesk($tickets)
    {
        foreach ($tickets as $ticket) {
            try {
                $response = $this->client->request(
                    'POST', $this->generateURL('imports/tickets'),
                    array_merge(['headers' => ['Content-Type' => 'application/json'],
                       'json' => ['ticket' => $ticket]], $this->auth)
                );
                $test = json_decode($response->getBody(), true);
                //$test = $response->getStatusCode();
            } catch (\Exception $exception) {
                echo $exception;
            }

        }
    }


    public function getStatusString($value)
    {
        switch($value)
        {
            case 2: return 'open'; break;
            case 3: return 'pending'; break;
            case 4: return 'solved'; break;
            case 5: return 'closed'; break;
        }
    }

    public function getPriorityString($value)
    {
        switch($value)
        {
            case 1: return 'low'; break;
            case 2: return 'normal'; break;
            case 3: return 'high'; break;
            case 4: return 'urgent'; break;
        }
    }


    public function searchOrCreateUser($name, $email, $role)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('users/search'),
                array_merge(['query' => ['query' => $email]], $this->auth)
            );
        } catch (\Exception $exception) {
            echo $exception;
        }

        $data = json_decode($response->getBody(), true);

        if($data['users'] != [])
        {
            return ['id' => $data['users'][0]['id'], 'name' => $data['users'][0]['name'], 'email' => $email];
        }
        else
        {
            try {
                $response = $this->client->request(
                    'POST', $this->generateURL('users'),
                    array_merge(['headers' => ['Content-Type' => 'application/json'],
                        'json' =>  ['user' => ['name' => $name, 'email' => $email, 'role' => $role]]],
                        $this->auth)
                );
                $l =$response;
            } catch (\Exception $exception) {
                $status = $response;
                $l =$response;
            }

            $data = json_decode($response->getBody(), true);

            if($data['users'] == [])
            {
                return ['id' => 385451567460, 'name' => 'Peggy Dobson', 'email' => 'migrate.qa+2@gmail.com'];
            }
            else
            {
                return ['id' => $data['user']['id'], 'name' => $name, 'email' => $email];
            }
        }
    }

    public function searchOrCreateGroup($name)
    {
        $response = [];
        $all_data = [];
        $next = '';
        $page = 1;
        do {
                try {
                    $response = $this->client->request(
                        'GET', $this->generateURL('groups'),
                        array_merge(['query' => ['per_page' => 100, 'page' => $page]], $this->auth)
                    );
                } catch (\Exception $exception) {
                    echo $exception;
                }
                $data = json_decode($response->getBody(), true);
                $next = $data['next_page'];
                $all_data = array_merge($all_data, $data['groups']);
                $page++;
        }
        while($next != null);

        $isName = false;
        $id = 0;

        foreach ($all_data as $group)
        {
            if($group['name'] == $name)
            {
                $isName = true;
                $id = $group['id'];
                return ['id' => $id, 'name' => $name];
            }
        }

            try {
                $response = $this->client->request(
                    'POST', $this->generateURL('groups'),
                    array_merge(['headers' => ['Content-Type' => 'application/json'],
                        'json' =>  ['group' => ['name' => $name]]],
                        $this->auth)
                );

                $data = json_decode($response->getBody(), true);
            } catch (\Exception $exception) {
                $res2 = $response;
                echo $exception;
            }

            return ['id' => $data['group']['id'], 'name' => $name];
        }


    public function searchOrCreateOrganization($name)
    {
        $response = [];
        $all_data = [];
        $hasMore = 0;
        $next = '';
        do {
            if($hasMore == 0)
            {
                try {
                    $response = $this->client->request(
                        'GET', $this->generateURL('organizations'),
                        array_merge(['query' => ['include' => 'page[size]=100']], $this->auth)
                    );
                } catch (\Exception $exception) {
                    echo $exception;
                }
                $data = json_decode($response->getBody(), true);
                $hasMore = $data['count'];
                $next = $data['next_page'];
                $all_data = array_merge($all_data, $data['organizations']);
            }
            else
            {
                try{
                    $response = $this->client->request(
                        'GET', $next, $this->auth
                    );
                } catch (\Exception $exception) {
                    echo $exception;
                }
                $data = json_decode($response->getBody(), true);
                $hasMore = $data['count'];
                $next = $data['next_page'];
                $all_data = array_merge($all_data, $data['organizations']);
            }
        }
        while($next != null);

        $isName = false;
        $id = 0;
        foreach ($all_data as $organization)
        {
            if($organization['name'] == $name)
            {
                $isName = true;
                $id = $organization['id'];
            }
        }

        if ($isName == true)
        {
            return ['id' => $id, 'name' => $name];
        }
        else
        {
            try {
                $response = $this->client->request(
                    'POST', $this->generateURL('organizations'),
                    array_merge(['headers' => ['Content-Type' => 'application/json'],
                        'json' =>  ['organization' => ['name' => $name]]],
                        $this->auth)
                );
            } catch (\Exception $exception) {
                echo $exception;
            }
            $data = json_decode($response->getBody(), true);
            return ['id' => $data['organization']['id'], 'name' => $name];
        }
    }


    public function saveResponse()
    {
        $response = [];

        $page = 1;
        $next = '';

       //if ($firstResponse == true) {
        $csvHandler = new CSVService();
        $csvHandler->headersToCSV();

                do {
                    try {
                        $response = $this->client->request(
                            'GET', $this->generateURL('tickets'),
                            array_merge(['query' => ['per_page' => 1, 'page' =>  $page]], $this->auth)
                        );
                    } catch (\Exception $exception) {
                        echo $exception;
                    }
                    $data = json_decode($response->getBody(), true);
                    $next = $data['next_page'];
                    $this->saveDtoToCSV($data);
                    $page++;
                }
                while($next != null);
    }

    public function saveDtoToCSV($data)
    {
        $tickets = [];
        foreach ($data['tickets'] as $ticket) {
            $oneTicket = new TicketDTO(
                $ticket['id'],
                $ticket['description'] ?? '',
                $ticket['status'],
                $ticket['priority'] ?? '',
                $ticket['submitter_id'],
                $this->getUserNameAndEmail($ticket['submitter_id'])[0],
                $this->getUserNameAndEmail($ticket['submitter_id'])[1],
                $ticket['requester_id'],
                $this->getUserNameAndEmail($ticket['requester_id'])[0],
                $this->getUserNameAndEmail($ticket['requester_id'])[1],
                $ticket['group_id'],
                $this->getGroupName($ticket['group_id']),
                $ticket['organization_id'] ?? 0,
                $this->getOrganizationName($ticket['organization_id']),
                $this->getComments($ticket['id'])
            );
            $tickets[] = $oneTicket;
        }

        $this->csvHandler->saveArrayToCSV($tickets);
    }

    public function getUserNameAndEmail($userID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('users/' . $userID) ,
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        $name = $data['user']['name'] ?? '';
        $email = $data['user']['email'] ?? '';

        return [$name, $email];
    }

    public function getGroupName($groupID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('groups/' . $groupID),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        return $data['group']['name'] ?? '';
    }

    public function getOrganizationName($organizationID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET',  $this->generateURL('organizations/' . $organizationID),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        return $data['organization']['name'] ?? '';
    }

    public function getComments($ticketID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('tickets/' . $ticketID . '/comments'),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }
        $fullComment = '';
        foreach(json_decode($response->getBody(), true)['comments'] as $comment) {
            $fullComment .=  $comment['body'] . PHP_EOL;
        }
        return substr($fullComment, 0, -1);
    }
}
