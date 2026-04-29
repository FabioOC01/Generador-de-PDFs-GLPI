<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

enum ApprovalStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
