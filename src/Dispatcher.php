<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use CommonDBTM;
use Session;
use Ticket;
use TicketValidation;
use Throwable;

/**
 * Hook entry point. Fired on every TicketValidation update; decides
 * whether the change is meaningful and routes to the right generator.
 */
final class Dispatcher
{
    public static function onValidationUpdate(CommonDBTM $item): void
    {
        if (!($item instanceof TicketValidation)) {
            return;
        }

        if (!isset($item->oldvalues['status'])) {
            return;
        }

        $old = (int) $item->oldvalues['status'];
        $new = (int) ($item->fields['status'] ?? 0);

        $terminal = [
            (int) TicketValidation::ACCEPTED,
            (int) TicketValidation::REFUSED,
        ];
        if ($new === $old || !in_array($new, $terminal, true)) {
            return;
        }

        $ticketId = (int) ($item->fields['tickets_id'] ?? 0);
        if ($ticketId <= 0) {
            return;
        }

        $ticket = new Ticket();
        if (!$ticket->getFromDB($ticketId)) {
            Logger::error("Ticket {$ticketId} not found on validation update");
            return;
        }

        try {
            $config = new PluginConfig();
            $triggeredBy = (int) (Session::getLoginUserID() ?: 0);

            if (self::isEquipmentTicket((string) ($ticket->fields['name'] ?? ''), $config)) {
                (new EquipmentPdfGenerator($config))->generate($ticket, $triggeredBy);
                return;
            }

            (new VacationPdfGenerator($config))->generate($ticket, $triggeredBy);
        } catch (Throwable $e) {
            Logger::error("Ticket {$ticketId}: " . $e->getMessage());
        }
    }

    private static function isEquipmentTicket(string $ticketName, PluginConfig $config): bool
    {
        foreach ($config->equipmentKeywords() as $keyword) {
            if (Text::contains($ticketName, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
