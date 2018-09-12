<?php

class Swift_Transport_NewsmanApiTransport implements Swift_Transport
{
	protected $_started = false;

	private $account_id;
	private $api_key;
	private $api_url;


	/**
	 * Creates a new Swift_Transport_NewsmanApi Transport
	 *
	 * @param string     $account_id
	 * @param string     $api_key
	 * @param string     $api_url
	 */
	public function __construct($account_id, $api_key, $api_url = "https://cluster.newsmanapp.com/api/1.0/")
	{
		$this->account_id = $account_id;
		$this->api_key = $api_key;
		$this->api_url = $api_url;
	}

	/**
	 * Create a new Swift_Transport_NewsmanApi instance.
	 *
	 * @param string $account_id
	 * @param string $api_key
	 *
	 * @return self
	 */
	public static function newInstance($account_id, $api_key)
	{
		return new self($account_id, $api_key);
	}


	/**
	 * Test if this Transport mechanism has started.
	 *
	 * @return bool
	 */
	public function isStarted()
	{
		return $this->_started;
	}

	/**
	 * Start this Transport mechanism.
	 */
	public function start()
	{
		$this->_started = true;
	}

	/**
	 * Stop this Transport mechanism.
	 */
	public function stop()
	{
		$this->_started = false;
	}

	/**
	 * Send the given Message.
	 *
	 * Recipient/sender data will be retrieved from the Message API.
	 * The return value is the number of recipients who were accepted for delivery.
	 *
	 * @param Swift_Mime_Message $message
	 * @param string[]           $failedRecipients An array of failures by-reference
	 *
	 * @return int
	 */
	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$failedRecipients = (array) $failedRecipients;

		$recipients = array();

		foreach($message->getTo() as $email => $name)
		{
			$recipients[$email] = $name;
		}
		foreach($message->getCc() as $email => $name)
		{
			$recipients[$email] = $name;
		}
		foreach($message->getBcc() as $email => $name)
		{
			$recipients[$email] = $name;
		}


		$json_data = array(
			"mime_message" => $message->toString(),
			"recipients" => array_keys($recipients),
			"account_id" => $this->account_id,
			"key" => $this->api_key
		);

		$ch = curl_init($this->api_url . "message.send_raw");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$result = @json_decode($response, true);
		if ($http_code != 200)
		{
			if ($http_code == 500 && is_array($result) && array_key_exists("err", $result))
			{
				throw new Swift_TransportException(
					$result["err"]
				);
			} else
			{
				throw new Swift_TransportException(
					"Could not call http method. Response code: $http_code - $result"
				);
			}
		}

		return count($result);
	}

	/**
	 * Register a plugin in the Transport.
	 *
	 * @param Swift_Events_EventListener $plugin
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		// no plugins
	}
}
?>
