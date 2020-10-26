<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Administrate\PhpSdk\Course;

/**
 * CourseTest
 *
 * @package Administrate\PhpSdk
 * @author Ali Habib <ahh@administrate.co>
 * @author Jad Khater <jck@administrate.co>
 */
final class CourseTest extends TestCase
{
    public function testLoadSingleCourse(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();

        $args = array(
            'returnType' => 'array', //array, obj, json
            'fields' => array(),
        );

        $course = new Course();
        $courseObj = new Course($weblinkActivationParams);
        $courseArray = $courseObj->loadById($_GET['courseId'], $args);

        $args['returnType'] = 'json';
        $courseJson = $courseObj->loadById($_GET['courseId'], $args);

        $args['returnType'] = 'obj';
        $courseObj = $courseObj->loadById($_GET['courseId'], $args);

        //check response is a php array
        $this->assertisArray($courseArray);
        //check response is in json format
        $this->assertTrue($this->is_json($courseJson));
        //check response is a pHP object
        $this->assertisObject($courseObj);

        $this->assertArrayHasKey('id', json_decode($courseJson, true), 'The returned json has invalid format');
        $this->assertArrayHasKey('name', json_decode($courseJson, true), 'The returned json has invalid format');
        $this->assertArrayHasKey('id', $courseArray, 'The returned array has invalid format');
        $this->assertArrayHasKey('name', $courseArray, 'The returned array has invalid format');
        $this->assertObjectHasAttribute('id', $courseObj, 'The returned object has invalid format');
        $this->assertObjectHasAttribute('name', $courseObj, 'The returned object has invalid format');
    }

    public function testLoadMultipleCourses(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();

        $args = array(
            'filters' => array(),
            'paging' => array(
                'page' => 1,
                'perPage' => 25
            ),
            'sorting' => array(
                'field' => 'name',
                'direction' => 'asc'
            ),
            'returnType' => 'json', //array, obj, json
            'fields' => array(),
        );

        $courseObj = new Course($weblinkActivationParams);

        $args['returnType'] = 'array';
        $resultArray = $courseObj->loadAll($args);

        $args['returnType'] = 'json';
        $resultJson = $courseObj->loadAll($args);

        $args['returnType'] = 'obj';
        $resultObject = $courseObj->loadAll($args);

        //check response is a php array
        $this->assertisArray($resultArray);
        //check response is in json format
        $this->assertTrue($this->is_json($resultJson));
        //check response is a pHP object
        $this->assertisObject($resultObject);

        $jsonToArray  = json_decode($resultJson, true);
        $this->assertArrayHasKey('edges', $jsonToArray['courses'], 'The returned json has invalid format');
        $this->assertArrayHasKey('pageInfo', $jsonToArray['courses'], 'The returned json has invalid format');
        $this->assertArrayHasKey('edges', $resultArray['courses'], 'The returned array has invalid format');
        $this->assertArrayHasKey('pageInfo', $resultArray['courses'], 'The returned array has invalid format');
        $this->assertObjectHasAttribute('edges', $resultObject->courses, 'The returned object has invalid format');
        $this->assertObjectHasAttribute('pageInfo', $resultObject->courses, 'The returned object has invalid format');
    }

    public function testPagination(): void
    {
        $weblinkActivationParams = $this->getWeblinkActivationParams();

        $args = array(
            'filters' => array(),
            'paging' => array(
                'page' => 1,
                'perPage' => 25
            ),
            'sorting' => array(
                'field' => 'name',
                'direction' => 'asc'
            ),
            'returnType' => 'array', //array, obj, json
            'fields' => array(),
        );

        $courseObj = new Course($weblinkActivationParams);
        $resultArray = $courseObj->loadAll($args);
        //check if pagination returns the requested number of results
        if ($resultArray['courses']['pageInfo']['totalRecords'] >= $paging['perPage']) {
            $this->assertEquals(25, count($resultArray['courses']['edges']), 'Error in pagination results');
        } else {
            $perPage = ceil($resultArray['courses']['pageInfo']['totalRecords']/2);
            $resultArray = $courseObj->loadAll(
                $filters,
                ['page' => 1, 'perPage' => $perPage],
                $sorting,
                $fields,
                'array'
            );
             $this->assertEquals($perPage, count($resultArray['courses']['edges']), 'Error in pagination results');
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
