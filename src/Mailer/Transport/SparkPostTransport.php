<?php
/**
 * SparkPost plugin for CakePHP 3
 * Copyright (c) Narendra Vaghela (http://www.narendravaghela.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.md
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Narendra Vaghela (http://www.narendravaghela.com)
 * @link          https://github.com/narendravaghela/cakephp-sparkpost
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace SparkPostEmail\Mailer\Transport;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Email;
use GuzzleHttp\Client;
use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use SparkPost\SparkPost;
use SparkPostEmail\Mailer\Exception\MissingCredentialsException;
use SparkPostEmail\Mailer\Exception\MissingRequiredFieldException;
use SparkPostEmail\Mailer\Exception\SparkPostApiException;

/**
 * SparkPost Transport class
 *
 * Send email using SparkPost SDK
 */
class SparkPostTransport extends AbstractTransport
{

    /**
     * Default config for this class
     *
     * @var array
     */
    protected $_defaultConfig = [
        'apiKey' => null
    ];

    /**
     * CakePHP Email object
     *
     * @var object Cake\Mailer\Email
     */
    protected $_cakeEmail;

    /**
     * SparkPost Class Object
     *
     * @var object SparkPost Class Object
     */
    protected $_spObject;

    /**
     * SparkPost options
     *
     * @var array
     */
    protected $_spOptions = [
        'start_time' => 'startTime', // string | \DateTime
        'open_tracking' => 'trackOpens', // boolean
        'click_tracking' => 'trackClicks', // boolean
        'transactional' => 'transactional', // boolean
        'sandbox' => 'sandbox', // boolean
        'campaign' => 'campaign', // string
        'rfc822' => 'rfc822', // string
        'inline_css' => 'inlineCss', // boolean
        'metadata' => 'metadata' // array
    ];

    /**
     * Headers which should be excluded
     *
     * @var type
     */
    protected $_excludeHeaders = [
        'Content-Type',
        'Content-Transfer-Encoding',
        'Date',
        'Message-ID',
        'MIME-Version',
        'Subject',
        'From',
        'To',
        'Reply-To'
    ];

    /**
     * SparkPost message parameters
     *
     * @var array
     */
    protected $_messageParams = [];

    /**
     * Custom option prefix
     *
     * @var string
     */
    protected $_customOptionPrefix = "co:";

    /**
     * Custom header prefix
     *
     * @var string
     */
    protected $_customHeaderPrefix = "ch:";

    /**
     * Send mail
     *
     * @param Email $email Cake Email
     * @return mixed SparkPost result
     */
    public function send(Email $email)
    {
        $this->_setSpObject();

        $this->_cakeEmail = $email;

        if (empty($this->_cakeEmail->subject())) {
            throw new MissingRequiredFieldException(['subject']);
        }

        $this->_messageParams['subject'] = $this->_cakeEmail->subject();

        $this->_processEmailAddresses();

        $headers = $this->_getAdditionalEmailHeaders();
        foreach ($headers as $header => $value) {
            if (in_array($header, $this->_excludeHeaders)) {
                continue;
            }

            if (0 === strpos($header, $this->_customHeaderPrefix) && !is_null($value)) {
                $this->_messageParams['customHeaders'][str_replace($this->_customHeaderPrefix, "", $header)] = $value;
            } elseif (0 === strpos($header, $this->_customOptionPrefix) && isset($this->_spOptions[str_replace($this->_customOptionPrefix, "", $header)]) && !is_null($value)) {
                $this->_messageParams[$this->_spOptions[str_replace($this->_customOptionPrefix, "", $header)]] = $value;
            }
        }

        $emailFormat = $this->_cakeEmail->emailFormat();

        if ('both' == $emailFormat || 'html' == $emailFormat) {
            $this->_messageParams['html'] = $this->_cakeEmail->message(Email::MESSAGE_HTML);
        }

        if ('both' == $emailFormat || 'text' == $emailFormat) {
            $this->_messageParams['text'] = $this->_cakeEmail->message(Email::MESSAGE_TEXT);
        }

        $attachments = $this->_processAttachments();
        if (!empty($attachments)) {
            $this->_messageParams['attachments'] = $attachments;
        }

        return $this->_sendMessage();
    }

    /**
     * Sends mail using SparkPost
     *
     * @return mixed SparkPost response
     */
    protected function _sendMessage()
    {
        try {
            $response = $this->_spObject->transmission->send($this->_messageParams);
        } catch (\SparkPost\APIResponseException $ex) {
            throw new SparkPostApiException([$ex->getAPIMessage(), $ex->getAPIDescription()]);
        }
        $this->_reset();
        return $response;
    }

    /**
     * Returns additional headers set via Email::setHeaders().
     *
     * @return array
     */
    protected function _getAdditionalEmailHeaders()
    {
        return $this->_cakeEmail->getHeaders(['_headers']);
    }

    protected function _processEmailAddresses()
    {
        $from = $this->_cakeEmail->from();
        if (empty($from)) {
            throw new MissingRequiredFieldException('"FROM Address"');
        }

        $this->_messageParams['from'] = is_array($from) ? $from[key($from)] . ':' . key($from) : $from;

        $to = $this->_cakeEmail->to();
        foreach ($to as $email => $name) {
            $address = [];
            if ($name === $email) {
                $address['email'] = $email;
            } else {
                $address = ['name' => $name, 'email' => $email];
            }
            $this->_messageParams['recipients'][] = [
                'address' => $address
            ];
        }

        unset($from);
        unset($to);
    }

    /**
     * Prepares attachments
     *
     * @return array
     */
    protected function _processAttachments()
    {
        $attachments = [];
        foreach ($this->_cakeEmail->attachments() as $name => $file) {
            $attachments[] = [
                'type' => $file['mimetype'],
                'name' => $name,
                'data' => base64_encode(file_get_contents($file['file']))
            ];
        }

        return $attachments;
    }

    /**
     * Sets SparkPost object
     *
     * @throws MissingCredentialsException If API key is missing
     */
    protected function _setSpObject()
    {
        if (empty($this->config('apiKey'))) {
            throw new MissingCredentialsException(['API Key']);
        }

        if (!is_a($this->_spObject, 'SparkPost')) {
            $httpAdapter = new Guzzle6HttpAdapter(new Client());
            $this->_spObject = new SparkPost($httpAdapter, ['key' => $this->config('apiKey')]);
        }
    }

    /**
     * Resets the variables to free memory
     *
     * @return void
     */
    protected function _reset()
    {
        $this->_spObject = null;
        $this->_messageParams = [];
    }
}
