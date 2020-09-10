<?php

namespace TestingCenter\Testing;

use TestingCenter\Controllers\ExamsController;
use TestingCenter\Models\Exam;
use TestingCenter\Models\Token;
use TestingCenter\Utilities\Cast;
use TestingCenter\Utilities\Testing;
use TestingCenter\Http\Methods;

class ExamsControllerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_EXAM_NAME = 'UnitTestName';
    private $studentToken;
    private $techToken;
    private $facToken;
    private $url = "http://icarus.cs.weber.edu/~bo20352/TestingCenter/API/v1/exams";

    protected function setUp()
    {
        $token = new Token();
        $this->studentToken = $token->buildToken(Token::ROLE_STUDENT, "generic");
        $this->facToken = $token->buildToken(Token::ROLE_FACULTY, "genericfac");
        $this->techToken = $token->buildToken(Token::ROLE_AIDE, "generictech");

        $this->assertNotEquals("", $this->studentToken, "studentToken creation failed");
        $this->assertNotEquals("", $this->techToken, "techToken creation failed");
        $this->assertNotEquals("", $this->facToken, "facToken creation failed");
    }

    public function testPost()
    {


        $body = "";
        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $body, $this->studentToken, Testing::JSON);

        $this->assertEquals("Non-Faculty members are not allowed to create exams.", $output, "Student Token returned back non-error message");


        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $body, $this->techToken, Testing::JSON);
        $this->assertEquals("Non-Faculty members are not allowed to create exams.", $output, "Tech Token returned back non-error message");


        $jsonObject = '{"exam_name":20,"num_attempts_allowed":3,"faculty_username":"genericfac","time_limit":"60","calculator":"graphing","text_book":true,"scratch_paper":true,"e_book":"UnitTesting101","notes":"2 pages","open_date":"2015-11-03","close_date":"2015-12-31"}';
        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $jsonObject, $this->facToken, Testing::JSON);
        $this->assertEquals("Exam name needs to be a string", $output, "Incorrect error if exam name is not a string");

        $jsonObject = '{"exam_name":"' . self::TEST_EXAM_NAME . '","num_attempts_allowed":3,"time_limit":"60","calculator":"graphing","text_book":true,"scratch_paper":true,"e_book":"UnitTesting101","notes":"2 pages","open_date":"2015-11-03","close_date":"2015-12-31"}';
        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $jsonObject, $this->facToken, Testing::JSON);
        $this->assertEquals("Please enter a faculty username", $output, "Incorrect error if faculty username is not provided");

        $jsonObject = '{"exam_name":"' . self::TEST_EXAM_NAME . '","num_attempts_allowed":3,"faculty_username":"genericfac","time_limit":"60","calculator":"graphing","text_book":true,"scratch_paper":true,"e_book":"UnitTesting101","notes":"2 pages","open_date":"11-03-2015","close_date":"2015-12-31"}';
        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $jsonObject, $this->facToken, Testing::JSON);
        $this->assertEquals("Please match the the format, (YYYY-MM-DD)", $output, "Incorrect error if open date is formatted incorrectly");

        $jsonObject = '{"exam_name":"' . self::TEST_EXAM_NAME . '","num_attempts_allowed":3,"faculty_username":"genericfac","time_limit":"60","calculator":"graphing","text_book":true,"scratch_paper":true,"e_book":"UnitTesting101","notes":"2 pages","open_date":"2015-11-03","close_date":"12-31-2015"}';
        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $jsonObject, $this->facToken, Testing::JSON);
        $this->assertEquals("Close date needs to be a date (YYYY-MM-DD)", $output, "Incorrect error if close date is formatted incorrectly");

        $jsonObject = '{"exam_name":"' . self::TEST_EXAM_NAME . '","num_attempts_allowed":3,"faculty_username":"genericfac","time_limit":"60","calculator":"graphing","text_book":true,"scratch_paper":true,"e_book":"UnitTesting101","notes":"2 pages","open_date":"2015-11-03","close_date":"2015-12-31"}';

        $output = Testing::callAPIOverHTTP($this->url, Methods::POST, $jsonObject, $this->facToken, Testing::JSON);

        $this->assertEquals("null", $output);


    }

    public function testPut()
    {
        $body = "";
        $output = Testing::callAPIOverHTTP($this->url, Methods::PUT, $body, $this->studentToken, Testing::JSON);

        $this->assertEquals("Non-Faculty members are not allowed to edit exams.", $output, "Student Token returned back non-error message");


        $output = Testing::callAPIOverHTTP($this->url, Methods::PUT, $body, $this->techToken, Testing::JSON);
        $this->assertEquals("Non-Faculty members are not allowed to edit exams.", $output, "Tech Token returned back non-error message");

        $output = Testing::callAPIOverHTTP($this->url, Methods::PUT, $body, $this->facToken, Testing::JSON);
        $this->assertEquals("An exam ID must be specified in the URI.", $output);

    }
    /* @depends testPost */
    public function testGet()
    {
        $body = "";
        $output = Testing::callAPIOverHTTP($this->url, Methods::GET, $body, $this->studentToken);
        $this->assertJson($output);
        $jsonObjects = json_decode($output);
        foreach ($jsonObjects as $jsonObject) {
            $exam = Cast::cast('TestingCenter\Models\Exam', $jsonObject);
            if($exam->get("exam_name") == self::TEST_EXAM_NAME) {
                $examID = $exam->get("exam_id");
            }
        }
        $this->assertNotNull($examID, "Couldn't retrieve the posted exam.");

        $output = Testing::callAPIOverHTTP($this->url . "/" . $examID, Methods::GET, $body, $this->studentToken);
        $this->assertJson($output);
        $jsonObject = json_decode($output);
        $exam = Cast::cast('TestingCenter\Models\Exam', $jsonObject);
        $this->assertEquals(self::TEST_EXAM_NAME, $exam->get('exam_name'));

        $output = Testing::callAPIOverHTTP($this->url . "/" . $examID . "/time_limit", Methods::GET, $body, $this->studentToken);
        $output = json_decode($output);
        $this->assertEquals("60", $output);
    }
/** @depends testPost */
    public function testDelete()
    {

    }
}
