<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

use GFAPI;

class FormRepository
{
    public static function all(): array
    {
        $plucked = array_map(function (array $form): array {
            return [
                'id' => (int) ($form['id'] ?? 0),
                'title' => (string) ($form['title'] ?? ''),
                'fields' => (array) ($form['fields'] ?? ''),
            ];
        }, GFAPI::get_forms());

        return array_filter($plucked, function (array $form): bool {
            return $form['id'] > 0 && '' !== $form['title'];
        });
    }
}
