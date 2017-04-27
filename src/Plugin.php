<?php

namespace Ellmore\LDAPUserAuthentication;

use WP_User;
use WP_Error;

/**
 * This class initializes all plugin functionality, as well as provides a place
 * for operations to be centralized. It's a singleton class, which will be
 * instantiated when the main plugin file is first loaded.
 */
class Plugin
{
    /**
     * The single instance of this class.
     */
    private static $instance;

    /**
     * The object which handles plugin settings.
     */
    private $config;

    /**
     * Create a instance of the class and create instances of all dependencies.
     */
    private function __construct()
    {
        $this->config = new Config('emldap');
    }

    /**
     * Retrieve the singleton's instance.
     *
     * @return Plugin
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Attach all plugin hooks.
     *
     * @return void
     */
    public function bootstrap()
    {
        // handle authentication requests AFTER the main authentication hooks
        add_action('authenticate', array($this, 'authenticate'), 50, 3);
    }

    /**
     * Handles authentication requests made during the WordPress login.
     *
     * Hooked to the `authenticate` hook. Some of the initial logic has been
     * pulled from core.
     * @see wp_authenticate_username_password()
     *
     * @param WP_User|WP_Error|null $user WP_User or WP_Error object from a previous callback. Default null.
     * @param string $username Username for authentication.
     * @param string $password Password for authentication.
     * @return WP_User|WP_Error WP_User on success, WP_Error if the user is considered a spammer.
     */
    public function authenticate($user, $email, $password)
    {
        // if the user's already authenticate, pass them through
        if ($user instanceof WP_User) {
            return $user;
        }

        // handle empty credentials
        if (empty($email) || empty($password)) {
            if (is_wp_error($user)) {
                return $user;
            }

            $error = new WP_Error();

            if (empty($email)) {
                $error->add('empty_username', __('<strong>ERROR</strong>: The email field is empty.'));
            }

            if (empty($password)) {
                $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));
            }

            return $error;
        }

        // let's try to instantiate LDAP
        $server = $this->config->get('ldap_server');
        $dn = $this->config->get('ldap_dn');
        if ($server instanceof WP_Error || $dn instanceof WP_Error) {
            return new WP_Error(
                'invalid_ldap_connection',
                __('<strong>ERROR</strong>: Cannot log in.')
            );
        }

        // now try to log in
        $ldap = new LDAPConnection($server, $dn);
        if (!$ldap->authenticate($email, $password)) {
            return new WP_Error(
                'invalid_ldap_login',
                __('<strong>ERROR</strong>: Cannot log in.')
            );
        }
        // we've successfully authenticated with LDAP

        // see if a user account exists with this username
        $user = get_user_by('email', $email);
        if ($user) {
            // it does--we can log in as this user
            return $user;
        }

        // get the user details from LDAP
        $userDetails = $ldap->getUserInformation($email);
        if (!$userDetails) {
            return new WP_Error(
                'invalid_ldap_user',
                __('<strong>ERROR</strong>: Cannot log in.')
            );
        }

        // a user account doesn't exist, so let's create it
        $password = wp_generate_password();
        $newUserID = wp_create_user($email, $password, $email); // username is the email

        // get the default user role (default subscriber)
        $newUserRole = $this->config->get('new_user_role', 'subscriber');

        // now update the user's details with the info from LDAP
        wp_update_user(array(
            'ID'            => $newUserID,
            'display_name'  => $userDetails['cn'],
            'first_name'    => $userDetails['givenname'],
            'last_name'     => $userDetails['sn'],
            'role'          => $newUserRole,
        ));

        // and load the user object for authentication
        $newUser = get_user_by('id', $newUserID);
        return $newUser;
    }
}
