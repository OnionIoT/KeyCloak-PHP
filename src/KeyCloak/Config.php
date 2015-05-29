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

	}

	/**
	 * Load configuration from a path.
	 *
	 * @param {String} $config_path Path to a `keycloak.json` configuration.
	 */
	public function load_configuration ($config_path) {

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
		
	}
}