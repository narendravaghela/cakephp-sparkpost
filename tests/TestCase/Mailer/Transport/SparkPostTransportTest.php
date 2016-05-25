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
    }
}
