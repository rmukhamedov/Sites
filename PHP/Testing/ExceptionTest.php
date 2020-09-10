<?php
/**
 * Created by PhpStorm.
 * User: Rachael
 * Date: 10/29/2015
 * Time: 12:08 AM
 */

namespace TestingCenter\Testing;


use TestingCenter\Controllers\ExceptionsController;
use \TestingCenter\Http;
use TestingCenter\Controllers\TokensController;
use TestingCenter\Models\Token;
use TestingCenter\Models\Exception;
use TestingCenter\Http\Methods;
use TestingCenter\Utilities\Testing;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    // Please comment out the unused endpoints but keep them
    //private $endpoint = "http://icarus.cs.weber.edu/~js77236/04/v1/exceptions";
    private $endpoint = "http://icarus.cs.weber.edu/~rs19526/v1/exceptions";
    //private $endpoint = "http://icarus.cs.weber.edu/~js77236/v1/exceptions";

    public function testPost(){
        // Create Faculty token
        // Credentials for faculty
        $token = $this->generateToken('genericfac', 'Hello896');
        $body = '{"student_id":"W111111","exam_id":40,"begin_date":"2001-02-05","end_date":"2001-02-05","time_extension":"Scientific","calculator":null,"notes":null,"scratch_paper":null,"text_book":null,"num_attempts":2,"other":"Hello World"}';
        $url = $this->endpoint;

        $output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $output = json_decode($output, true);

        $this->assertNotEmpty($output);
        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.

        //make sure the data inserted is what was expected
        $this->assertEquals("W111111", $output['student_id']);
        $this->assertEquals("40", $output['exam_id']);

    }

    public function testPut(){
        // Create Faculty token
        // Credentials for faculty
        $token = $this->generateToken('genericfac', 'Hello896');
        $url = $this->endpoint;

        // Add a new exception so that we can edit it.
        $body = '{"student_id":"WPut010","exam_id":1010,"begin_date":"2001-02-05","end_date":"2001-02-05","time_extension":"Scientific","calculator":null,"notes":null,"scratch_paper":null,"text_book":null,"num_attempts":0,"other":"I was made in put"}';
        $output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $output = json_decode($output, true);
        $except = $output['exception_id'];

        // Make an update to the new entry
        $body = '{"exception_id":' . $except . ', "student_id":"WPut010","exam_id":1010,"begin_date":"2010-02-05","end_date":"2016-02-05","time_extension":null,"calculator":"Scientific","notes":"yes","scratch_paper":null,"text_book":null,"num_attempts":15,"other":"I was updated"}';
        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $body, $token, Testing::JSON);
        $output = json_decode($output, true);

        $this->assertNotEmpty($output);
        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.

    }

    public function testDelete(){

        $token = $this->generateToken('genericfac', 'Hello896');
        $url = $this->endpoint;

        // Add a new exception so that we can delete it.
        $body = '{"student_id":"WPut010","exam_id":1010,"begin_date":"2001-02-05","end_date":"2001-02-05","time_extension":"Scientific","calculator":null,"notes":null,"scratch_paper":null,"text_book":null,"num_attempts":0,"other":"I was made in put"}';
        $output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::JSON);
        $output = json_decode($output, true);
        $except = $output['exception_id'];

        //delete the new entry
        $body = '{"exception_id":' . $except . ',"student_id":"WPut010","exam_id":1010}';
        $output = Testing::callAPIOverHTTP($url, Methods::DELETE, $body, $token, Testing::JSON);
        $output = json_decode($output, true);

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.
    }

    public function testGet() {

        $body = $token = $this->generateToken('genericfac', 'Hello896');
        $url = $this->endpoint;

        $output = Testing::callAPIOverHTTP($url, Methods::GET, $body, $token, Testing::JSON);
        $output = json_decode($output, true);

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.

        //creates array of results
        $controller = new ExceptionsController();
        $url = array();
        $results = $controller->get($url);

        //check each individual result for null fields
        foreach ($results as $model) {
            $this->verifyModel($model);
        }
    }

    private function verifyModel($model){
        //Required fields can't be null
        $this->assertNotEmpty($model->exception_id);
        $this->assertNotEmpty($model->student_id);
        $this->assertNotEmpty($model->exam_id);
    }

    private function generateToken($username, $password)
    {
        $_POST['username'] = $username;
        $_POST['password'] = $password;

        $tokenController = new TokensController();
        return $tokenController->post();
    }

}
