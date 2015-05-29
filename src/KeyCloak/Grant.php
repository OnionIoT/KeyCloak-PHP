<?php

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
    public function __construct ($grant) {
    	$this->update($grant);
    }

    /**
	 * Update this grant in-place given data in another grant.
	 *
	 * This is used to avoid making client perform extra-bookkeeping
	 * to maintain the up-to-date/refreshed grant-set.
	 */
    public function update ($grant) {
    	$this->access_token = $grant->access_token;
		$this->refresh_token = $grant->refresh_token;
		$this->id_token = $grant->id_token;

		$this->token_type = $grant->token_type;
		$this->expires_in = $grant->expires_in;
		$this->_raw = $grant->_raw;
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
			return true;
		}

		return $this->access_token->is_expired();
    }
}
