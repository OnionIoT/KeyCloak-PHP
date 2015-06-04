<?php

namespace OnionIoT\KeyCloak;

class Config {
	/**
	 * Construct a configuration object.
	 *
	 * A configuration object may be constructed with either
	 * a path to a `keycloak.json` file (which defaults to
	 * `$PWD/keycloak.json` if not present, or with a configuration
	 * object akin to what parsing `keycloak.json` provides.
	 *
	 * @param {String|Object} $config Configuration path or details.
	 *
	 * @constructor
	 */
	public function __construct ($config) {
		if (gettype($config) === 'string') {
			$this->load_configuration($config);
		} else {
			$this->configure($config);
		}
	}

	/**
	 * Load configuration from a path.
	 *
	 * @param {String} $config_path Path to a `keycloak.json` configuration.
	 */
	public function load_configuration ($config_path) {
		$json = file_get_contents($config_path);
		$config = json_decode($json, TRUE);
		$this->configure($config);
	}

	/**
	 * Configure this `Config` object.
	 *
	 * This will set the internal configuration details.  The details
	 * may come from a `keycloak.json` formatted object (with names such
	 * as `auth-server-url`) or from an existing `Config` object (using
	 * names such as `authServerUrl`).
	 *
	 * @param {Object} $config The configuration to instill.
	 */
	public function configure ($config) {
		/**
		 * Realm ID
		 * @type {String}
		 */
		$this->realm = $config['realm'];

		/**
		 * Client/Application ID
		 * @type {String}
		 */
		$this->client_id = array_key_exists('resource', $config) ? $config['resource'] : $config['client_id'];

		/**
		 * Client/Application secret
		 * @type {String}
		 */
		$this->secret = array_key_exists('credentials', $config) ? $config['credentials']['secret'] : (array_key_exists('secret', $config) ? $config['secret'] : NULL);

		/**
		 * If this is a public application or confidential.
		 * @type {String}
		 */
		$this->is_public = array_key_exists('public-client', $config) ? $config['public-client'] : FALSE;

		/**
		 * Authentication server URL
		 * @type {String}
		 */
		$this->auth_server_url = $config['auth-server-url'] ? $config['auth-server-url'] : $config['authServerUrl'];

		/**
		 * Root realm URL.
		 * @type {String}
		 */
		$this->realm_url = $this->auth_server_url . '/realms/' . $this->realm;

		/**
		 * Root realm admin URL.
		 * @type {String}
		 */
		$this->realmAdminUrl = $this->auth_server_url . '/admin/realms/' . $this->realm;

		/**
		 * Formatted public-key.
		 * @type {String}
		 */
		$plain_key = $config['realm-public-key'];
		$key_parts = str_split($plain_key, 64);

		$this->public_key = "-----BEGIN PUBLIC KEY-----\n" . implode("\n", $key_parts) . "\n-----END PUBLIC KEY-----\n";
	}
}

