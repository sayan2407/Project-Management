<?php

/**
 * Plugin Name: Task Manager
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-table-creation.php';


function initialize_plugin() {
    $task_manager = new Task_Manager();
    $task_manager->init(__FILE__);
}


/**
 * Hooks
 */
add_action( 'plugin_loaded', 'initialize_plugin' );