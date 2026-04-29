<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

enum PdfType: string
{
    case VacationRequest     = 'vacation_request';
    case VacationCertificate = 'vacation_certificate';
    case Equipment           = 'equipment';

    public function filename(int $ticketId): string
    {
        return match ($this) {
            self::VacationCertificate => "constancia_vacaciones_{$ticketId}.pdf",
            self::VacationRequest     => "solicitud_vacaciones_{$ticketId}.pdf",
            self::Equipment           => "conformidad_equipo_{$ticketId}.pdf",
        };
    }

    public function documentTitle(int $ticketId): string
    {
        return match ($this) {
            self::VacationCertificate => "Constancia de Vacaciones - Ticket #{$ticketId}",
            self::VacationRequest     => "Solicitud de Vacaciones - Ticket #{$ticketId}",
            self::Equipment           => "Conformidad de Equipo - Ticket #{$ticketId}",
        };
    }

    public function template(): string
    {
        return match ($this) {
            self::VacationCertificate => '@vacationpdf/vacation_certificate.html.twig',
            self::VacationRequest     => '@vacationpdf/vacation_request.html.twig',
            self::Equipment           => '@vacationpdf/equipment.html.twig',
        };
    }
}
