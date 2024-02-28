<?php

class Task_Manager
{
    public function init($plugin_file)
    {
        // Enable error logging
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);

        // wp_die('test');

        // register_activation_hook($plugin_file, array( $this, 'activate_plugin') );
        $this->create_table();

        add_action('init', array($this, 'register_custom_post_types'));
        add_action( 'save_post', array( $this, 'update_custom_posts' ) );
        add_filter( 'manage_project_posts_columns', [ $this, 'add_custom_columns' ] );
        add_action( 'manage_project_posts_custom_column', [ $this, 'populate_project_custom_columns' ], 10, 2 );



    }

    public function populate_project_custom_columns($col, $post_id) {
        switch($col) {
            case 'project_start_date' : 
                $start_date = get_post_meta($post_id, 'project_start_date', true);
                echo $start_date;
                break;
            case 'client_name' : 
                $client_name = get_post_meta( $post_id, 'client_name', true );
                echo $client_name;
                break;
        }
    }

    public function add_custom_columns($col) {
        // var_dump($col);
        // $col
        // wp_die('test');
        global $post;
        error_log('col '. print_r($col, 1));
        error_log('post '. print_r($post, 1));
        if ( $post->post_type === "project" ) {

            $col['project_start_date'] =  __("Project Start Date");
            $col['client_name']  = __("Client Name");
        }
        return $col;
    }

    public function update_custom_posts( $post_id ) {
        // wp_die('test');


        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (!current_user_can('edit_post', $post_id)) return;

        // wp_die($_POST['client_name']);
        if ( isset($_POST['client_name']) ) {
            update_post_meta( $post_id, 'client_name', sanitize_text_field( $_POST['client_name'] ) );
        }

        if ( isset( $_POST['project_start_date'] ) ) {
            update_post_meta( $post_id, 'project_start_date', sanitize_text_field( $_POST['project_start_date'] ) );
        }
    }

    public function register_custom_post_types()
    {
        register_post_type(
            'project',
            array(
                'labels' => [
                    'name' => __('Projects'),
                    'singular_name' => __('Project'),
                    'add_new'   =>  __("Add New Project"),
                ],
                'public' => true,
                'has_archive' => true,
                'menu_position' => 5, // Position in the main menu
                'menu_icon' => 'dashicons-portfolio', // Icon for the menu item
                'register_meta_box_cb'  =>   [$this, 'register_custom_meta_box_product'], // Corrected callback method name
            )
        );
        

        register_post_type(
            'task',
            array(
                'labels' => array(
                    'name' => __('Tasks'),
                    'singular_name' => __('Task'),
                    'add_new'   =>  __("Add New Task")

                ),
                'public' => true,
                'has_archive' => true,
                'show_in_menu' => 'edit.php?post_type=project', // Show as submenu of 'Project' menu
            )
        );

    }

    public function register_custom_meta_box_product() {
        add_meta_box('project_meta_box', __('Project Details'), array($this, 'display_project_meta_boxes'), 'project', 'normal', 'default');
    }

    public function display_project_meta_boxes($post) {
        // var_dump($post);
        // wp_die('test');
        $client_name = get_post_meta($post->ID, 'client_name', true);
        $start_date = get_post_meta($post->ID, 'project_start_date', true);
        $project_bg = get_post_meta( $post->ID, 'project_bg', true );
        ?>
        <label for="client_name">Client Name</label>
        <input type="text" id="client_name" name="client_name" value="<?php echo esc_attr( $client_name ) ?>"><br><br>

        <label>Project/Client background</label>
        <?php
        wp_editor( $project_bg, 'project_bg', [
            'textarea_name' =>  'project_bg',
            'textarea_rows' =>  10
        ] )
        ?>
        <br><br>
        <label for="project_start_date">start Date</label>
        <input type="date" id="project_start_date" name="project_start_date" value="<?php echo esc_attr( $start_date ) ?>">
        <br><br>
        <?php

    }
    public function activate_plugin()
    {

        $this->create_table();

    }

    public function create_table()
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
        var_dump('here2');

        global $wpdb;

        $table_name = $wpdb->prefix . 'project_and_task_management';

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' (
            id int NOT NULL AUTO_INCREMENT,
            project_id int,
            task_id int,
            user_id int,
            PRIMARY KEY(id)
        )';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}