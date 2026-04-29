<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Dropdown;
use User;

/**
 * Read-only lookup helpers for GLPI User records.
 */
final class UserInfo
{
    public static function displayName(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }
        $user = new User();
        if (!$user->getFromDB($userId)) {
            return '';
        }
        $full = trim(
            (string) ($user->fields['firstname'] ?? '')
            . ' ' . (string) ($user->fields['realname'] ?? '')
        );
        return $full !== '' ? $full : (string) ($user->fields['name'] ?? '');
    }

    public static function titleLabel(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }
        $user = new User();
        if (!$user->getFromDB($userId)) {
            return '';
        }
        $titleId = (int) ($user->fields['usertitles_id'] ?? 0);
        if ($titleId <= 0) {
            return '';
        }
        $label = Dropdown::getDropdownName('glpi_usertitles', $titleId);
        return is_string($label) ? $label : '';
    }
}
