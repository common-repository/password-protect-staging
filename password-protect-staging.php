<?php
/*
Plugin Name: Password Protect Staging
Text Domain: password-protect-staging
Description: Puts a HTTP auth on staging. Just to keep search engines out, passwords in clear text!
Version: 1.0.0
*/

add_action('init', 'password_protect_staging');
function password_protect_staging() {
	$details = get_option( 'password_protect_staging_option' );

	// no user or pw = not protected
	if (empty($details['username']) || empty($details['password'])) {
		return;
	}

	if (!is_admin() && (!isset($_SERVER['PHP_AUTH_USER']) || ($_SERVER['PHP_AUTH_USER'] != $details['username'] ) || ($_SERVER['PHP_AUTH_PW'] != $details['password'] ))) {
	    header('WWW-Authenticate: Basic realm="Staging"');
	    header('HTTP/1.0 401 Unauthorized');
	    echo 'Access denied';
	    exit;
	}
}

class PasswordProtectPagingSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Password Protect Staging',
            'Password Protect Staging',
            'manage_options',
            'password-protect-staging',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'password_protect_staging_option' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Password Protect Staging</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'password_protect_staging_group' );
                do_settings_sections( 'password-protect-staging' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'password_protect_staging_group', // Option group
            'password_protect_staging_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'password_protect_staging_id', // ID
            'Username/Password', // Title
            array( $this, 'print_section_info' ), // Callback
            'password-protect-staging' // Page
        );

        add_settings_field(
            'username', // ID
            'Username', // Title
            array( $this, 'username_callback' ), // Callback
            'password-protect-staging', // Page
            'password_protect_staging_id' // Section
        );

        add_settings_field(
            'password',
            'Password',
            array( $this, 'password_callback' ),
            'password-protect-staging',
            'password_protect_staging_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['username'] ) )
            $new_input['username'] = sanitize_text_field( $input['username'] );

        if( isset( $input['password'] ) )
            $new_input['password'] = sanitize_text_field( $input['password'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function username_callback()
    {
        printf(
            '<input type="text" id="title" name="password_protect_staging_option[username]" value="%s" />',
            isset( $this->options['username'] ) ? esc_attr( $this->options['username']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function password_callback()
    {
        printf(
            '<input type="text" id="password" name="password_protect_staging_option[password]" value="%s" />',
            isset( $this->options['password'] ) ? esc_attr( $this->options['password']) : ''
        );
    }
}

if( is_admin() ) {
    $pps_settings_page = new PasswordProtectPagingSettingsPage();
}
