<?php

namespace Includes;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package Appointment_Manager
 */
class Deactivator {

	/**
	 *      * Runs the deactivation sequence.
	 * Flushes the rewrite rules to remove any custom rules added by the plugin.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

}