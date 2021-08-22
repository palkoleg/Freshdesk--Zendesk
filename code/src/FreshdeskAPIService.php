<?php
namespace project;
use project\APIService;
use GuzzleHttp\Exception\GuzzleException;
/*include_once 'APIService.php';*/

class FreshdeskAPIService extends APIService
{
    private $helpDesk;
    private $toZendesk;

    public function __construct(bool $isToken, string $subdomain, string $username, string $password)
    {
        parent::__construct($isToken, $subdomain, $username, $password);
        $this->helpDesk = '.freshdesk.com/api/';
        $this->toZendesk = new ZendeskAPIService(true,'helpdeskmigrationservice1625213240','qa@help-desk-migration.com/token','Orc6EHV3ZdcqKt1D7TO9mPp5FhoCbanehNSZiUhP');
    }

    public function generateURL($param)
    {
        return $this->protocol . $this->subdomain . $this->helpDesk . $this->version . $param . '.json';
    }


    public function saveResponse()
    {
        $response = [];

        //$page = 1;

        /*$csvHandler = new CSVService();
        $csvHandler->headersToCSV();*/

                //do {
                    try {
                        $response = $this->client->request(
                            'GET', $this->generateURL('tickets'),
                            array_merge(['query' => ['per_page' => 1, 'page' =>  1/*$page*/, 'include' => 'description']], $this->auth)
                        );
                    } catch (GuzzleException $e) {
                        echo $e;
                    }
                    $data = json_decode($response->getBody(), true);
                    $this->saveToDtoAndMigrate($data);
                    //$page++;
                //}
               //while(!$e);
    }

    public function saveToDtoAndMigrate($data)
    {
        $tickets = [];
        foreach ($data as $ticket) {
            $oneTicket = [
                //'id'=>$ticket['id'],
                'description' => $ticket['description_text'] ?? '',
                'status' => $this->toZendesk->getStatusString($ticket['status']),
                'priority' => $this->toZendesk->getPriorityString($ticket['priority']),

                'submitter_id' => $this->toZendesk->searchOrCreateUser($this->getAgentNameAndEmail($ticket['responder_id'])[0],
                $this->getAgentNameAndEmail($ticket['responder_id'])[1],
                'agent')['id'],

                /*'submitter_name' => $toZendesk->searchOrCreateUser($this->getAgentNameAndEmail($ticket['responder_id'])[0],
                    $this->getAgentNameAndEmail($ticket['responder_id'])[1],
                    'agent',
                    $this->getOrganizationName($ticket['company_id']))['name'],*/

                /*'submitter_email' => $toZendesk->searchOrCreateUser($this->getAgentNameAndEmail($ticket['responder_id'])[0],
                    $this->getAgentNameAndEmail($ticket['responder_id'])[1],
                    'agent',
                    $this->getOrganizationName($ticket['company_id']))['email'],*/

                'requester_id' => $this->toZendesk->searchOrCreateUser($this->getUserNameAndEmail($ticket['requester_id'])[0],
                    $this->getUserNameAndEmail($ticket['requester_id'])[1],
                    'end-user')['id'],
                /*'requester_name' => $toZendesk->searchOrCreateUser($this->getAgentNameAndEmail($ticket['requester_id'])[0],
                $this->getAgentNameAndEmail($ticket['requester_id'])[1],
                'end-user',
                $this->getOrganizationName($ticket['company_id']))['name'],

                'requester_email' => $toZendesk->searchOrCreateUser($this->getAgentNameAndEmail($ticket['requester_id'])[0],
                    $this->getAgentNameAndEmail($ticket['requester_id'])[1],
                    'end_user',
                    $this->getOrganizationName($ticket['company_id']))['email'],*/

                'group_id' => $this->toZendesk->searchOrCreateGroup($this->getGroupName($ticket['group_id']))['id'],
                /*'group_name' => $toZendesk->searchOrCreateGroup($this->getGroupName($ticket['group_id']))['name'],*/

                'organization_id' => $this->toZendesk->searchOrCreateOrganization($this->getOrganizationName($ticket['company_id']))['id'],
                /*'organization_name' => $toZendesk->searchOrCreateOrganization($ticket['company_id'])['name'],*/
                'created_at' => $ticket['created_at'],
                'comments' => $this->getComments($ticket['id']) ?? []
            ];
            $tickets[] = $oneTicket;
        }

        $this->toZendesk->migrateToZendesk($tickets);
    }


    public function getUserNameAndEmail($userID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('contacts/' . $userID),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;

            return null;
        }

        $data = json_decode($response->getBody(), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';

        return [$name, $email];
    }

    public function getAgentNameAndEmail($userID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('agents/' . $userID),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        $name = $data['contact']['name'] ?? '';
        $email = $data['contact']['email'] ?? '';

        return [$name, $email];
    }

    public function getGroupName($groupID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET',  $this->generateURL('groups/' . $groupID),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        return $data['name'] ?? '';
    }

    public function getOrganizationName($organizationID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('companies/' . $organizationID),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $data = json_decode($response->getBody(), true);
        return $data['name'] ?? '';
    }

    public function getComments($ticketID)
    {
        $response = [];
        try {
            $response = $this->client->request(
                'GET', $this->generateURL('tickets/' . $ticketID . '/conversations'),
                $this->auth
            );
        } catch (GuzzleException $e) {
            echo $e;
        }

        $comments = json_decode($response->getBody(), true);

        foreach($comments as &$comment) {
            $comment['authorEmail'] = $this->getUserNameAndEmail($comment['user_id'])[1] != null
                ? $this->getUserNameAndEmail($comment['user_id'])[1]
                : $this->getAgentNameAndEmail($comment['user_id'])[1];

            $comment['author_id'] = $this->toZendesk->searchOrCreateUser('', $comment['authorEmail'], 'end-user')['id'];
        }

        return $comments;
    }
}
