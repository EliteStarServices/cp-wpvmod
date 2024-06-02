<?php
/**
* Plugin Name: WP Version Modifier for CP
* Plugin URI: https://elite-star-services.com/
* Version: 1.0.1
* Requires at least: 4.9
* Tested up to: 6.5
* Requires PHP: 7.4
* Requires CP: 2.0
* Author: Elite Star Services
* Network: true
* Description: Allows Spoofing the WP Version for Installing Plugins & Themes in ClassicPress
* Text Domain: cwv-textdomain
*
	*
	* @License:
	* GPL v3 | https://elite-star-services.com/license/
    *
*
**/


/**
 * Plugin Activation
 *

function cwv_activate() {

}
register_activation_hook( __FILE__, 'cwv_activate' );
*/


function cwv_version_injector() {
    if ( function_exists( 'classicpress_version' ) ) {

        global $wp_version;
        $options = get_option( 'cwv_plugin_options' );

        if ((isset($options['wp_version'])) && ($options['wp_version'] != "")) {
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


// ADMIN AREA MENU
function cwv_admin_menu() {
    if ( function_exists( 'classicpress_version' ) ) {
        add_menu_page(
//        add_submenu_page(
//            'tools.php',
            __( 'WP Version Modifier', 'cwv-textdomain' ),
            __( 'WP Version', 'cwv-textdomain' ),
//            __( 'Compatibility', 'cwv-textdomain' ),
            'manage_options',
            'cwv-settings',
            'cwv_settings_page',
            'dashicons-wordpress-alt'
        );
    } else {
        add_menu_page(
            __( 'WP Version Modifier', 'cwv-textdomain' ),
            __( 'WP Version', 'cwv-textdomain' ),
            'manage_options',
            'vmod-warn',
            'cwv_warn_page',
            'dashicons-warning'
        );
    }
}
if (is_multisite()) {
    add_action( 'network_admin_menu', 'cwv_admin_menu' );
} else {
    add_action( 'admin_menu', 'cwv_admin_menu' );
}

// SETTINGS PAGE
function cwv_register_settings() {
    global $wp_version;
    register_setting( 'cwv_plugin_options', 'cwv_plugin_options' );
    add_settings_section( 'api_settings', 'WP Version Settings', 'cwv_plugin_section_text', 'cwv_plugin' );

//    add_settings_field( 'cwv_plugin_setting_token', 'Security Token<br><small style="font-weight:normal;">Secure Token based on Machine ID</small>', 'cwv_plugin_setting_token', 'cwv_plugin', 'api_settings' );
    add_settings_field( 'cwv_plugin_setting_wp_version', 'Set WordPress Version<br><small style="font-weight:normal;">Current Value: '.$wp_version.'</small>', 'cwv_plugin_setting_wp_version', 'cwv_plugin', 'api_settings' );
    add_settings_field( 'cwv_plugin_setting_is_active', 'Activate Version Spoofing', 'cwv_plugin_setting_is_active', 'cwv_plugin', 'api_settings' );
}
add_action( 'admin_init', 'cwv_register_settings' );

function cwv_plugin_section_text() {
    echo 'Utility to Report an Alternate WordPress Version for ClassicPress v2<br>';
    echo '<em>Aids in the Installation, Upgrade & Testing of Plugins & Themes</em>';
//    print_r(get_option('cwv_plugin_options'));
}

function cwv_plugin_setting_wp_version() {
    $options = get_option( 'cwv_plugin_options' );
    $cwv_db_ver = '';
    if (is_array($options)) {
    	$cwv_db_ver = $options['wp_version'];
    }
     echo "<input id='cwv_plugin_setting_wp_version' name='cwv_plugin_options[wp_version]' type='text' size='10' value='" . esc_attr( $cwv_db_ver ) . "' />";
}

function cwv_plugin_setting_is_active() {
    $options = get_option( 'cwv_plugin_options' );
    if (is_array($options) && array_key_exists('cwv_active', $options)) { $cwv_db_active = esc_attr( $options['cwv_active'] ); } else { $cwv_db_active = '0'; }
        echo "<input id='cwv_plugin_setting_is_active' name='cwv_plugin_options[cwv_active]' type='checkbox' value='1'" . checked( 1, $cwv_db_active, false ) . " />";
}

function cwv_settings_page() {
?>
    <form action="../options.php" method="post">
        <?php
        settings_fields( 'cwv_plugin_options' );
        do_settings_sections( 'cwv_plugin' );
        echo '<hr>';
        ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save Settings', 'cwv-textdomain' ); ?>" /> _
        <a class="button-secondary" href="https://elite-star-services.com/wordpress-development/">Visit Elite Star Services</a>
    </form>
<?php
}


// Show WordPress Warning
function cwv_warn_page() {
    echo '<hr><h3 style="color:red;">WordPress detected - this plugin is for use with ClassicPress only</h3>';
}


/**
 * Plugin Deactivation
 */
function cwv_deactivate() {
    delete_option('cwv_plugin_options');
}
register_deactivation_hook( __FILE__, 'cwv_deactivate' );


// Plugin Update Checker if not using ClassicPress Directory Integration plugin
if ( ! function_exists( 'is_plugin_active' ) ) {
     require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
if (
		version_compare(function_exists('classicpress_version') ? classicpress_version() : '0', '2', '>=') &&
		is_plugin_active('classicpress-directory-integration/classicpress-directory-integration.php')
	) {
	return;
}
require 'vendor/bh-update/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$MyUpdateChecker = PucFactory::buildUpdateChecker(
	'https://cs.elite-star-services.com/wp-repo/?action=get_metadata&slug=cp-wpvmod', //Metadata URL.
	__FILE__, //Full path to the main plugin file.
	'cp-wpvmod' //Plugin slug. Usually it's the same as the name of the directory.
);
?>
