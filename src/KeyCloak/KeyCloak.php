<?php

namespace OnionIoT\KeyCloak;

use OnionIoT\KeyCloak\Config;
use OnionIoT\KeyCloak\GrantManager;

abstract class KeyCloak
{
	public $config;
	public $grant_manager;
	public $store;

	/**
	 * Instantiate a Keycloak.
	 *
	 * The `config` and `keycloakConfig` hashes are both optional.
	 *
	 * The `config` hash, if provided, may include either `store`, pointing
	 * to the actual session-store used by your application, or `cookies`
	 * with boolean `true` as the value to support using cookies as your
	 * authentication store.
	 *
	 * A session-based store is recommended, as it allows more assured control
	 * from the Keycloak console to explicitly logout some or all sessions.
	 *
	 * In all cases, also, authentication through a Bearer authentication
	 * header is supported for non-interactive APIs.
	 *
	 * The `keycloakConfig` object, by default, is populated by the contents of
	 * a `keycloak.json` file installed alongside your application, copied from
	 * the Keycloak administration console when you provision your application.
	 *
	 * @constructor
	 *
	 * @param      {Object}    $config          Configuration for the Keycloak connector.
	 * @param      {Object}    $keycloak_config  Keycloak-specific configuration.
	 *
	 * @return     {Keycloak}  A constructed Keycloak object.
	 *
	 */
    public function __construct ($config, $keycloak_config) {

    }

    /**
	 * Apply protection middleware to an application or specific URL.
	 *
	 * If no `spec` parameter is provided, the subsequent handlers will
	 * be invoked if the user is authenticated, regardless of what roles
	 * he or she may or may not have.
	 *
	 * If a user is not currently authenticated, the middleware will cause
	 * the authentication workflow to begin by redirecting the user to the
	 * Keycloak installation to login.  Upon successful login, the user will
	 * be redirected back to the originally-requested URL, fully-authenticated.
	 *
	 * If a `spec` is provided, the same flow as above will occur to ensure that
	 * a user it authenticated.  Once authenticated, the spec will then be evaluated
	 * to determine if the user may or may not access the following resource.
	 *
	 * The `spec` may be either a `String`, specifying a single required role,
	 * or a function to make more fine-grained determination about access-control
	 *
	 * If the `spec` is a `String`, then the string will be interpreted as a
	 * role-specification according to the following rules:
	 *
	 *  - If the string starts with `realm:`, the suffix is treated as the name
	 *    of a realm-level role that is required for the user to have access.
	 *  - If the string contains a colon, the portion before the colon is treated
	 *    as the name of an application within the realm, and the portion after the
	 *    colon is treated as a role within that application.  The user then must have
	 *    the named role within the named application to proceed.
	 *  - If the string contains no colon, the entire string is interpreted as
	 *    as the name of a role within the current application (defined through
	 *    the installed `keycloak.json` as provisioned within Keycloak) that the
	 *    user must have in order to proceed.
	 *
	 * Example
	 *
	 *     // Users must have the `special-people` role within this application
	 *     app.get( '/special/:page', keycloak.protect( 'special-people' ), mySpecialHandler );
	 *
	 * If the `spec` is a function, it may take up to two parameters in order to
	 * assist it in making an authorization decision: the access token, and the
	 * current HTTP request.  It should return `true` if access is allowed, otherwise
	 * `false`.
	 *
	 * The `token` object has a method `hasRole(...)` which follows the same rules
	 * as above for `String`-based specs.
	 *
	 *     // Ensure that users have either `nicepants` realm-level role, or `mr-fancypants` app-level role.
	 *     function pants(token, request) {
	 *       return token.hasRole( 'realm:nicepants') || token.hasRole( 'mr-fancypants');
	 *     }
	 *
	 *     app.get( '/fancy/:page', keycloak.protect( pants ), myPantsHandler );
	 *
	 * With no spec, simple authentication is all that is required:
	 *
	 *     app.get( '/complain', keycloak.protect(), complaintHandler );
	 *
	 * @param {String} spec The protection spec (optional)
	 */
    public function protect ($spec) {

    }

    /** 
	 * Callback made upon successful authentication of a user.
	 *
	 * By default, this a no-op, but may assigned to another 
	 * function for application-specific login which may be useful
	 * for linking authentication information from Keycloak to 
	 * application-maintained user information.
	 *
	 * The `request.auth.grant` object contains the relevant tokens
	 * which may be inspected.
	 *
	 * For instance, to obtain the unique subject ID:
	 *
	 *     request.auth.grant.id_token.sub => bf2056df-3803-4e49-b3ba-ff2b07d86995
	 *
	 * @param {Object} request The HTTP request.
	 */
    abstract public function authenticated () {

    }

    /**
	 * Callback made upon successful de-authentication of a user.
	 *
	 * By default, this is a no-op, but may be used by the application
	 * in the case it needs to remove information from the user's session
	 * or otherwise perform additional logic once a user is logged out.
	 *
	 * @param {Object} request The HTTP request.
	 */
    abstract public function deauthenticated () {

    }

    /**
	 * Replaceable function to handle access-denied responses.
	 *
	 * In the event the Keycloak middleware decides a user may
	 * not access a resource, or has failed to authenticate at all,
	 * this function will be called.
	 *
	 * By default, a simple string of "Access denied" along with 
	 * an HTTP status code for 403 is returned.  Chances are an
	 * application would prefer to render a fancy template.
	 */
    public function access_denied () {

    }

    protected function get_grant () {

    }

    protected function store_grant () {

    }

    protected function unstore_grant () {

    }

    protected function get_grant_from_code () {

    }

    protected function login_url () {

    }

    protected function logout_url () {

    }

    protected function account_url () {

    }

    protected function get_account () {

    }
}


