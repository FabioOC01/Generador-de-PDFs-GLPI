<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Ticket;

/**
 * Orchestrates generation of the equipment assignment / conformity PDF.
 * The template receives the ticket's raw content so the document reflects
 * whatever fields the requester filled in the ticket form.
 */
final class EquipmentPdfGenerator
{
    public function __construct(
        private readonly PluginConfig $config,
        private readonly PdfBuilder $builder = new PdfBuilder(),
        private readonly DocumentAttacher $attacher = new DocumentAttacher(),
    ) {
    }

    public function generate(Ticket $ticket, int $triggeredBy): bool
    {
        $ticketId = (int) $ticket->getID();
        Logger::info("START equipment ticket_id={$ticketId} triggered_by={$triggeredBy}");

        $requesterId = (int) ($ticket->fields['users_id_recipient'] ?? 0);
        $plain = Text::htmlToPlain((string) ($ticket->fields['content'] ?? ''));
        $approval = ApprovalService::summarize($ticketId);

        $data = [
            'ticket_id'       => $ticketId,
            'ticket_name'     => (string) ($ticket->fields['name'] ?? ''),
            'ticket_date'     => (string) ($ticket->fields['date'] ?? ''),
            'ticket_content'  => $plain,
            'employee_name'   => UserInfo::displayName($requesterId),
            'employee_title'  => UserInfo::titleLabel($requesterId),
            'approval_status' => $approval['status']->value,
            'approvers'       => implode(', ', $approval['approvers']),
            'current_date_es' => DateUtils::formatSpanishLongDate(date('Y-m-d'), $this->config->city()),
            'company_name'    => $this->config->companyName(),
            'city'            => $this->config->city(),
            'logo_path'       => $this->logoPath(),
        ];

        $type = PdfType::Equipment;
        $filename = $type->filename($ticketId);
        $this->builder->render($type, $data, $filename);

        $attached = $this->attacher->attach($ticketId, $filename, $type->documentTitle($ticketId));
        Logger::info("ATTACH ticket_id={$ticketId} type={$type->value} result=" . ($attached ? 'OK' : 'FAIL'));

        return $attached;
    }

    private function logoPath(): ?string
    {
        $path = rtrim(GLPI_PLUGIN_DOC_DIR, DIRECTORY_SEPARATOR)
            . '/vacationpdf/' . $this->config->logoFilename();
        return is_file($path) ? $path : null;
    }
}
