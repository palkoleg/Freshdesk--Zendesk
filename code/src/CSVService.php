<?php
namespace project;
use project\TicketDTO;
/*include_once 'TicketDTO.php';*/

class CSVService
{
    private $filename;

    public function __construct()
    {
        $this->filename = 'tickets.csv';
    }

    public function headersToCSV(): void
    {
        $headers = [
            'Ticket ID',
            'Description',
            'Status',
            'Priority',
            'Agent ID',
            'Agent Name',
            'Agent Email',
            'Contact ID',
            'Contact Name',
            'Contact Email',
            'Group ID',
            'Group Name',
            'Company ID',
            'Company Name',
            'Comments'
        ];

        $fh = fopen($this->filename, 'w');
        fputcsv($fh, $headers);
        fclose($fh);
    }

    /**
     * @param TicketDTO[] $ticketDTOs
     */
    public function saveArrayToCSV(array $ticketDTOs): void
    {
        $fh = fopen($this->filename, 'a');

        foreach ($ticketDTOs as $ticket) {
            fputcsv($fh, [
                $ticket->getTicketID(),
                $ticket->getDescription(),
                $ticket->getStatus(),
                $ticket->getPriority(),
                $ticket->getAgentID(),
                $ticket->getAgentName(),
                $ticket->getAgentEmail(),
                $ticket->getContactID(),
                $ticket->getContactName(),
                $ticket->getContactEmail(),
                $ticket->getGroupID(),
                $ticket->getThisGroupName(),
                $ticket->getCompanyID(),
                $ticket->getCompanyName(),
                $ticket->getThisComments()
            ]);
        }

        fclose($fh);
    }
}
