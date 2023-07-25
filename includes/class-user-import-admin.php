<?php
class UserImportAdmin
{
    public function __construct()
    {

        add_action('admin_menu', array($this, 'userimport_add_settings_page'));
        add_action('admin_init', array($this, 'userimport_register_settings'));
        add_action('show_user_profile', array($this, 'extra_user_profile_fields'), 10, 1);
        add_action('edit_user_profile', array($this, 'extra_user_profile_fields'), 10, 1);
        add_action('personal_options_update', array($this, 'save_extra_user_profile_fields'));
        add_action('edit_user_profile_update', array($this, 'save_extra_user_profile_fields'));
        add_action('admin_notices', array($this, 'save_extra_user_notice'));
    }
    //add notice message
    public function save_extra_user_notice()
    {

        if (isset($_POST['option_page'])) :
?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('User Data Imported :) ', 'sample-text-domain'); ?></p>
            </div>
        <?php
        endif;
    }
    //register the option menu 
    public function userimport_add_settings_page()
    {
        add_options_page('UserImport', 'UserImport', 'manage_options', 'userimport', array($this, 'userimport_render_plugin_settings_page'));
    }

    //render the option page
    public function userimport_render_plugin_settings_page()
    {
        if (!isset($_POST['option_page'])) {
        ?>

            <form action="options-general.php?page=userimport" method="post" enctype="multipart/form-data">
                <?php
                settings_fields('userimport_plugin_options');
                do_settings_sections('userimport'); ?>
                <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Start importing'); ?>" />
            </form>


        <?php
        }

        //if post take action
        if (isset($_POST['option_page'])) {
            $del = [];
            //if delete user
            $replace_user = isset($_POST['userimport_plugin_options']['setting_delete']) ? $_POST['userimport_plugin_options']['setting_delete'] : '';
            if ($replace_user == 'enable') {
                $args = array(
                    'role'    => 'customrole',
                    'orderby' => 'user_nicename',
                    'order'   => 'ASC'
                );
                $users = get_users($args);
                foreach ($users as $user) {
                    $del[] = $user->user_email;
                    wp_delete_user($user->ID);
                }
            }

            $csv = array();

            // check there are no errors
            if ($_FILES['userimport_plugin_options']['error']['select_file'] == 0) {
                $name = $_FILES['userimport_plugin_options']['name']['select_file'];
                $ext = strtolower(end(explode('.', $_FILES['userimport_plugin_options']['name']['select_file'])));
                $type = $_FILES['userimport_plugin_options']['type']['select_file'];
                $tmpName = $_FILES['userimport_plugin_options']['tmp_name']['select_file'];
                // check the file is a csv
                if ($ext === 'csv') {
                    if (($handle = fopen($tmpName, 'r')) !== FALSE) {
                        // necessary if a large csv file
                        set_time_limit(0);

                        $row = 0;

                        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                            // number of fields in the csv
                            $col_count = count($data);

                            // get the values from the csv
                            $csv[$row]['col1'] = $data[0];
                            $csv[$row]['col2'] = $data[1];
                            $csv[$row]['col3'] = $data[2];
                            $csv[$row]['col4'] = $data[3];
                            $csv[$row]['col5'] = $data[4];
                            // inc the row
                            $row++;
                        }
                        fclose($handle);
                    }
                }
            }

            //render the csv file 
            $success = [];
            $error = [];
            $success1 = [];
            if ($csv) {
                $i = 0;
                $_SESSION['custom_logs'] = '';
                foreach ($csv as $single) {
                    $i++;
                    if ($i == 1) continue;
                    $username = strtolower($single['col2']);
                    $userid = $single['col1'];
                    $email = sanitize_email(strtolower($single['col2']));
                    $full_name = strtolower($single['col3']);
                    $lifestats = strtolower($single['col4']);
                    $sort = strtolower($single['col5']);
                    $password = wp_generate_password();
                    if ($email) {
                        if (!email_exists($email)) {
                            $user_id = wp_create_user($username, $password, $email);
                            $user = get_user_by('id', $user_id);
                            $user->add_role('customrole');
                            update_user_meta($user_id, 'user_extra_id', $userid);
                            update_user_meta($user_id, 'user_extra_entry', $full_name);
                            update_user_meta($user_id, 'user_extra_lifestats', $lifestats);
                            update_user_meta($user_id, 'user_extra_sort', $sort);
                            $success[] = $email . ' is inserted successfully';
                        } else {
                            $replace_user = isset($_POST['userimport_plugin_options']['setting_replace']) ? $_POST['userimport_plugin_options']['setting_replace'] : '';
                            if ($replace_user == 'enable') {
                                $user_id = email_exists($email);
                                update_user_meta($user_id, 'user_extra_id', $userid);
                                update_user_meta($user_id, 'user_extra_entry', $full_name);
                                update_user_meta($user_id, 'user_extra_lifestats', $lifestats);
                                update_user_meta($user_id, 'user_extra_sort', $sort);
                                $success1[] = $email . ' is update successfully';
                            }
                        }
                    } else {
                        $error[] = $single['col2'] . ' is invalid email address... Skiping';
                    }
                }
            }

            //show the result after imported
            if ($del) {
                $d = 1;
                echo '<div class"custom-table">';
                echo '<h3>Removed rows..</h3>';
                foreach ($del as $info) {
                    echo '<p><b>' . $d . '. &nbsp</b>' . $info . '</p>';
                    $d++;
                }
                echo '</div>';
            }
            if ($success) {
                $i = 1;
                echo '<div class"custom-table">';
                echo '<h3>Successfully inserted rows</h3>';
                foreach ($success as $info) {
                    echo '<p><b>' . $i . '. &nbsp</b>' . $info . '</p>';
                    $i++;
                }

                echo '</div>';
            } else {
                echo '<div class"custom-table">';
                echo '<h3>There no new data to insert.</h3>';
                echo '</div>';
            }
            if ($success1) {
                $i = 1;
                echo '<div class"custom-table">';
                echo '<h3>Successfully updated rows</h3>';
                foreach ($success1 as $info) {
                    echo '<p><b>' . $i . '. &nbsp</b>' . $info . '</p>';
                    $i++;
                }
                echo '</div>';
            }
            if ($error) {
                $j = 1;
                echo '<div class"custom-table">';
                echo '<h3>Error rows</h3>';
                foreach ($error as $info) {
                    echo '<p><b>' . $j . '. &nbsp</b>' . $info . '</p>';
                    $j++;
                }
                echo '</div>';
            }

            echo '<br><p><a href="/wp-admin/users.php">Please visit the following page to see the lists of users</a></p>';
            echo '<br><p>Short code <b> [user-lists] </b></a></p>';
        }
    }
    //register the settings
    public function userimport_register_settings()
    {
        register_setting('userimport_plugin_options', 'userimport_plugin_options', array($this, 'userimport_plugin_options_validate'));
        add_settings_section('user_import', 'Import Users', array($this, 'userimport_plugin_section_text'), 'userimport');

        add_settings_field('userimport_plugin_setting_delete', 'Remove old user', array($this, 'userimport_plugin_setting_delete'), 'userimport', 'user_import');
        add_settings_field('userimport_plugin_setting_replace', 'Replace/update old user', array($this, 'userimport_plugin_setting_replace'), 'userimport', 'user_import');

        add_settings_field('userimport_plugin_setting_select_file', 'Upload CSV', array($this, 'userimport_plugin_setting_select_file'), 'userimport', 'user_import');
    }
    //add subtitle
    public function userimport_plugin_section_text()
    {
        echo '<p>Upload the CSV file</p>';
    }
    public function userimport_plugin_setting_delete()
    {
        $options = get_option('userimport_plugin_options');
        $check = '';

        echo "<input id='userimport_plugin_setting_delete' name='userimport_plugin_options[setting_delete]' type='checkbox' value='enable' " . $check . " />";
    }
    public function userimport_plugin_setting_replace()
    {
        $options = get_option('userimport_plugin_options');
        $check = '';

        echo "<input id='userimport_plugin_setting_replace' name='userimport_plugin_options[setting_replace]' type='checkbox' value='enable' " . $check . " />";
    }

    public function userimport_plugin_setting_select_file()
    {
        $options = get_option('userimport_plugin_options');
        echo "<input accept='.csv' id='userimport_plugin_setting_select_file' name='userimport_plugin_options[select_file]' type='file' value='" . esc_attr(isset($options['select_file']) ? $options['select_file'] : '') . "' required/>";
    }

    public function userimport_plugin_options_validate($input)
    {

        return $input;
    }


    //add meta field in profile
    public function extra_user_profile_fields($user)
    { ?>
        <h3><?php _e("Extra user information", "user-import"); ?></h3>

        <table class="form-table">
            <tr>
                <th><label for="user_extra_id"><?php _e("User ID"); ?></label></th>
                <td>
                    <input type="text" name="user_extra_id" id="user_extra_id" value="<?php echo esc_attr(get_the_author_meta('user_extra_id', $user->ID)); ?>" class="regular-text" /><br />

                </td>
            </tr>
            <tr>
                <th><label for="user_extra_entry"><?php _e("Entry"); ?></label></th>
                <td>
                    <input type="text" name="user_extra_entry" id="user_extra_entry" value="<?php echo esc_attr(get_the_author_meta('user_extra_entry', $user->ID)); ?>" class="regular-text" /><br />

                </td>
            </tr>
            <tr>
                <th><label for="user_extra_lifestats"><?php _e("Life Stats"); ?></label></th>
                <td>
                    <input type="text" name="user_extra_lifestats" id="user_extra_lifestats" value="<?php echo esc_attr(get_the_author_meta('user_extra_lifestats', $user->ID)); ?>" class="regular-text" /><br />

                </td>
            </tr>
            <tr>
                <th><label for="user_extra_sort"><?php _e("Sort"); ?></label></th>
                <td>
                    <input type="text" name="user_extra_sort" id="user_extra_sort" value="<?php echo esc_attr(get_the_author_meta('user_extra_sort', $user->ID)); ?>" class="regular-text" /><br />

                </td>
            </tr>

        </table>
<?php }

    //save the meta field data
    public function save_extra_user_profile_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_user_meta($user_id, 'user_extra_id', $_POST['user_extra_id']);
        update_user_meta($user_id, 'user_extra_entry', $_POST['user_extra_entry']);
        update_user_meta($user_id, 'user_extra_lifestats', $_POST['user_extra_lifestats']);
        update_user_meta($user_id, 'user_extra_sort', $_POST['user_extra_sort']);
    }
}
$UserImportAdmin = new UserImportAdmin();
