<?php
namespace project;
class TicketDTO
{
    private  $ticketID;
    private  $description;
    private  $status;
    private  $priority;
    private  $agentID;
    private  $agentName;
    private  $agentEmail;
    private  $contactID;
    private  $contactName;
    private  $contactEmail;
    private  $groupID;
    private  $groupName;
    private  $companyID;
    private  $companyName;
    private  $comments;

    public function __construct(int $ticketID, string $description, string $status, string $priority, int $agentID, string $agentName, string $agentEmail, int $contactID, string $contactName, string $contactEmail, int $groupID, string $groupName, int $companyID, string $companyName, string $comments)
    {
        $this->ticketID = $ticketID;
        $this->description = $description;
        $this->status = $status;
        $this->priority = $priority;
        $this->agentID = $agentID;
        $this->agentName = $agentName;
        $this->agentEmail = $agentEmail;
        $this->contactID = $contactID;
        $this->contactName = $contactName;
        $this->contactEmail = $contactEmail;
        $this->groupID = $groupID;
        $this->groupName = $groupName;
        $this->companyID = $companyID;
        $this->companyName = $companyName;
        $this->comments = $comments;
    }

    public function getTicketID(): int
    {
        return $this->ticketID;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getAgentID(): int
    {
        return $this->agentID;
    }

    public function getAgentName(): string
    {
        return $this->agentName;
    }

    public function getAgentEmail(): string
    {
        return $this->agentEmail;
    }

    public function getContactID(): int
    {
        return $this->contactID;
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function getGroupID(): int
    {
        return $this->groupID;
    }

    public function getThisGroupName(): string
    {
        return $this->groupName;
    }

    public function getCompanyID(): int
    {
        return $this->companyID;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getThisComments(): string
    {
        return $this->comments;
    }
}
