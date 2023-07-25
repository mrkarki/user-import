<?php
class UserImportShortcode
{
    public function __construct()
    {

        add_shortcode('user-lists', array($this, 'user_import_shortcode'));
        $plugin = PLUGIN_PATH;
        add_filter("plugin_action_links_$plugin", array($this, 'my_plugin_settings_link'));
    }

    public function user_import_shortcode($atts)
    {
        ob_start();

        // include template with the arguments (The $args parameter was added in v5.5.0)
        $args = array(
            'role'    => 'customrole',
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        );
        $users = get_users($args);
        if ($users) :
            echo '<div><h2>User Lists</h2>';
            foreach ($users as $user) {
                $user_entry = get_user_meta($user->ID, 'user_extra_entry', true);
                $user_lifestats = get_user_meta($user->ID, 'user_extra_lifestats', true);
                if ($user_entry || $user_lifestats) {
                    echo '<div style="padding-bottom:20px">';
                    if ($user_entry) echo '<p>' . $user_entry . '</p>';
                    if ($user_lifestats) echo '<p>' . $user_lifestats . '</p>';
                    echo '</div>';
                }
            }
            echo '</div>';
        endif;

        return ob_get_clean();
    }

    public function my_plugin_settings_link($links)
    {
        $settings_link = '<a href="options-general.php?page=userimport">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
$UserImportShortcode = new UserImportShortcode();
