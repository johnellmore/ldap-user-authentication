<?php

namespace Ellmore\LDAPUserAuthentication;

/**
 * Handles all communication with the LDAP server.
 */
class LDAPConnection
{
    /**
     * The server URI to connect to. Set on construction.
     */
    private $server;

    /**
     * The designated name (DN) that this user will be present in.
     */
    private $dn;

    /**
     * PHP's LDAP object.
     */
    private $ldap;

    /**
     * Create a instance of the class and create instances of all dependencies.
     */
    public function __construct($server, $dn)
    {
        $this->server = $server;
        $this->dn = $dn;
        $this->ldap = ldap_connect($this->server);
    }

    /**
     * Close the LDAP connection when the script ends.
     */
    public function __destruct()
    {
        ldap_unbind($this->ldap);
    }

    /**
     * Connect to the LDAP server with the provided credentials and report if
     * it was successful or not.
     *
     * @param string $email The email to connect with.
     * @param string $password The password to connect with.
     * @return
     */
    public function authenticate($email, $password)
    {
        // set an error handler to "catch" ldap bind failure
        set_error_handler(function () {
            throw new \Exception();
        });

        try {
            ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
            $success = ldap_bind($this->ldap, $email, $password);
        } catch (\Exception $e) {
            $success = false;
        }

        restore_error_handler();

        return $success;
    }

    /**
     * Retrieve this user's details from the server.
     *
     * @param string $email The email to connect with.
     * @return string[] An indexed array of user attributes from LDAP
     */
    public function getUserInformation($email)
    {
        // set up the query
        $filter = "(&(objectCategory=person)(userprincipalname=$email))";
        $fields = array('givenname', 'sn', 'title', 'cn');

        // find results
        $result = ldap_search($this->ldap, $this->dn, $filter, $fields);
        if (false !== $result) {
            // get the first entry
            $entries = ldap_get_entries($this->ldap, $result);
            if (isset($entries[0])) {
                // normalize the values
                $values = array();
                foreach ($fields as $field) {
                    if (isset($entries[0][$field][0])) {
                        $values[$field] = $entries[0][$field][0];
                    } else {
                        $values[$field] = false;
                    }
                }
                return $values;
            }
        }

        // fallback if something fails
        return false;
    }
}
