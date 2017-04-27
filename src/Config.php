<?php

namespace Ellmore\LDAPUserAuthentication;

use WP_Error;

/**
 * Grabs configuration values from the WordPress environment and provides a
 * unified interface for retrieving the plugin to retrieve them.
 */
class Config
{
    /**
     * A unique prefix that will go in front of all settings retrieved.
     */
    protected $prefix;

    /**
     * Create a instance of the class and create instances of all dependencies.
     *
     * @param string $prefix The string to prefix settings with.
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the value for the given configuration directive.
     *
     * This looks through different locations to find the most authoritative
     * answer.
     *
     * @param string $slug The unprefixed setting name.
     * @return mixed|WP_Error
     */
    public function get($slug, $default = null)
    {
        $setting = $this->getSettingFullSlug($slug);

        // first, try retrieving the value from the wp-config define()'s
        $value = $this->getFromWPConfig($setting);

        if ($value instanceof WP_Error) {
            // that didn't work--try getting it from a filter
            $value = $this->getFromFilter($setting);

            if ($value instanceof WP_Error) {
                // that didn't work--try getting it from the settings DB table
                $value = $this->getFromDatabase($setting);
            }
        }

        // use the fallback if given
        if ($value instanceof WP_Error && $default !== null) {
            $value = $default; // default fallback
        }
        return $value;
    }

    /**
     * Get the value of a setting from wp-config.php.
     *
     * @param string $setting The full setting name.
     * @return mixed|WP_Error
     */
    public function getFromWPConfig($setting)
    {
        // get the constant's name
        $constantName = strtoupper($setting);
        if (defined($constantName)) {
            return constant($constantName);
        } else {
            return new WP_Error();
        }
    }

    /**
     * Get the value of a setting by letting plugins/themes set it via filters.
     *
     * @param string $setting The full setting name.
     * @return mixed|WP_Error
     */
    public function getFromFilter($setting)
    {
        // TODO: implement
        return new WP_Error();
    }

    /**
     * Get the value of a setting from the database settings table.
     *
     * @param string $setting The full setting name.
     * @return mixed|WP_Error
     */
    public function getFromDatabase($setting)
    {
        // TODO: implement
        return new WP_Error();
    }

    /**
     * Constructs the setting slug from the prefix and given slug.
     *
     * @param string $slug The setting name.
     * @return string
     */
    private function getSettingFullSlug($slug)
    {
        return strtolower($this->prefix.'_'.$slug);
    }
}
