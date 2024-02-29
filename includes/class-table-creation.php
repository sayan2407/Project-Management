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

        add_filter( 'manage_task_posts_columns', [ $this, 'add_custom_columns_task' ] );
        add_action( 'manage_task_posts_custom_column', [ $this, 'populate_task_custom_columns' ], 10, 2 );



    }

    public function add_custom_columns_task($col) {
        // if ( $post->post_type === "task" ) {
            $col['task_start_date'] = __("Task Start Date");
            $col['project_name']    =   __("Project Name");
            $col['status'] = __("Status");
            $col['assign_to']   = __("Assign To");
        // }
        return $col;
    }

    public function populate_task_custom_columns( $col, $post_id ) {
        $project_task_details = get_post_meta( $post_id, 'project_task_details', true );

        
        switch($col) {
            case 'task_start_date' :
                echo $project_task_details['task_start_date'];
                break;
            case 'project_name' :
                echo get_the_title($project_task_details['project_task']);
                break;

        }
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

        // var_dump($_POST);
        error_log(print_r($_POST, 1));
        $post_type = get_post_type($post_id);

        if ( $post_type == "task" ) {
            $project_task_details = get_post_meta( $post_id, 'project_task_details', true );
            error_log('project_task_details1 ' . print_r($project_task_details, 1));


            if (empty($project_task_details)) {
                error_log('inserting');
                $project_task_details = [];
            }

            error_log('project_task_details ' . print_r($project_task_details, 1));

            if ( isset( $_POST['project_task'] ) ) {
                $project_task_details["project_task"] = $_POST['project_task'];
            }

            if ( isset( $_POST['task_start_date'] ) ) {
                $project_task_details["task_start_date"] = $_POST['task_start_date'];

                // update_post_meta( $post_id, 'task_start_date', $_POST['task_start_date'] );
            }

            if ( isset( $_POST['task_end_date'] ) ) {
                $project_task_details["task_end_date"] = $_POST['task_end_date'];

                // update_post_meta( $post_id, 'task_end_date', $_POST['task_end_date'] );
            }

            if ( isset( $_POST['task_details'] ) ) {
                $project_task_details["task_details"] = $_POST['task_details'];

            }
            error_log('project_task_details_after ' . print_r($project_task_details, 1));


            update_post_meta( $post_id, 'project_task_details', $project_task_details );
        }

        // if ( isset( $_POST['project_task'] ) ) {
        //     error_log('project_task_update ' . $_POST['project_task']);
        //     update_post_meta( $post_id, 'project_task', $_POST['project_task'] );
        // }

        // if ( isset( $_POST['task_start_date'] ) ) {
        //     update_post_meta( $post_id, 'task_start_date', $_POST['task_start_date'] );
        // }

        // if ( isset( $_POST['task_end_date'] ) ) {
        //     update_post_meta( $post_id, 'task_end_date', $_POST['task_end_date'] );
        // }

        // if ( isset( $_POST['task_details'] ) ) {
        //     update_post_meta( $post_id, 'task_details', $_POST['task_details'] );
        // }
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
                'register_meta_box_cb'  =>  [ $this, 'register_custom_meta_box_task' ]
            )
        );

    }

    public function register_custom_meta_box_task() {
        add_meta_box( 'task_meta_box0', __( "Task Details" ), [ $this, 'display_task_details_fields' ], 'task', 'normal', 'default' );
    }

    public function register_custom_meta_box_product() {
        add_meta_box('project_meta_box', __('Project Details'), array($this, 'display_project_meta_boxes'), 'project', 'normal', 'default');
    }

    public function display_task_details_fields($post) {
        $task_start_date = get_post_meta( $post->ID, 'task_start_date', true );
        $task_end_date = get_post_meta( $post->ID, 'task_end_date', true );
        $task_content = get_post_meta( $post->ID, 'task_details', true );
        $project_task = get_post_meta( $post->ID, 'project_task', true );


        // error_log('project_task ' . $project_task);

        $project_task_details = get_post_meta( $post->ID, 'project_task_details', true );

        if ( !isset($project_task_details['task_start_date']) ) {
            $project_task_details['task_start_date'] = '';
        }

        if ( !isset($project_task_details['task_end_date']) ) {
            $project_task_details['task_end_date'] = '';
        }

        if ( !isset($project_task_details['task_details']) ) {
            $project_task_details['task_details'] = '';
        }

        if ( !isset($project_task_details['project_task']) ) {
            $project_task_details['project_task'] = '0';
        }


        $args = [
            'post_type' => 'project',
            'posts_per_page'    =>  -1,
            'post_status'    => 'publish'
        ];

        $all_projects = get_posts( $args );
        // error_log('all_projects '. print_r($all_projects, 1));
        ?>
        <div>
            <div>
            <label for="task_start_date"><strong>Select Start Date</strong></label>
            <input name="task_start_date" id="task_start_date" type="date" value="<?php echo $project_task_details['task_start_date'] ?>">
            </div>
            <div>
            <label for="task_end_date"><strong>Select End Date</strong></label>
            <input name="task_end_date" id="task_end_date" type="date" value="<?php echo $project_task_details['task_end_date'] ?>">
            </div>
            
        </div> <br><br>
        <div>
            <label for="task_details">Enter Task Details</label>
            <?php
            wp_editor( $project_task_details['task_details'], 'task_details', [
                'textarea_name' =>  'task_details',
                'textarea_rows' =>  10
            ] )
            ?>
        </div>

        <div>
            <label><strong>Select a Project</strong></label>
            <select name="project_task" id="project_task" class="project_task">
                <option value="0">select a Project</option>
                <?php 

                foreach( $all_projects as $project ) {
                    ?>
                   <option <?php echo ($project_task_details['project_task'] == $project->ID) ?  "selected" :  ""; ?> value="<?php echo $project->ID ?>"><?php echo $project->post_title ?></option>

                    <?php
                }
                
                ?>
            </select>
        </div>

        <?php
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