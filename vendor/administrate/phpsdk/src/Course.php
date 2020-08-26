<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQL\Client;

/**
 * Course
 *
 * @package Administrate\PhpSdk
 * @author Ali Habib <ahh@administrate.co>
 * @author Jad Khater <jck@administrate.co>
 */
class Course
{
    public $params;
    private static $defaultFields = array('id', 'code', 'name', 'description', 'category', 'imageUrl');
    private static $paging = array('page' => 1, 'perPage' => 25);
    private static $sorting = array('field' => 'name', 'direction' => 'ASC');

    /**
     * Default constructor.
     * Set the static variables.
     *
     * @return void
     *
     */
    public function __construct($params = array())
    {
        $this->setParams($params);
    }

    /**
     * Method to set APP Environment Params
     * @param array $params configuration array
     *
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Method to Get a single course Info from ID.
     *
     * @param  string $id   LMS Course ID
     *
     * @return String       JSON Object
     */
    public function loadById($courseId, $fields = [], $returnType = 'array')
    {
        $filters = [
            'id' => $courseId
        ];
        return self::load($filters, $fields, $returnType);
    }

    /**
     * Method to get course Info.
     * @param array $filters
     * @param array $fields //defaults array('id', 'name', 'shortDescription', 'parent')
     * @param string $returnType //json, array, obj default: array
     * @return based on returnType
     */
    public function load($filters = [], $fields = [], $returnType = 'array')
    {
        if (!$fields) {
            $fields = self::$defaultFields;
        }

        $node = (new QueryBuilder('node'));
        foreach ($fields as $fieldKey) {
            $node->selectField($fieldKey);
        }

        $builder = (new QueryBuilder('courses'))
            ->setVariable('filters', '[CourseFieldFilter]', true)
            ->setArgument('filters', '$filters')
            ->selectField(
                (new QueryBuilder('edges'))
                    ->selectField($node)
            );

        $gqlQuery = $builder->getQuery();

        $variablesArray = array(
            "filters" => array(
            )
        );

        foreach ($filters as $key => $value) {
            $filter = array(
                "field" => $key,
                "operation" => "eq",
                "value" => $value
            );
            array_push($variablesArray['filters'], $filter);
        };

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);

        if (isset($result['courses']['edges'][0]['node']) && !empty($result['courses']['edges'][0]['node'])) {
            return Client::toType($returnType, $result['courses']['edges'][0]['node']);
        }
    }

    /**
     * Method to get all Courses
     * @param array $filters
     * @param array $paging ['page' => '', 'perPage' => '']
     * @param array $sorting ['field' => '', 'direction' => '']
     * @param array $fields //defaults ['id', 'name', 'shortDescription', 'parent']
     * @param string $returnType //json, array, obj default: array
     * @return based on returnType
     */
    public function loadAll($filters = [], $paging = [], $sorting = [], $fields = [], $returnType = 'array')
    {
        //set paging variables
        if (empty($paging)) {
            $paging = self::$paging;
        }
        $perPage = $paging['perPage'];
        $page = $paging['page'];


        //set sorting variables
        if (empty($sorting)) {
            $sorting = self::$sorting;
        }
        $sortField = $sorting['field'];
        $sortDirection = $sorting['direction'];

        if (!$fields) {
            $fields = self::$defaultFields;
        }

        $node = (new QueryBuilder('node'));
        foreach ($fields as $fieldKey) {
            $node->selectField($fieldKey);
        }

        $first = $perPage;
        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        $builder = (new QueryBuilder('courses'))
        ->setVariable('order', 'CourseFieldOrder', false)
         ->setArgument('order', '$order')
        ->setArgument('first', $first)
        ->setArgument('offset', $offset)
        ->setVariable('filters', '[CourseFieldFilter]', true)
        ->setArgument('filters', '$filters')
        ->selectField(
            (new QueryBuilder('pageInfo'))
                ->selectField('startCursor')
                ->selectField('endCursor')
                ->selectField('totalRecords')
        )
        ->selectField(
            (new QueryBuilder('edges'))
                ->selectField($node)
        );

        $gqlQuery = $builder->getQuery();

        $variablesArray = array(
            "filters" => array(),
            "order" => ''
        );

        if (isset($filters['categoryId']) && $filters['categoryId'] != "") {
            array_push($variablesArray['filters'], array(
                "field" => "categoryId",
                "operation" => "eq",
                "value" => $filters['categoryId']
            ));
        }
        if (isset($filters['keyword']) && $filters['keyword'] != "") {
            array_push($variablesArray['filters'], array(
                "field" => "name",
                "operation" => "like",
                "value" => "%".$filters['keyword']."%"
            ));
        }

        if (!empty($sorting)) {
            $sortingObject = new Class{
            };
            $sortingObject->field = $sortField;
            $sortingObject->direction = $sortDirection;
            //$sortingObject = new RawObject('{"field": "'.$sortField.'", "direction": "'.$sortDirection.'"}');
            $variablesArray['order'] = $sortingObject;
        }

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);
        return Client::toType($returnType, $result);
    }
}
