<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

use GFAPI;

class EntryRepository
{
    public static function findByFormId(int $formId): array
    {
        return GFAPI::get_entries(
            $formId,
            [],
            null,
            [
                'page_size' => PHP_INT_MAX,
            ]
        );
    }
}
