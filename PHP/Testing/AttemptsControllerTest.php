<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/27/2015
 * Time: 8:10 AM
 */

namespace TestingCenter\Testing;


use TestingCenter\Controllers\AttemptsController;
use TestingCenter\Models\Attempt;
use TestingCenter\Utilities\DatabaseConnection;

class AttemptsControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testPost()
    {
        $dbh  = DatabaseConnection::getInstance();
        $controller = new AttemptsController();
        $uri = json_encode(array("exam_id" => 10,"student_id" => 20,"workstation_id"=> 30,"reservation_id"=>40));
        //$results = $controller->post($uri);
        //array_push($uri, '1234');
        $results = $controller->post($uri);
        $result = $controller->get($uri);
        $numModels = count($result);


        $this->assertEquals($results, $uri);
        //$controller->delete($uri);


    }

    /*
     * @depends testPost
     */

    public function testGet()
    {
        $controller = new AttemptsController();
        $uri = array();
        $results = $controller->get($uri);

        $numModels = count($results);
        $this->assertGreaterThan(0, $numModels);

        foreach($results as $model)
        {
            $this->testModel($model);
        }
    }

    /*
     * @depends testPost
     */

    public function testPut()
    {
        $controller = new AttemptsController();
        $uri = json_encode(array("exam_id" => 10,"student_id" => 20,"workstation_id"=> 30,"reservation_id"=>40));

        $controller->post($uri);
        $controller->put($uri);
        $time= $_SERVER['REQUEST_TIME'];

        $this->assertEquals($time, $this->endTime);

        $controller->delete($uri);


    }

    /*
     * @depends testPost
     */

    public function testDelete()
    {
        $dbh  = DatabaseConnection::getInstance();
        $controller = new AttemptsController();
        $uri = array();
        array_push($uri, '1234');
        $controller->post($uri);
        $results = $controller->delete($uri);

        $this->assertEquals($results, 1);

        /*$controller = new WorkstationsController();
        $uri = array();
        array_push($uri, '1234');
        $controller->post($uri);
        $result = $controller->delete($uri);

        $this->assertEquals($result, 1);*/
    }

    private function testModel(Attempt $model)
    {
        $this->assertNotEmpty($model);
        //$this->assertNotEmpty($model->getAttemptID());
        //$this->assertNotEmpty($model->getAttemptNum());   // This is never passed to us
        //$this->assertNotEmpty($model->getEndTime());      //  Needs to be null-able/empty
        //$this->assertNotEmpty($model->getReservationID());
        //$this->assertNotEmpty($model->getStartTime());
        //$this->assertNotEmpty($model->getStudentID());
        //$this->assertNotEmpty($model->getWorkstationID());
        //$this->assertNotEmpty($model->getExamID());
    }
}
