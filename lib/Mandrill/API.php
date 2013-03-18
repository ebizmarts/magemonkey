<?php

/**
 * MailChimp MANDRILL API wrapper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Mandrill_API {

	/**
	 * API version number
	 *
	 * @var string
	 */
    public $version = '1.0';

	/**
	 * Error Message storage
	 *
	 * @var string
	 */
    public $errorMessage;

	/**
	 * Error Code storage
	 *
	 * @var integer
	 */
    public $errorCode;

	/**
	 * Cache the user api_key so we only have to log in once per client instantiation
	 *
	 * @var string MailChimp API key
	 */
    public $api_key;

	/**
	 * STS API URL
	 *
	 * @var string
	 */
    public $apiUrl;

	/**
	 * Request output format
	 *
	 * @var string
	 */
    protected $_output = 'json';

    protected $_attachments = array();

    /**
     * Setup data
     *
     * @param string $apikey Your MailChimp apikey
     * @param string $secure Whether or not this should use a secure connection
     */
    function __construct($apikey = null) {
        if($apikey){
			$this->setApiKey($apikey);
		}
    }

	/**
	 * Api key setter
	 *
	 * @param string $key API Key
	 * @return Ebizmarts_MageMonkey_Model_TransactionalEmail_MANDRILL
	 */
	public function setApiKey($key) {
		$this->api_key = $key;

        $this->apiUrl = "http://mandrillapp.com/api/{$this->version}/";

        return $this;
	}

	/**
	 * ===== Users 유  Calls =====
	 */

	/**
	 * Validate an API key and respond to a ping
	 *
	 */
	public function usersPing() {
        return $this->_callServer("users/ping");
	}

	/**
	 * Return the information about the API-connected user
	 *
	 */
	public function usersInfo() {
        return $this->_callServer("users/info");
	}

	/**
	 * Return the senders that have tried to use this account, both verified and unverified
	 *
	 */
	public function usersSenders() {
        return $this->_callServer("users/senders");
	}

	/**
	 * Disable a sender from being able to send
	 *
	 * @param string $email
	 */
	public function usersDisableSender($email) {
        $params          = array();
        $params["email"] = $email;

        return $this->_callServer("users/disable-sender", $params);
	}

	/**
	 * Send an email to the given address to verify that it is an accepted sender for your Mandrill account.
	 *
	 * @param string $email
	 */
	public function usersVerifySender($email) {
        $params          = array();
        $params["email"] = $email;

        return $this->_callServer("users/verify-sender", $params);
	}

	public function verifyEmailAddress($email) {
		return $this->usersVerifySender($email);
	}

	/**
	 *
	 * ===== Users 유  Calls =====
	 */


	/**
	 * ===== Messages ✐ Calls =====
	 */

	/**
	 * Send a new transactional message through Mandrill
	 *
	 * @param array $message The message data with the following keys:
	 *   string  html	 the full HTML content to be sent
	 *   string  text	 optional full text content to be sent
     *   string  subject	 the message subject
	 *   string  from_email	 the sender email address. If this address has not been verified, the message will be queued and not sent until it is verified
     *   string  from_name	 optional from name to be used
     *   array   to	 an array of email addresses to use as recipients. Each item in the array should be a struct with two keys - email: the email address of the recipient, and name: the optional display name to use for the recipient
     *   struct  headers	 optional extra headers to add to the message (currently only Reply-To and X-* headers are allowed)
     *   boolean track_opens	 whether or not to turn on open tracking for the message
     *   boolean track_clicks	 whether or not to turn on click tracking for the message
     *   array   tags	 an array of string to tag the message with. Stats are accumulated using tags, though we only store the first 100 we see, so this should not be unique or change frequently. Tags should be 50 characters or less. Any tags starting with an understore are reserved for internal use and will cause errors.
	 */
	public function messagesSend($message) {
		$to = array();

		foreach($message['to_email'] as $pos => $email){
			$to []= array(
							'email' => $email,
							'name'  => $message['to_name'][$pos]
						 );
		}
        if(count($this->_attachments)) {
            $message['attachments'] = $this->_attachments;
        }
		$message['to'] = $to;
		unset($message['to_email'], $message['to_name']);

        $params          = array();
        $params["message"] = $message;

        return $this->_callServer("messages/send", $params);
	}

	public function sendEmail($message) {
		return $this->messagesSend($message);
	}

	/**
	 * ===== Messages ✐ Calls =====
	 */


	/**
	 * ===== Tags ✰ Calls =====
	 */

	 /**
	  * Return all of the user-defined tag information
	  *
	  */
	 public function tagsList() {
		return $this->_callServer("tags/list");
	 }

	 /**
	  * Return the recent history (hourly stats for the last 30 days) for a tag
	  *
	  * @param string $tag
	  */
	 public function tagsTimeSeries($tag) {
        $params        = array();
        $params["tag"] = $tag;

		return $this->_callServer("tags/time-series", $params);
	 }

	 /**
	  * Return the recent history (hourly stats for the last 30 days) for all tags
	  *
	  */
	 public function tagsAllTimeSeries() {
		return $this->_callServer("tags/all-time-series");
	 }

	/**
	 * ===== Tags ✰ Calls =====
	 */


	/**
	 * ===== Urls ≎ Calls =====
	 */

	/**
	 * Get the 100 most clicked URLs
	 *
	 */
    public function urlsList() {
		return $this->_callServer("urls/list");
	}

	/**
	 * Return the 100 most clicked URLs that match the search query given
	 *
	 * @param string $query
	 */
    public function urlsSearch($query) {
        $params      = array();
        $params["q"] = $query;

		return $this->_callServer("urls/search", $params);
	}

	 /**
	  * Return the recent history (hourly stats for the last 30 days) for a url
	  *
	  * @param string $url
	  */
	 public function urlsTimeSeries($url) {
        $params        = array();
        $params["url"] = $url;

		return $this->_callServer("urls/time-series", $params);
	 }

	/**
	 * ===== Urls ≎ Calls =====
	 */


    /**
     * Actually connect to the server and call the requested methods, parsing the result
	 *
	 * @param string $method
	 * @param array OPTIONAL $params
	 * @return object|false
     */
    protected function _callServer($method, $params = array()) {

        $this->errorMessage = null;
        $this->errorCode    = null;

		$params['key'] = $this->api_key;

		$url = $this->apiUrl . $method . '.' . $this->_output;

		Mage::helper('mandrill')->log($url, 'MageMonkey_ApiCall.log');
		Mage::helper('mandrill')->log($params, 'MageMonkey_ApiCall.log');

        $curlSession = curl_init();

		curl_setopt($curlSession, CURLOPT_USERAGENT, Mage::helper('mandrill')->getUserAgent());
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 0);
        curl_setopt($curlSession, CURLOPT_POST, TRUE);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, TRUE);
        //curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
        //curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

		$result = curl_exec($curlSession);
        if(!$result){

			$errstr = curl_error($curlSession);
			$errno = curl_errno($curlSession);

			$this->errorMessage = "Could not connect (ERR $errno: $errstr)";
            $this->errorCode = "-99";
            return false;
		}

        // Check that a connection was made
        if (curl_error($curlSession)) {
			$this->errorMessage = curl_error($curlSession);
            $this->errorCode    = "-99";
            return false;
        }

		$httpCode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);

		curl_close($curlSession);

		$resultObject = json_decode($result);

		Mage::helper('mandrill')->log($resultObject, 'MageMonkey_ApiCall.log');

		//You can consider any non-200 HTTP response code an error
		//the returned data will contain more detailed information
		if($httpCode != 200){
			$this->errorMessage = $resultObject->message;
            $this->errorCode    = "-99";
			return false;
		}

		return $resultObject;

    }

    public function tagsInfo($tag) {
        $params        = array();
        $params["tag"] = $tag;

        return $this->_callServer("tags/info", $params);
    }
    public function createAttachment($body,
                                     $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding    = Zend_Mime::ENCODING_BASE64,
                                     $filename    = null)
    {
        $att = array();
        $att['type'] = $mimeType;
        $att['name'] = $filename;
        $att['content'] = base64_encode($body);
        $this->_attachments[] = $att;
        return $att;
    }
}