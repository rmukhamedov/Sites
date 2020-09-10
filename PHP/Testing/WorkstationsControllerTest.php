<?php
/**
 * Created by PhpStorm.
 * User: tannergriffin
 * Date: 10/29/2015
 * Time: 8:18 AM
 */

namespace TestingCenter\Testing;
use TestingCenter\Models\Workstation;
use TestingCenter\Controllers\WorkstationsController;

class WorkstationsControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $controller = new WorkstationsController();
        $uri = array();
        $results = $controller->get($uri);

        $numModels = count($results);
        $this->assertGreaterThan(0, $numModels);

        $tf = array();
        array_push($tf, '0');
        array_push($tf, '1');

        foreach ($results as $model) {
            $this->assertNotEmpty($model);
            $this->assertNotNull($model->Workstation_id);
            $this->assertNotNull($model->Occupied);
            $this->assertNotNull($model->Operational);
        }

        array_push($uri, 111);
        $result = $controller->get($uri);
        $this->assertNotEmpty($result);
        $this->assertEquals(111, $result->Workstation_id);
        $this->assertEquals(1, $result->Operational);
        $this->assertEquals(0, $result->Occupied);

    }


    public function testPost(){
        $controller = new WorkstationsController();
        $uri = array();
        array_push($uri, '1234');
        $results = $controller->post($uri);
        $result = $controller->get($uri);
        $numModels = count($result);
        $controller->delete($uri);

    }


    public function testPut(){
        $controller = new WorkstationsController();
        $uri = array();
        array_push($uri, '235');

        $controller->delete($uri);
        $controller->post($uri);

        $controller->put($uri, $Occupied=0);
        $results = $controller->get($uri);


        $this->assertEquals(235, $results->Workstation_id);
        $this->assertEquals(0, $results->Occupied);


        $controller->put($uri, $Occupied = 0, $Operational=0);
        $results = $controller->get($uri);

        $this->assertEquals(235, $results->Workstation_id);
        $this->assertEquals(0, $results->Operational);
//
        $controller->delete($uri);
    }



    public function testDelete(){
        $controller = new WorkstationsController();
        $uri = array();
        array_push($uri, '1234');
        $controller->post($uri);
        $result = $controller->delete($uri);

        $this->assertEquals($result, 1);
    }



}