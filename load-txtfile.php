<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Load_Txtfile
 *
 * @wordpress-plugin
 * Plugin Name:       WP Custom Plugin Load Textfile
 * Plugin URI:        http://example.com/load-txtfile-uri/
 * Description:       This plugin provides a simple user interface within the Wordpress Admin for loading the provided text file.
 * Version:           1.0.0
 * Author:            Wencheng Li
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       load-txtfile
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LOAD_TXTFILE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-load-txtfile-activator.php
 */
function activate_load_txtfile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-load-txtfile-activator.php';
	Load_Txtfile_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-load-txtfile-deactivator.php
 */
function deactivate_load_txtfile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-load-txtfile-deactivator.php';
	Load_Txtfile_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_load_txtfile' );
register_deactivation_hook( __FILE__, 'deactivate_load_txtfile' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-load-txtfile.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_load_txtfile() {

	$plugin = new Load_Txtfile();
	$plugin->run();

}
run_load_txtfile();


/**
 * *************************************************************************************
 * My Custom Codes for Plugin
 * *************************************************************************************
 */

/**
 * create plugin db table on activation
 */
function create_plugin_database_table()
{
    global $wpdb;
    $tblname = $wpdb->prefix . "loadtxtfile";
	$charset_collate = $wpdb->get_charset_collate();

	# Check to see if the table exists already, if not, then create it
    if ($wpdb->get_var("SHOW TABLES LIKE '$tblname'") != $tblname)
    {
        $sql = "CREATE TABLE $tblname (
        		id mediumint(9) NOT NULL AUTO_INCREMENT,
				loadtime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				txtline varchar(200) DEFAULT '' NOT NULL,
				wordnum  int(11) NOT NULL,
				PRIMARY KEY (id)
				) $charset_collate;";
        require_once(ABSPATH . "/wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, "create_plugin_database_table");

/**
 * include WP_List_Table class in plugin
 */
if (!class_exists('WP_List_Table'))
{
	require_once(ABSPATH . "wp-admin/includes/class-wp-list-table.php");
}

/**
 * init plugin
 */
add_action("admin_menu", "my_plugin_setup_menu");

function my_plugin_setup_menu() {
	add_menu_page("My Plugin Page", "Load-Text-File Plugin", "manage_options", "my-plugin", "plugin_init");
}

function plugin_init() {
	handle_text_load();
	load_db();
?>

	<h1>Hello PlayersVoice!</h1>
	<br>
	<h2>Load a text file</h2>
	<form method="post" enctype="multipart/form-data">
		<input type="file" id="btn_load_txtfile" name="btn_load_txtfile">
		<?php
			submit_button("Load Text");
		?>
	</form>

<?php
}

function handle_text_load() {
	if (isset($_FILES["btn_load_txtfile"]))
	{
		$file_handle = $_FILES["btn_load_txtfile"]["tmp_name"];
		if ($file_handle)
		{
			try {
				# read text file as array
				$my_txt = file($file_handle);

				# save to DB
				save_db($my_txt);

				draw_text_table($my_txt);
			} catch (\Throwable $ex) {
				echo $ex->getMessage();
			}
		}
		else
		{
			echo "<script>alert('Please choose .txt file')</script>";
			return;
		}
	}
}

function load_db() {
	//
}

function save_db($data) {
	global $wpdb;
	$tblname = $wpdb->prefix . "loadtxtfile";

	if (!empty($data))
	{
		try {
			foreach ($data as $line)
			{
				if (str_word_count($line) != 0)
				{
					$wpdb->insert(
						$tblname,
						array(
							"loadtime" => current_time("mysql"),
							"txtline" => mb_strimwidth($line, 0, 200, "..."),
							"wordnum" => str_word_count($line)
						)
					);
				}
			}
		} catch (\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}

function draw_text_table($data) {
	$tbl_html = "";

	if (!empty($data))
	{
		try {
			$tbl_html = "<ul>";
			foreach ($data as $line)
			{
				$tbl_html .= "<li>" . $line . "</li>";
			}
			$tbl_html .= "</ul>";
		} catch (\Throwable $ex) {
			echo $ex->getMessage();
		}
	}

	echo $tbl_html;
}

?>
