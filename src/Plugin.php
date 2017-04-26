<?php

namespace Ellmore\LDAPUserAuthentication;

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
     * Create a instance of the class and create instances of all dependencies.
     */
    private function __construct()
    {
        // silence for now
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
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        // echo 'heyo';
    }
}
