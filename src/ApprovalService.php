<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use DBmysql;
use TicketValidation;

/**
 * Aggregates the validations of a ticket into a single ApprovalStatus
 * and a de-duplicated list of approver display names.
 */
final class ApprovalService
{
    /**
     * @return array{status: ApprovalStatus, approvers: list<string>}
     */
    public static function summarize(int $ticketId): array
    {
        /** @var DBmysql $DB */
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['users_id_validate', 'status'],
            'FROM'   => 'glpi_ticketvalidations',
            'WHERE'  => ['tickets_id' => $ticketId],
            'ORDER'  => 'id DESC',
        ]);

        $approvers    = [];
        $hasAccepted  = false;
        $hasRejected  = false;

        foreach ($iterator as $row) {
            $uid = (int) ($row['users_id_validate'] ?? 0);
            $st  = (int) ($row['status'] ?? 0);

            if ($uid > 0) {
                $approvers[] = UserInfo::displayName($uid);
            }
            if ($st === (int) TicketValidation::ACCEPTED) {
                $hasAccepted = true;
            } elseif ($st === (int) TicketValidation::REFUSED) {
                $hasRejected = true;
            }
        }

        $approvers = array_values(array_unique(array_filter($approvers)));

        $status = match (true) {
            $hasRejected => ApprovalStatus::Rejected,
            $hasAccepted => ApprovalStatus::Approved,
            default      => ApprovalStatus::Pending,
        };

        return ['status' => $status, 'approvers' => $approvers];
    }
}
