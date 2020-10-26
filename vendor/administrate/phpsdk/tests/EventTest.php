<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Administrate\PhpSdk\Event;

/**
 * EventTest
 *
 * @package Administrate\PhpSdk
 * @author Ali Habib <ahh@administrate.co>
 * @author Jad Khater <jck@administrate.co>
 */
final class EventTest extends TestCase
{
    public function testLoadSingleEvent(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();

        $fields = [];
        $returnType = 'array'; //array, obj, json
        $event = new Event();
        $eventObj = new Event($weblinkActivationParams);
        $eventArray = $eventObj->loadById($_GET['eventId'], $fields, 'array');
        $eventJson = $eventObj->loadById($_GET['eventId'], $fields, 'json');
        $eventObj = $eventObj->loadById($_GET['eventId'], $fields, 'obj');

        //check response is a php array
        $this->assertisArray($eventArray);
        //check response is in json format
        $this->assertTrue($this->is_json($eventJson));
        //check response is a pHP object
        $this->assertisObject($eventObj);
        
        $this->assertArrayHasKey('id', json_decode($eventJson, true), 'The returned json has invalid format');
        $this->assertArrayHasKey('name', json_decode($eventJson, true), 'The returned json has invalid format');
        $this->assertArrayHasKey('id', $eventArray, 'The returned array has invalid format');
        $this->assertArrayHasKey('name', $eventArray, 'The returned array has invalid format');
        $this->assertObjectHasAttribute('id', $eventObj, 'The returned object has invalid format');
        $this->assertObjectHasAttribute('name', $eventObj, 'The returned object has invalid format');
    }

    public function testLoadMultipleEvent(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();
        
        $fields = [];
        $paging = ['page' => 1, 'perPage' => 25];
        $sorting = ['field' => 'title', 'direction' => 'asc'];
        $filters = [];

        $eventObj = new Event($weblinkActivationParams);
        $resultArray = $eventObj->loadAll($filters, $paging, $sorting, $fields, 'array');
        $resultJson = $eventObj->loadAll($filters, $paging, $sorting, $fields, 'json');
        $resultObject = $eventObj->loadAll($filters, $paging, $sorting, $fields, 'obj');

        //check response is a php array
        $this->assertisArray($resultArray);
        //check response is in json format
        $this->assertTrue($this->is_json($resultJson));
        //check response is a pHP object
        $this->assertisObject($resultObject);

        $jsonToArray  = json_decode($resultJson, true);
        $this->assertArrayHasKey('edges', $jsonToArray['events'], 'The returned json has invalid format');
        $this->assertArrayHasKey('pageInfo', $jsonToArray['events'], 'The returned json has invalid format');
        $this->assertArrayHasKey('edges', $resultArray['events'], 'The returned array has invalid format');
        $this->assertArrayHasKey('pageInfo', $resultArray['events'], 'The returned array has invalid format');
        $this->assertObjectHasAttribute('edges', $resultObject->events, 'The returned object has invalid format');
        $this->assertObjectHasAttribute('pageInfo', $resultObject->events, 'The returned object has invalid format');
    }

    public function testLoadEventsbyCourseCode(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();
        
        $fields = [];
        $paging = ['page' => 1, 'perPage' => 25];
        $sorting = ['field' => 'title', 'direction' => 'asc'];
        $filters = ['courseCode' => $_GET['courseCode']];

        $eventObj = new Event($weblinkActivationParams);

        $resultArray = $eventObj->loadByCourseCode($filters, $paging, $sorting, $fields, 'array');
        $resultJson = $eventObj->loadByCourseCode($filters, $paging, $sorting, $fields, 'json');
        $resultObject = $eventObj->loadByCourseCode($filters, $paging, $sorting, $fields, 'obj');

        //check response is a php array
        $this->assertisArray($resultArray);
        //check response is in json format
        $this->assertTrue($this->is_json($resultJson));
        //check response is a pHP object
        $this->assertisObject($resultObject);

        $jsonToArray  = json_decode($resultJson, true);
        $this->assertArrayHasKey('edges', $jsonToArray['events'], 'The returned json has invalid format');
        $this->assertArrayHasKey('pageInfo', $jsonToArray['events'], 'The returned json has invalid format');
        $this->assertArrayHasKey('edges', $resultArray['events'], 'The returned array has invalid format');
        $this->assertArrayHasKey('pageInfo', $resultArray['events'], 'The returned array has invalid format');
        $this->assertObjectHasAttribute('edges', $resultObject->events, 'The returned object has invalid format');
        $this->assertObjectHasAttribute('pageInfo', $resultObject->events, 'The returned object has invalid format');
    }

    public function testPagination(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();
        
        $fields = [];
        $paging = ['page' => 1, 'perPage' => 25];
        $sorting = ['field' => 'title', 'direction' => 'asc'];
        $filters = [];

        $eventObj = new Event($weblinkActivationParams);

        $resultArray = $eventObj->loadAll($filters, $paging, $sorting, $fields, 'array');

        //check if pagination returns the requested number of results
        if ($resultArray['events']['pageInfo']['totalRecords'] >= $paging['perPage']) {
            $this->assertEquals(25, count($resultArray['events']['edges']), 'Error in pagination results');
        } else {
            $perPage = intval(ceil($resultArray['events']['pageInfo']['totalRecords']/2));
            $resultArray = $eventObj->loadAll(
                $filters,
                $paging = ['page' => 1, 'perPage' => $perPage],
                $sorting,
                $fields,
                'array'
            );
             $this->assertEquals($perPage, count($resultArray['events']['edges']), 'Error in pagination results');
        }
    }

    public function is_json($str)
    {
        return json_decode($str) != null;
    }
    
    public function getWeblinkActivationParams()
    {
        return array(
            'oauthServer' => $_GET['weblinkOauthServer'],
            'apiUri' => $_GET['weblinkApiUri'],
            'portal' => $_GET['portal'],
            'portalToken' => ''.$_GET['portalToken'].''
        );
    }
}
