<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.nabinkarki2.com.np
 * @since      1.0.0
 *
 * @package    User_Import
 * @subpackage User_Import/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    User_Import
 * @subpackage User_Import/includes
 * @author     Nabin karki <mrkarki2@gmail.com>
 */
class User_Import_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_role( 'customrole', 'Custom Role', get_role( 'subscriber' )->capabilities );
	}

}
