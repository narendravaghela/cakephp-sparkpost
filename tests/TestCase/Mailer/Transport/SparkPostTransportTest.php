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

namespace SparkPostEmail\Test\TestCase\Mailer\Transport;

use Cake\Mailer\Email;
use Cake\TestSuite\TestCase;
use SparkPostEmail\Mailer\Transport\SparkPostTransport;

class SparkPostTransportTest extends TestCase
{

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->SparkPostTransport = new SparkPostTransport();
        $this->validConfig = [
            'apiKey' => 'a-valid-api-key'
        ];
        $this->invalidConfig = [
            'apiKey' => ''
        ];
    }

    /**
     * Test configuration
     *
     * @return void
     */
    public function testInvalidConfig()
    {
        $this->setExpectedException('SparkPostEmail\Mailer\Exception\MissingCredentialsException');
        $this->SparkPostTransport->config($this->invalidConfig);

        $email = new Email();
        $email->transport($this->SparkPostTransport);
        $email->from(['support@sparkpostbox.com' => 'CakePHP SparkPost'])
                ->to('test@sparkpostbox.com')
                ->subject('This is test subject')
                ->emailFormat('text')
                ->send('Testing Maingun');
    }

    /**
     * Test required fields
     *
     * @return void
     */
    public function testMissingRequiredFields()
    {
        $this->setExpectedException('BadMethodCallException');
        $this->SparkPostTransport->config($this->validConfig);

        $email = new Email();
        $email->transport($this->SparkPostTransport);
        $email->to('test@sparkpostbox.com')
                ->subject('This is test subject')
                ->emailFormat('text')
                ->send('Testing Maingun');
    }
}
