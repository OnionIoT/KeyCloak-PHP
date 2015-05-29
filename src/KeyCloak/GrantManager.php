<?php

namespace OnionIoT\KeyCloak;

use OnionIoT\KeyCloak\Token;
use OnionIoT\KeyCloak\Grant;

class GrantManager {

	public $realm_url;
	public $client_id;
	public $secret;
	public $public_key;
	public $not_before;
	public $public;

	/**
	 * Construct a grant manager.
	 *
	 * @param {Config} $config Config object.
	 *
	 * @constructor
	 */
	public function __construct ($config) {
		$this->realm_url = $config['realm_url'];
		$this->client_id = $config['client_id'];
		$this->secret = $config['secret'];
		$this->public_key = $config['public_key'];
		$this->not_before = 0;
	}

	/**
	 * Use the direct grant API to obtain a grant from Keycloak.
	 *
	 * The direct grant API must be enabled for the configured realm
	 * for this method to work. This function ostensibly provides a
	 * non-interactive, programatic way to login to a Keycloak realm.
	 *
	 * This method can either accept a callback as the last parameter
	 * or return a promise.
	 *
	 * @param {String} $username The username.
	 * @param {String} $password The cleartext password.
	 */
	public function obtain_directly ($username, $password) {
		$url = $this->realm_url . '/tokens/grants/access';

		$headers = array(
		    'Content-type: application/x-www-form-urlencoded'
		);

		$params = array(
			'username' => $username,
			'password' => $password
		);

		if ($this->public) {
			$params['client_id'] = $this->client_id;
		} else {
			array_push($headers, 'Basic ' . base64_encode($this->client_id . ':' . $this->secret));
		}

		// Making POST request to KeyCloak
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_POST, TRUE);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($request);
        $response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        // Shit has failed
        if ($response_code < 200 || $response_code > 299) {
            return FALSE;

        // Success!
        } else {
            try {
            	return $this->create_grant($response);
            } catch (Exception $e) {
            	return FALSE;
            }
        }
	}

	/**
	 * PHP version of Javascript's encodeURIComponent that doesn't covert every character
	 *
	 * @param {String} $str The string to be encoded.
	 */
	private function _encodeURIComponent ($str) {
	    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
	    return strtr(rawurlencode($str), $revert);
	}

	/**
	 * Obtain a grant from a previous interactive login which results in a code.
	 *
	 * This is typically used by servers which receive the code through a
	 * redirect_uri when sending a user to Keycloak for an interactive login.
	 *
	 * An optional session ID and host may be provided if there is desire for
	 * Keycloak to be aware of this information.  They may be used by Keycloak
	 * when session invalidation is triggered from the Keycloak console itself
	 * during its postbacks to `/k_logout` on the server.
	 *
	 * This method returns or promise or may optionally take a callback function.
	 *
	 * @param {String} $code The code from a successful login redirected from Keycloak.
	 * @param {String} $session_id Optional opaque session-id.
	 * @param {String} $session_host Optional session host for targetted Keycloak console post-backs.
	 */
	public function obtain_from_code ($request, $code, $session_id, $session_host) {
		$url = $this->realm_url . '/tokens/access/codes';

		// PHP doesn't have request object, need to pass something in...
		$redirect_uri = $this->_encodeURIComponent(request.session.auth_redirect_uri);

		$params = array(
			'code' => $code,
			'application_session_state' => $session_id,
			'redirect_uri' => $redirect_uri,
			'application_session_host' => $session_host
		);

		$headers = array(
			'Content-Length: ' . strlen($params),
		    'Content-Type: application/x-www-form-urlencoded',
		    'Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->secret)
		);

		// Making POST request to KeyCloak
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_POST, TRUE);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($request);
        $response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        try {
            return $this->create_grant($response);
        } catch (Exception $e) {
        	return FALSE;
        }
	}

	/**
	 * Ensure that a grant is *fresh*, refreshing if required & possible.
	 *
	 * If the access_token is not expired, the grant is left untouched.
	 *
	 * If the access_token is expired, and a refresh_token is available,
	 * the grant is refreshed, in place (no new object is created),
	 * and returned.
	 *
	 * If the access_token is expired and no refresh_token is available,
	 * an error is provided.
	 *
	 * The method may either return a promise or take an optional callback.
	 *
	 * @param {Grant} $grant The grant object to ensure freshness of.
	 */
	public function ensure_freshness ($grant) {
		if ($grant->is_expired()) {
			return FALSE;
		}

		if (!$grant->refresh_token) {
			// Unable to refresh without refresh token
			return FALSE;
		}

		$url = $this->realm_url . '/tokens/refresh';

		$headers = array(
		    'Content-type: application/x-www-form-urlencoded',
		    'Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->secret)
		);

		$params = array(
			'grant_type' => 'refresh_token',
			'refresh_token' => $grant->refresh_token['token']
		);

		// Making POST request to KeyCloak
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_POST, TRUE);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($request);
        curl_close($request);

        try {
			$grant->update($this->createGrant($response));
			return TRUE;
		} catch (Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Perform live validation of an `access_token` against the Keycloak server.
	 *
	 * @param {Token|String} $token The token to validate.
	 *
	 * @return {boolean} FALSE if the token is invalid, or the same token if valid.
	 */
	public function validate_access_token ($token) {
		$url = $this->realm_url . '/tokens/validate';

		// extract the token string
		$token_str = (gettype($token) === 'string') ? $token : $token->token;

		$params = array(
			'access_token' => $token_str
		);

		$url .= http_build_query($params);

		// Making GET request to KeyCloak
		$request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($request);
        curl_close($request);

        $data = json_decode($response, TRUE);

        if ($data->error) {
        	return FALSE;
        } else {
        	return $token;
        }
	}

	/**
	 * Create a `Grant` object from a string of JSON data.
	 *
	 * This method creates the `Grant` object, including
	 * the `access_token`, `refresh_token` and `id_token`
	 * if available, and validates each for expiration and
	 * against the known public-key of the server.
	 *
	 * @param {String} $raw_data The raw JSON string received from the Keycloak server or from a client.
	 * @return {Grant} A validated Grant.
	 */
	public function create_grant ($raw_data) {
		$grant_data = json_decode($raw_data, TRUE);

		$access_token = NULL;
		$refresh_token = NULL;
		$id_token = NULL;

		if ($grant_data->access_token) {
			$access_token = new Token($grant_data->access_token, $this->client_id);
		}

		if ($grant_data->refresh_token) {
			$refresh_token = new Token($grant_data->refresh_token);
		}

		if ($grantData->id_token) {
			$id_token = new Token($grant_data->id_token);
		}

		$grant = new Grant((object)array(
			'access_token' => $access_token,
			'refresh_token' => $refresh_token,
			'id_token' => $id_token,
			'expires_in' => $grant_data->expires_in,
			'token_type' => $grant_data->token_type
		));

		$grant->_raw = $raw_data;

		return $this->validate_grant($grant);
	}

	/**
	 * Validate the grant and all tokens contained therein.
	 *
	 * This method filters a grant (in place), by nulling out
	 * any invalid tokens.  After this method returns, the
	 * passed in grant will only contain valid tokens.
	 *
	 * @param {Grant} The grant to validate.
	 */
	public function validate_grant ($grant) {
		$grant->access_token = $this->validate_token($grant->access_token);
		$grant->refresh_token = $this->validate_token($grant->refresh_token);
		$grant->id_token = $this->validate_token($grant->id_token);

		return grant;
	}

	/**
	 * Validate a token.
	 *
	 * This method accepts a token, and either returns the
	 * same token object, if valid, else, it returns `undefined`
	 * if any of the following errors are seen:
	 *
	 * - The token was undefined in the first place.
	 * - The token is expired.
	 * - The token is not expired, but issued before the current *not before* timestamp.
	 * - The token signature does not verify against the known realm public-key.
	 *
	 * @return {Token} The same token passed in, or `undefined`
	 */
	public function validate_token ($token) {
		if (!$token) {
			return NULL;
		}

		if ($token->is_expired() || $token->content->iat < $this->not_before) {
			return NULL;
		}

		$verify = openssl_verify($token->signed, $this->signature, $this->public_key, OPENSSL_ALGO_SHA256);

        if (!$verify) {
            return NULL;
        } else {
            return $token;
        }
	}

	/**
	 * Get the account information associated with the token
	 *
	 * This method accepts a token, and either returns the
	 * user account information, or it returns NULL
	 * if it encourters error:
	 *
	 * @return {object} An object that contains user account info, or NULL
	 */
	public function get_account ($token) {
		$url = $this->realm_url . '/account';

		// extract the token string
		$token_str = (gettype($token) === 'string') ? $token : $token->token;

		$headers = array(
		    'Authorization: Bearer ' . $token_str,
		    'Accept: application/json'
		);

		// Making GET request to KeyCloak
		$request = curl_init();

        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($request);
        $response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        // Shit has failed
        if ($response_code < 200 || $response_code > 299) {
            return NULL;

        // Success!
        } else {
            $data = json_decode($response, TRUE);

            if ($data['error']) {
            	return NULL;
            } else {
            	return $data;
            }
        }
	}
}



