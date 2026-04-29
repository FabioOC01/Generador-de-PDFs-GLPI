<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Ticket;

/**
 * Orchestrates generation of the vacation request / certificate PDF.
 */
final class VacationPdfGenerator
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
        Logger::info("START vacation ticket_id={$ticketId} triggered_by={$triggeredBy}");

        $parser = ContentParser::fromHtml((string) ($ticket->fields['content'] ?? ''));
        $requesterId = (int) ($ticket->fields['users_id_recipient'] ?? 0);

        $vacStart = $parser->date($this->config->labelsVacationStart());
        $vacEnd   = $parser->date($this->config->labelsVacationEnd());
        $obs      = $parser->text($this->config->labelsObservations());
        if ($obs === '') {
            $obs = (string) ($ticket->fields['name'] ?? '');
        }

        $approval = ApprovalService::summarize($ticketId);

        $data = [
            'ticket_id'        => $ticketId,
            'ticket_date'      => (string) ($ticket->fields['date'] ?? ''),
            'employee_name'    => UserInfo::displayName($requesterId),
            'employee_title'   => UserInfo::titleLabel($requesterId),
            'dni'              => $parser->integer($this->config->labelsDni()),
            'start_work'       => $parser->date($this->config->labelsStartWork()),
            'vac_start'        => $vacStart,
            'vac_end'          => $vacEnd,
            'days'             => DateUtils::daysInclusive($vacStart, $vacEnd),
            'obs'              => $obs,
            'approval_status'  => $approval['status']->value,
            'approvers'        => implode(', ', $approval['approvers']),
            'current_date_es'  => DateUtils::formatSpanishLongDate(date('Y-m-d'), $this->config->city()),
            'company_name'     => $this->config->companyName(),
            'city'             => $this->config->city(),
            'year'             => (int) date('Y'),
            'logo_path'        => $this->logoPath(),
        ];

        $type = $approval['status'] === ApprovalStatus::Approved
            ? PdfType::VacationCertificate
            : PdfType::VacationRequest;

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
