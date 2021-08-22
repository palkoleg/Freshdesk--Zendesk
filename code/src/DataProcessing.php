<?php

/*include_once 'ZendeskAPIService.php';
include_once 'CSVService.php';*/
namespace project;

use project\ZendeskAPIService, project\CSVService;

class DataProcessing
{
    private $token;
    private $subdomain;
    private $username;
    private $password;

    public function setToken(bool $token)
    {
        $this->token = $token;
    }

    public function setSubdomain(string $subdomain)
    {
        $this->subdomain = $subdomain;
    }

    public function setUserName(string $username)
    {
        $this->username = $username;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getData($helpDeskType, $token, $subdomain, $userName, $password): void
    {
            if($helpDeskType == 1) {
                $this->setToken($token);
                $this->setSubdomain($subdomain);
                $this->setUserName($token ? $userName . '/token' : $userName);
                  $this->setPassword($password);
                $this->processingData($helpDeskType);
            } else if($helpDeskType == 2) {
                $this->setToken($token);
                $this->setSubdomain($subdomain);
                $this->setUserName($userName);
                    $this->setPassword($password);
                $this->processingData($helpDeskType);
            }

    }


    private function processingData($helpDeskType): void
    {
        $apiService = null;
        if($helpDeskType == 1) {
            $apiService = new ZendeskAPIService($this->token, $this->subdomain, $this->username, $this->password);
        } else if($helpDeskType == 2)
        {
            $apiService = new FreshdeskAPIService($this->token, $this->subdomain, $this->username, $this->password);

        }

        $apiService->saveResponse();
        //echo '<b>Successfully saved to CSV file</b>';
    }


}
