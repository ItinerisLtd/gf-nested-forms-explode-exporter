<?php
/**
 * Plugin Name:     GF Nested Forms Explode Exporter
 * Plugin URI:      https://github.com/ItinerisLtd/gf-nested-forms-explode-exporter
 * Description:     Exporter gravity forms nested entries.
 * Version:         0.2.0
 * Author:          Itineris Limited
 * Author URI:      https://itineris.co.uk
 * License:         GPL-2.0-or-later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     gf-nested-forms-explode-exporter
 */

declare(strict_types=1);

namespace Itineris\GFNestedFormsExplodeExporter;

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Begins execution of the plugin.
 *
 * @return void
 */
function run()
{
    $plugin = new Plugin();
    $plugin->run();
}

run();
