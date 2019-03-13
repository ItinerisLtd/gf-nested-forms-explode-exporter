<?php
declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

class Plugin
{
    public const PREFIX = 'gfnfee_';

    public function run(): void
    {
        add_action('admin_init', [ExporterPage::class, 'registerSettings']);
        add_action('admin_menu', [ExporterPage::class, 'addManagementPage']);

        // Do not save exporter page options.
        add_filter('pre_update_option_' . ExporterPage::FORM_ID_OPTION_ID, '__return_false', 1000);
        add_action('pre_update_option_' . ExporterPage::FORM_ID_OPTION_ID, [ExporterPage::class, 'handleFormSubmit']);
    }
}
