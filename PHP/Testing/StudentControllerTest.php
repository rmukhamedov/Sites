<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 10/29/2015
 * Time: 7:59 AM
 */



use TestingCenter\Controllers\StudentController;
use TestingCenter\Utilities\Testing;
use TestingCenter\Http\Methods;

class StudentControllerTest extends \PHPUnit_Framework_TestCase
{


    /*
     *
        $token = "";
        $body = "username=generic&password=Hello357";
        $url = "http://icarus.cs.weber.edu/~iamcaptaincode/api/v1/tokens";

        $output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::FORM);

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.

        //$this->assertJsonStringEqualsJsonString(""); //Compare against expected JSON object. You  could also do other tests.
     */


    // This should create a new student in the database
    public function testPost()
    {
        $token = "";
        $body = "username=generic&password=Hello357";
        $url = "http://icarus.cs.weber.edu/~iamcaptaincode/api/v1/tokens";

        $output = Testing::callAPIOverHTTP($url, Methods::POST, $body, $token, Testing::FORM);


        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.

        //$this->assertJsonStringEqualsJsonString(""); //Compare against expected JSON object. You  could also do other tests.



        $controller = new StudentController();

        $wNumber = 01234567;
        $firstName = "Joe";
        $lastName = "Swanson";

        $controller->post($wNumber, $firstName, $lastName);

        $results = $controller->get($wNumber);

        $expected = new Student($wNumber, $firstName, $lastName);

        assertEquals($expected, $results);
    }

    // This should retrieve a student already existing in the database
    public function testGet()
    {
        /*
         * @depends testPost
         */
        $controller = new StudentController();

        $wNumber = 01234567;
        $firstName = "Joe";
        $lastName = "Swanson";

        $results = $controller->get($wNumber);

        $expected = new Student($wNumber, $firstName, $lastName);

        assertEquals($expected, $results);

        // Test if DNE, exists
    }


    // Should update an existing student in the database
    public function testPut()
    {

        $token = "";
        $body = "username=generic&password=Hello357";
        $url = "http://icarus.cs.weber.edu/~iamcaptaincode/api/v1/tokens";

        $output = Testing::callAPIOverHTTP($url, Methods::PUT, $body, $token, Testing::FORM);


        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.


        $controller = new StudentController();

        $wNumber = 00145567;
        $firstName = "Bobby";
        $lastName = "Testerson";

        /*
         * @depends testPost();
         */
        $controller->post($wNumber,$firstName,$lastName);

        $newLastName = "Testy";

        $controller->put($wNumber, $firstName, $newLastName);

        $results = $controller->get($wNumber);

        $expected = new Student($wNumber, $firstName, $newLastName);

        assertEquals($expected, $results);
    }

    public function testDelete()
    {
        $controller = new StudentController();

        $wNumber = 00135265;
        $firstName = "Testing";
        $lastName = "Testwasserson";

        /*
         * @depends testPost();
         */
        $controller->post($wNumber,$firstName,$lastName);

        $student = $controller->get($wNumber);

        $controller->delete($wNumber);

        $otherStudent = $controller->get($wNumber);

        assertEquals(null, $student);
    }
}