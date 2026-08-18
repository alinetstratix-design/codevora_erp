<?php

namespace App\Constants;

class QuotationStatus
{
    const DRAFT = 'Draft';
    const SENT = 'Sent';
    const ACCEPTED = 'Accepted';
    const REJECTED = 'Rejected';

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SENT,
            self::ACCEPTED,
            self::REJECTED,
        ];
    }
}
