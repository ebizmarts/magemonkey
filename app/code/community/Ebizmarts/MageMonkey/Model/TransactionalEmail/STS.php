<?php

/**
 * MailChimp STS (Amazon Simple Email Service) API wrapper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_STS
{
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
	 * Use Secure connection or not
	 *
	 * @var bool
	 */
    public $secure = false;

	/**
	 * STS API URL
	 *
	 * @var string
	 */
    public $apiUrl;

    /**
     * Setup data
     *
     * @param string $apikey Your MailChimp apikey
     * @param string $secure Whether or not this should use a secure connection
     */
    function __construct($apikey, $secure = false)
    {

        $this->secure  = $secure;
		$this->api_key = $apikey;

	    $dc = "us1";
	    if (strstr($this->api_key, "-")){
        	list($key, $dc) = explode("-", $this->api_key, 2);
            if (!$dc){
				$dc = "us1";
			}
        }
        $this->apiUrl = "http://{$dc}.sts.mailchimp.com/{$this->version}/";

    }

	/**
	 * Composes an email message based on input data, and then immediately queues the message for sending.
	 * We manage requeuing for you, so if a message gets rejected for a queueing issue, you've completely blown out your Amazon sending limits.
	 * You are encourage to always make this call with a POST request.
	 *
	 * <Important: If you have not yet requested production access to Amazon SES, then you will only be able to send email to and from verified email addresses.>
	 *
	 *
	 * @param array $message The message data with the following keys:
	 *	string	html	the full HTML content to be sent
	 *	string	text	optional - the full Text content to be sent
	 *	string	subject	the message subject
	 *	string	from_name	the from name to be used
	 *	string	from_email	a verified email address from ListVerifiedEmailAddresses
     *	array	reply_to	option the email address(es) that should be set as reply-to email addresses.
	 *	array	to_email	an array containing up to 50 email addresses to receive this email
	 *	array	to_name	optional - an array of To names to be used. Theses will be processed in order with to_email, so every to_email should have a to_name, even if it is blank.
	 *	array	cc_email	optional - an array containing up to 50 email addresses to receive this email as CC recipients
	 *	array	cc_name	optional - an array of CC names to be used. Theses will be processed in order with to_email, so every to_email should have a to_name, even if it is blank.
	 *	array	bcc_email	optional - an array containing up to 50 email addresses to receive this email as BCC recipients
	 *	array	bcc_name	optional - an array of BCC names to be used. Theses will be processed in order with to_email, so every to_email should have a to_name, even if it is blank.
	 *	bool	autogen_html	optional - if an html section is not passed in, generate it from the text. For historical reasons, this defaults to true.
	 * @param bool $track_opens 	whether or not to turn on MailChimp-specific opens tracking
	 * @param bool $track_clicks 	whether or not to turn on MailChimp-specific click tracking
     * @param array $tags 	an array of strings to tag the message with. Stats can be accumulated using tags, though we only store the first 100 we see, so this should not be unique or change frequently. Tags should be 50 characters or less - any starting with an underscore are reserved and will cause errors.
	 * @return JSON object	containing the "status" as either "sent" or "queued" along with extra info on what happened.
	 */
	public function sendEmail($message, $track_opens = FALSE, $track_clicks = FALSE, $tags = array())
	{

        $params                 = array();
        $params["message"]      = $message;
        $params["track_opens"]  = $track_opens;
        $params["track_clicks"] = $track_clicks;
        $params["tags"]         = $tags;

        return $this->_callServer("sendEmail", $params);

	}

    /**
     * Actually connect to the server and call the requested methods, parsing the result
	 *
	 * @param string $method
	 * @param array $params
	 * @return object|false
     */
    protected function _callServer($method, $params)
    {

        $this->errorMessage = null;
        $this->errorCode    = null;

		$params['apikey'] = $this->api_key;

		$url = $this->apiUrl . $method;

        $curlSession = curl_init();

		curl_setopt($curlSession, CURLOPT_USERAGENT, Mage::helper('monkey')->getUserAgent());
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 0);
        curl_setopt($curlSession, CURLOPT_POST, TRUE);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, TRUE);

        if (TRUE === $this->secure){
			//TODO
			//curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, TRUE);
			//curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, TRUE);
        }

		$result = curl_exec($curlSession);
        if(!$result){
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

		curl_close($curlSession);

		return json_decode($result);

    }

}
