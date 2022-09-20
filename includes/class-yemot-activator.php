<?php

/**
 * Fired during plugin activation
 *
 * @link       WebDuck
 * @since      1.0.0
 *
 * @package    Yemot
 * @subpackage Yemot/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Yemot
 * @subpackage Yemot/includes
 * @author     WebDuck <office@webduck.co.il>
 */
class Yemot_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		$args = ["url" => get_site_url()];
		$res = wp_remote_post("http://plugins.webduck.co.il/plugins/yemot/hook_activation.php", array(
			'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
			'body'        => json_encode($args),
			'method'      => 'POST',
			'data_format' => 'body',
		));
	}

}
