# WordPress LDAP User Authentication

Invisible WordPress plugin which uses allows LDAP users to authenticate.

## Requirements

You'll need [PHP's LDAP module](http://php.net/manual/en/ldap.installation.php)
compiled and installed on your server.

## Configuration

The plugin is configured using `define()` statements in your site's
`wp-config.php` file. There are three configuration constants that can be defined:

```php
// required; the server to authenticate to
define('EMLDAP_LDAP_SERVER', 'your.ldap.server.net');

// required; the base DN (designated name) of the user search
define('EMLDAP_LDAP_DN', 'DC=yourorghere,DC=net');

// optional; the role that newly created users should be given (default subscriber)
define('EMLDAP_NEW_USER_ROLE', 'editor');

/* That's all, stop editing! Happy blogging. */
```

## Login Process

This plugin allows WordPress to query an LDAP server when users try to login.
The general process goes like this:

1. A visitor attempts to log in with credentials.
2. WordPress attempts to authenticate the user using normal authentication
methods. If this succeeds, the process is over. But if it fails...
3. The plugin attempts to log in to the LDAP server with these credentials. If
this fails, the user log in attempt is rejected. But if it's successful...
4. The plugin looks through the WordPress user table to find a user who has the
credentials' username. If a matching user is found, the visitor is
logged in as this user. If no matching user is found...
5. The plugin creates a new user with the credentials' username and email
address from LDAP. The new user's role is set to *subscriber* by default, but
this can be configured to use a different role. Then the visitor is logged in
as this new user.