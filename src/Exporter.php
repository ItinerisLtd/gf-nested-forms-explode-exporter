<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

use League\Csv\CannotInsertRecord;
use League\Csv\Writer;
use Illuminate\Support\Arr;

class Exporter
{
    public static function toCsvString(array ...$explodedRows)
    {
        try {
            $rows = self::normalize(...$explodedRows);
            $headers = array_keys($rows[0]);

            $writer = Writer::createFromString();

            $writer->insertOne($headers);
            $writer->insertAll($rows);

            return $writer->getContent();
        } catch (CannotInsertRecord $e) {
            wp_die(
                esc_html($e->getName())
            );
        }
    }

    protected static function normalize(array ...$rows): array
    {
        $collapsed = Arr::collapse($rows);

        return self::fillKeys($collapsed);
    }

    protected static function fillKeys(array $collapsed): array
    {
        $keys = array_map(function (array $row): array {
            return array_keys($row);
        }, $collapsed);
        $keys = Arr::collapse($keys);
        $keys = array_unique($keys);


        return array_map(function (array $row) use ($keys): array {
            // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
            foreach ($keys as $key) {
                $value = $row[$key] ?? null;

                if (! is_string($value)) {
                    $row[$key] = '';
                }
            }

            ksort($row);

            return $row;
        }, $collapsed);
    }
}
