<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

use GF_Field;
use GFCommon;
use GP_Field_Nested_Form;
use GPNF_Entry;

/**
 * TODO: Refactor!
 */
class EntryTransformer
{
    /**
     * TODO: Refactor!
     */
    public static function toExplodedRows(array $entry): array
    {
        $array = self::toArray($entry);

        $nestedArray = array_filter($array, function ($item): bool {
            return is_array($item) && [] !== $item;
        });
        $nonNestedArray = array_filter($array, function ($item): bool {
            return ! is_array($item);
        });

        if (count($nestedArray) < 1) {
            return [$nonNestedArray];
        }
        if (count($nestedArray) > 1) {
            // TODO: Throw exception instead.
            wp_die('Multiple nested forms found!');
        }

        // TODO: Use PHP 7.3 array_key_first.
        $key = array_keys($nestedArray)[0];
        $nestedEntries = $nestedArray[$key];

        return array_map(function (array $nestedEntry) use ($key, $nonNestedArray): array {
            $prefixedKeys = array_map(function (string $k) use ($key): string {
                return $key . ' ' . $k;
            }, array_keys($nestedEntry));

            $prefixedNestedEntry = array_combine(
                $prefixedKeys,
                $nestedEntry
            );

            return array_merge($nonNestedArray, $prefixedNestedEntry);
        }, $nestedEntries);
    }

    /**
     * TODO: Refactor!
     */
    public static function toArray(array $entry, bool $isParentEntry = true): array
    {
        $transformed = [];

        $transformed = $isParentEntry
            ? array_merge($transformed, self::getBasicInfo($entry))
            : array_merge($transformed, self::getBasicNestedInfo($entry));

        $transformed = array_merge($transformed, self::getNestedUserInputs($entry));
        $transformed = array_merge($transformed, self::getUserInputs($entry));

        return $transformed;
    }

    /**
     * TODO: Refactor!
     */
    protected static function getBasicInfo(array $entry): array
    {
        $columns = array_filter($entry, function ($value, $key): bool {
            return ! is_numeric($key) && 0 !== strncmp($key, 'gpnf_', 5);
        }, ARRAY_FILTER_USE_BOTH);

        return array_map(function ($value): string {
            return (string) $value;
        }, $columns);
    }

    /**
     * TODO: Refactor!
     */
    protected static function getBasicNestedInfo(array $entry): array
    {
        $columns = array_filter($entry, function ($value, $key): bool {
            return in_array($key, ['id', 'form_id'], true);
        }, ARRAY_FILTER_USE_BOTH);

        return array_map(function ($value): string {
            return (string) $value;
        }, $columns);
    }

    /**
     * TODO: Refactor!
     */
    protected static function getUserInputs(array $entry): array
    {
        $transformed = [];

        foreach ($entry as $key => $value) {
            if (! is_numeric($key)) {
                continue;
            }

            $field = FieldRepository::find($entry['form_id'], $key);
            if (! $field instanceof GF_Field) {
                continue;
            }
            if ($field instanceof GP_Field_Nested_Form) {
                continue;
            }

            $title = (string) GFCommon::get_label($field, $key);
            $value = (string) $field->get_value_export($entry, $key, false, true);

            $transformed[$title] = $value;
        }

        return $transformed;
    }

    /**
     * TODO: Refactor!
     */
    protected static function getNestedUserInputs(array $entry): array
    {
        $formId = $entry['form_id'];
        $nestedFields = array_filter($entry, function ($value, $key) use ($formId): bool {
            if (! is_numeric($key)) {
                return false;
            }

            $field = FieldRepository::find($formId, $key);
            return $field instanceof GP_Field_Nested_Form;
        }, ARRAY_FILTER_USE_BOTH);

        if (count($nestedFields) < 1) {
            return [];
        }
        if (count($nestedFields) > 1) {
            // TODO: Throw exception instead.
            wp_die('Multiple nested forms found!');
        }

        // TODO: Use PHP 7.3 array_key_first.
        $key = array_keys($nestedFields)[0];
        $field = FieldRepository::find($formId, $key);

        $nested = new GPNF_Entry($entry);
        $title = (string) GFCommon::get_label($field, $key);

        return [
            $title => array_map(function (array $nestedEntry): array {
                return self::toArray($nestedEntry, false);
            }, $nested->get_child_entries($field->id)),
        ];
    }
}
