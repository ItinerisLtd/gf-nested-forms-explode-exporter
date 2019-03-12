<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

use GF_Field;
use GFAPI;

class FieldRepository
{
    public static function find($formId, $fieldId): ?GF_Field
    {
        $field = GFAPI::get_field($formId, $fieldId);

        return $field instanceof GF_Field
            ? $field
            : null;
    }
}
