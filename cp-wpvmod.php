<?php

/**
 * Plugin Name: WP Version Modifier for CP
 * Plugin URI: https://elite-star-services.com/
 * Version: 1.1.0
 * Requires at least: 4.9
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * Requires CP: 2.0
 * Author: Elite Star Services
 * Network: true
 * Description: Allows Spoofing the WP Version for Installing Plugins & Themes in ClassicPress
 * Text Domain: cwv-textdomain
 *
 * @package VersionModifier
 *
 * @license GPL v3 | https://elite-star-services.com/license/
 **/

/**
 * Plugin Activation
 */
function cwv_activate() {}
register_activation_hook(__FILE__, 'cwv_activate');

/**
 * WP Version Override
 */
function cwv_version_injector()
{
	if (function_exists('classicpress_version')) {

		global $wp_version;
		$options = get_option('cwv_plugin_options');

		if ((isset($options['wp_version'])) && ($options['wp_version'] !== '')) {
			$cwv_ver = $options['wp_version'];
		} else {
			$cwv_ver = $wp_version;
		}

		if (isset($options['cwv_active'])) {
			$wp_version = $cwv_ver; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
add_action('init', 'cwv_version_injector');

/**
 * Create Admin Menu
 */
function cwv_admin_menu()
{
	if (function_exists('classicpress_version')) {
		add_menu_page(
			__('WP Version Modifier', 'cwv-textdomain'),
			__('WP Version', 'cwv-textdomain'),
			'manage_options',
			'cwv-settings',
			'cwv_settings_page',
			'dashicons-wordpress-alt'
		);
	} else {
		add_menu_page(
			__('WP Version Modifier', 'cwv-textdomain'),
			__('WP Version', 'cwv-textdomain'),
			'manage_options',
			'vmod-warn',
			'cwv_warn_page',
			'dashicons-warning'
		);
	}
}

/* DISPLAY SAVE SUCCESS OR ERROR */
if (is_multisite()) {
	add_action('network_admin_menu', 'cwv_admin_menu');
	if (! function_exists('bhna_save_settings_errors')) {
		/**
		 * MULTISITE
		 */
		function bhna_save_settings_errors()
		{
			settings_errors();
		}
		add_action('network_admin_notices', 'bhna_save_settings_errors');
	}
} else {
	add_action('admin_menu', 'cwv_admin_menu');
	if (! function_exists('bh_save_settings_errors')) {
		/**
		 * SINGLE SITE
		 */
		function bh_save_settings_errors()
		{
			settings_errors();
		}
		add_action('admin_notices', 'bh_save_settings_errors');
	}
}


/* OPTION SETTINGS STYLES */
if (isset($_GET['page']) && $_GET['page'] == 'cwv-settings') { // phpcs:ignore
	if (! function_exists('bh_ess_AdminStyles')) {
		/**
		 * Conditional Enqueue Admin
		 */
		function bh_ess_AdminStyles()
		{
			wp_enqueue_style('bh-ess-admin-styles', plugin_dir_url(__FILE__) . 'css/bh-ess-admin.css', array(), '1.0');
		}
		add_action('admin_enqueue_scripts', 'bh_ess_AdminStyles');
	}
	if (! function_exists('bh_ess_global_AdminStyles')) {
		/**
		 * Conditional Enqueue Global Admin
		 */
		function bh_ess_global_AdminStyles()
		{
			wp_enqueue_style('bh-ess-global-admin-styles', plugin_dir_url(__FILE__) . 'css/bh-ess-global-admin.css', array(), '1.0');
		}
		add_action('admin_enqueue_scripts', 'bh_ess_global_AdminStyles');
	}
}


/**
 * PLUGIN SETTINGS
 */
function cwv_register_settings()
{
	global $wp_version;
	register_setting('cwv_plugin_options', 'cwv_plugin_options');
	add_settings_section('api_settings', 'WP Version Modifier', 'cwv_plugin_section_text', 'cwv_plugin');
	add_settings_field('cwv_plugin_setting_wp_version', 'Set WordPress Version<br><small style="font-weight:normal;">Current Value: ' . $wp_version . '</small>', 'cwv_plugin_setting_wp_version', 'cwv_plugin', 'api_settings');
	add_settings_field('cwv_plugin_setting_is_active', 'Activate Version Spoofing', 'cwv_plugin_setting_is_active', 'cwv_plugin', 'api_settings');
}
add_action('admin_init', 'cwv_register_settings');

/**
 * Settings Page Text
 */
function cwv_plugin_section_text()
{
	echo 'Utility to Report an Alternate WordPress Version for ClassicPress v2<br>';
	echo '<em>Aids in the Installation, Upgrade & Testing of Plugins & Themes</em>';
	/* print_r(get_option('cwv_plugin_options')); */ // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
}

/**
 * WP Version Input
 */
function cwv_plugin_setting_wp_version()
{
	$options    = get_option('cwv_plugin_options');
	$cwv_db_ver = '';
	if (is_array($options)) {
		$cwv_db_ver = $options['wp_version'];
	}
	echo "<input id='cwv_plugin_setting_wp_version' name='cwv_plugin_options[wp_version]' type='text' size='10' value='" . esc_attr($cwv_db_ver) . "' />";
}

/**
 * Version Modifier Status
 */
function cwv_plugin_setting_is_active()
{
	$options = get_option('cwv_plugin_options');
	if (is_array($options) && array_key_exists('cwv_active', $options)) {
		$cwv_db_active = esc_attr($options['cwv_active']);
	} else {
		$cwv_db_active = '0';
	}
	echo "<input class='bh-ess-ui-toggle' id='cwv_plugin_setting_is_active' name='cwv_plugin_options[cwv_active]' type='checkbox' value='1'" . checked(1, $cwv_db_active, false) . ' />';
}

/**
 * Settings Page Form
 */
function cwv_settings_page()
{
?>
	<form action="/wp-admin/options.php" method="post">
		<?php
		settings_fields('cwv_plugin_options');
		do_settings_sections('cwv_plugin');
		echo '<hr>';
		?>
		<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Settings', 'cwv-textdomain'); ?>" /> _
		<a class="button-secondary" href="https://elite-star-services.com/">Visit Elite Star Services</a>
	</form>
<?php
}

/**
 * Show WordPress Warning
 */
function cwv_warn_page()
{
	echo '<hr><h3 style="color:red;">WordPress detected - this Plugin is for use with ClassicPress only</h3>';
}

/**
 * Plugin Deactivation
 */
function cwv_deactivate()
{
	delete_option('cwv_plugin_options');
}
register_deactivation_hook(__FILE__, 'cwv_deactivate');


/* Plugin Update Checker if not using ClassicPress Directory Integration Plugin */
if (! function_exists('is_plugin_active')) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}
if (
	version_compare(function_exists('classicpress_version') ? classicpress_version() : '0', '2', '>=') &&
	is_plugin_active('classicpress-directory-integration/classicpress-directory-integration.php')
) {
	return;
}
require 'vendor/bh-update/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$MyUpdateChecker = PucFactory::buildUpdateChecker( // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	'https://cs.elite-star-services.com/wp-repo/?action=get_metadata&slug=cp-wpvmod',
	__FILE__, // Full path to the main plugin file.
	'cp-wpvmod' // Plugin slug. Usually it's the same as the name of the directory.
);
?>