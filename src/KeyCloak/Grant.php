<?php

namespace OnionIoT\KeyCloak;

class Grant
{
	public $access_token;
	public $refresh_token;
	public $id_token;

	public $token_type;
	public $expires_in;
	public $_raw;

	/**
	 * Construct a new grant.
	 *
	 * The passed in argument may be another `Grant`, or any object with
	 * at least `access_token`, and optionally `refresh_token` and `id_token`,
	 * `token_type`, and `expires_in`.  Each token should be an instance of
	 * `Token` if present.
	 *
	 * If the passed in object contains a field named `__raw` that is also stashed
	 * away as the verbatim raw `String` data of the grant.
	 *
	 * @param {Object} grant The `Grant` to copy, or a simple `Object` with similar fields.
	 *
	 * @constructor
	 */
    public function __construct ($grant_data) {
    	$this->update($grant_data);
    }

    /**
	 * Update this grant in-place given data in another grant.
	 *
	 * This is used to avoid making client perform extra-bookkeeping
	 * to maintain the up-to-date/refreshed grant-set.
	 */
    public function update ($grant_data) {
    	$this->access_token = array_key_exists('access_token', $grant_data) ? $grant_data['access_token'] : '';
		$this->refresh_token = array_key_exists('refresh_token', $grant_data) ? $grant_data['refresh_token'] : '';
		$this->id_token = array_key_exists('id_token', $grant_data) ? $grant_data['id_token'] : '';

		$this->token_type = array_key_exists('token_type', $grant_data) ? $grant_data['token_type'] : 'bearer';
		$this->expires_in = array_key_exists('expires_in', $grant_data) ? $grant_data['expires_in'] : 300;
		$this->_raw = array_key_exists('_raw', $grant_data) ? $grant_data['_raw'] : '';
    }

    /**
	 * Returns the raw String of the grant, if available.
	 *
	 * If the raw string is unavailable (due to programatic construction)
	 * then `undefined` is returned.
	 */
    public function to_string () {
    	return $this->_raw;
    }

    /**
	 * Determine if this grant is expired/out-of-date.
	 *
	 * Determination is made based upon the expiration status of the `access_token`.
	 *
	 * An expired grant *may* be possible to refresh, if a valid
	 * `refresh_token` is available.
	 *
	 * @return {boolean} `true` if expired, otherwise `false`.
	 */
    public function is_expired () {
    	if (!$this->access_token) {
			return TRUE;
		}

		return $this->access_token->is_expired();
    }
}
