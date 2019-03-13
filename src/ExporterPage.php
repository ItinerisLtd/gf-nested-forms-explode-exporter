<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

use TypistTech\WPBetterSettings\Builder;
use TypistTech\WPBetterSettings\Registrar;
use TypistTech\WPBetterSettings\Section;
use TypistTech\WPOptionStore\Factory as OptionStoreFactory;

class ExporterPage
{
    protected const SLUG = 'gf-nested-forms-explode-exporter';
    public const FORM_ID_OPTION_ID = Plugin::PREFIX . 'form_id';

    public static function registerSettings(): void
    {
        $builder = new Builder(
            OptionStoreFactory::build()
        );

        $section = new Section(
            'gf_nested_forms_explode_exporter',
            __('Export gravity forms nested entries to CSV file', 'gf-nested-forms-explode-exporter')
        );

        $section->add(
            $builder->select(
                static::FORM_ID_OPTION_ID,
                __('Which form to export?', 'gf-nested-forms-explode-exporter'),
                FormRepository::allAsDropdownChoices()
            )
        );


        $registrar = new Registrar(static::SLUG);
        $registrar->add($section);
        $registrar->run();
    }

    public static function addManagementPage(): void
    {
        add_management_page(
            __('GF Nested Forms Explode Exporter', 'gf-nested-forms-explode-exporter'),
            __('GF Nested Forms Explode Exporter', 'gf-nested-forms-explode-exporter'),
            'manage_options',
            self::SLUG,
            function () {
                echo '<div class="wrap">';
                settings_errors();
                echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
                echo '<form action="options.php" method="post">';
                settings_fields(static::SLUG);
                do_settings_sections(static::SLUG);
                submit_button(
                    __('Export', 'gf-nested-forms-explode-exporter')
                );
                echo '</form>';
                echo '</div>';
            }
        );
    }


    public static function handleFormSubmit()
    {
        $formId = '';
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
        if (isset($_POST[static::FORM_ID_OPTION_ID])) { // Input var okay.
            // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
            $formId = sanitize_text_field(wp_unslash($_POST[static::FORM_ID_OPTION_ID])); // Input var okay.
        }

        if (! is_numeric($formId)) {
            wp_die(
                esc_html__('Form ID is not numeric!', 'gf-nested-forms-explode-exporter')
            );
        }
        $formId = (int) $formId;

        $entries = EntryRepository::findByFormId($formId);
        if (count($entries) < 1) {
            wp_die(
                esc_html__('Entries not found!', 'gf-nested-forms-explode-exporter')
            );
        }

        $explodedRows = array_map(function (array $entry): array {
            return EntryTransformer::toExplodedRows($entry);
        }, $entries);

        $csvString = Exporter::toCsvString(...$explodedRows);

        $filename = 'export-' . time();

        header('Cache-Control: private');
        header('Content-type: application/csv');
        header("Content-disposition: attachment; filename=$filename.csv");

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $csvString;
        exit;
    }
}
