<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Document;
use Document_Item;
use RuntimeException;
use Session;
use Ticket;

/**
 * Wraps a PDF file on disk into a GLPI Document linked to a ticket,
 * then removes the temporary source file.
 */
final class DocumentAttacher
{
    public function attach(int $ticketId, string $tmpFilename, string $docName): bool
    {
        $tmpPath = rtrim(GLPI_TMP_DIR, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $tmpFilename;

        if (!is_file($tmpPath)) {
            throw new RuntimeException("Temporary PDF not found: {$tmpPath}");
        }

        $document = new Document();
        $docId = $document->add([
            'name'              => $docName,
            'users_id'          => Session::getLoginUserID() ?: 0,
            '_filename'         => [$tmpFilename],
            '_prefix_filename'  => [''],
            '_tag_filename'     => [''],
        ]);

        if (!$docId) {
            @unlink($tmpPath);
            return false;
        }

        $linked = (new Document_Item())->add([
            'documents_id' => $docId,
            'items_id'     => $ticketId,
            'itemtype'     => Ticket::class,
        ]);

        @unlink($tmpPath);

        return (bool) $linked;
    }
}
