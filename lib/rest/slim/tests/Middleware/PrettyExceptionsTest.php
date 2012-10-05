<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     1.6.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());

require_once 'Slim/Middleware/Interface.php';
require_once 'Slim/Middleware/PrettyExceptions.php';
require_once 'Slim/Http/Headers.php';
require_once 'Slim/Http/Response.php';
require_once 'Slim/Log.php';
require_once 'Slim/LogFileWriter.php';

class CustomAppExc {
    function call( &$env ) {
        return array(200, array('Content-Type' => 'text/html'), 'Hello world');
    }
}

class CustomAppWithException {
    function call( &$env ) {
        throw new Exception('Danger, Will Robinson!', 600);
        return array(200, array('Content-Type' => 'text/html'), 'Hello world');
    }
}

class PrettyExceptionsTest extends PHPUnit_Framework_TestCase {
    /**
     * Test middleware returns successful response unchanged
     */
    public function testReturnsUnchangedSuccessResponse() {
        $env = array(); //stub
        $app = new CustomAppExc();
        $mw = new Slim_Middleware_PrettyExceptions($app);
        list($status, $header, $body) = $mw->call($env);
        $this->assertEquals(200, $status);
        $this->assertEquals('Hello world', $body);
    }

    /**
     * Test middleware returns diagnostic screen for error response
     */
    public function testReturnsDiagnosticsForErrorResponse() {
        $env = array(
            'slim.log' => new Slim_Log(new Slim_LogFileWriter(fopen('php://temp', 'w')))
        );
        $app = new CustomAppWithException();
        $mw = new Slim_Middleware_PrettyExceptions($app);
        list($status, $header, $body) = $mw->call($env);
        $this->assertEquals(500, $status);
        $this->assertEquals(1, preg_match('#<h1>Slim Application Error</h1>#', $body));
    }
}