<?php

namespace project;
use GuzzleHttp\Client;
use project\TicketDTO;
/*include_once 'TicketDTO.php';*/

abstract class APIService
{
    protected  $isToken;
    protected  $subdomain;
    protected  $version;
    protected  $username;
    protected  $password;
    protected  $protocol;
    protected  $client;
    protected  $csvHandler;
    protected  $auth;

    public function __construct(bool $isToken, string $subdomain, string $username, string $password)
    {
        $this->isToken = $isToken;
        $this->subdomain = $subdomain;
        $this->version = 'v2/';
        $this->username = $username;
        $this->password = $password;
        $this->protocol = 'https://';
        $this->client = new Client();
        $this->csvHandler = new CSVService();
        $this->csvHandler = new CSVService();
        $this->auth = ['auth' => [$this->username, $this->password]];
    }

   abstract public function saveResponse();


    //abstract public function saveDtoToCSV($data);


    abstract public function getUserNameAndEmail($userID);


    abstract public function getGroupName($groupID);


    abstract public function getOrganizationName($organizationID);


    abstract public function getComments($ticketID);

}
