<?php
/**
 * Created by PhpStorm.
 * User: Joshua
 * Date: 10/26/2015
 * Time: 1:23 PM
 */

namespace TestingCenter\Testing;


use TestingCenter\Controllers\TokensController;
use TestingCenter\Http\Methods;
use TestingCenter\Models\Token;
use TestingCenter\Utilities\Testing;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function testPostAsStudent()
    {
        $token = $this->generateToken('generic', 'Hello357');

        $this->assertNotNull($token);
        $this->assertEquals(Token::ROLE_STUDENT, Token::getRoleFromToken($token));
    }

    public function testPostAsFaculty()
    {
        $token = $this->generateToken('genericfac', 'Hello896');

        $this->assertNotNull($token);
        $this->assertEquals(Token::ROLE_FACULTY, Token::getRoleFromToken($token));
    }

    public function testPostAsTech()
    {
        $token = $this->generateToken('generictech', 'Hello361');

        $this->assertNotNull($token);
        $this->assertEquals(Token::ROLE_AIDE, Token::getRoleFromToken($token));
    }

    private function generateToken($username, $password)
    {
        $_POST['username'] = $username;
        $_POST['password'] = $password;

        $tokenController = new TokensController();
        return $tokenController->post();
    }

    /**
     *
     */
    public function testCurl()
    {
        $token = "";
        $body = "username=generic&password=Hello357";
        $url = "http://icarus.cs.weber.edu/~iamcaptaincode/api/v1/tokens";

        $output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::FORM);

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.

        //$this->assertJsonStringEqualsJsonString(""); //Compare against expected JSON object. You  could also do other tests.
    }
}