<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQL\Client;
use GraphQL\RawObject;

/**
 * Category
 *
 * @package Administrate\PhpSdk
 * @author Jad Khater <jck@administrate.co>
 * @author Ali Habib <ahh@administrate.co>
 */
class Category
{
    public $params;
    private static $paging = array('page' => 1, 'perPage' => 25);
    private static $sorting = array('field' => 'name', 'direction' => 'asc');

    private static $defaultFields = array(
        'id',
        'name',
        'shortDescription',
        'parent' => array('id')
    );

    private static $defaultCoreFields = array(
        'id',
        'legacyId',
        'name',
        'description',
        'parentCategory' => array('id', 'name')
    );

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
     * Method to Get a single category Info from ID.
     *
     * @param  string $id LMS Category ID
     * @param  array $args associative array to pass return type and fields
     *
     * Example $args:
     * $args = array(
     *     'returnType' => 'json', //array, obj, json
     *     'fields' => array('id','name'),
     *     'coreApi' => false, //boolean to specify if call is a weblink or a core API call.
     * );
     *
     * @return based on returnType
     */
    public function loadById($categoryId, $args)
    {
        if ($categoryId) {
            $args['filters'] = array(
                array(
                    'field' => 'id',
                    'operation' => 'eq',
                    'value' => $categoryId,
                )
            );
        }
        return self::load($args);
    }

    /**
     * Method to Get category Info.
     * @param  array $args associative array to pass return type and fields
     *
     * Example $args:
     * $args = array(
     *     'filters' => array(
     *          array(
     *               'field' => 'name',
     *               'operation' => 'eq',
     *               'value' => 'Example Category 1',
     *          ),
     *     ),
     *     'paging' => array(
     *           'page' => 1,
     *           'perPage' => 2
     *     ),
     *     'sorting' => array(
     *           'field' => 'name',
     *           'direction' => 'asc'
     *      ),
     *      'returnType' => 'json', //array, obj, json
     *      'fields' => array(
     *            'id',
     *            'name'
     *      )
     *      'coreApi' => false, //boolean to specify if call is a weblink or a core API call.
     *);
     *
     * @return based on returnType
     */
    public function load($args)
    {
        $defaultArgs = array(
            'filters' => array(),
            'fields' => self::$defaultFields,
            'returnType' => 'json', //array, obj, json,
            'coreApi' => false,
        );

        $categories = 'categories';
        $categoriesFilters = 'CategoryFieldFilter';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $categories = 'learningCategories';
            $categoriesFilters = 'LearningCategoryFieldGraphFilter';
        }
        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        $node = QueryBuilder::buildNode($fields);

        $builder = (new QueryBuilder($categories))
            ->setVariable('filters', "[$categoriesFilters]", true)
            ->setArgument('filters', '$filters')
            ->selectField(
                (new QueryBuilder('edges'))
                    ->selectField($node)
            );

        $gqlQuery = $builder->getQuery();

        $variablesArray = array("filters" => $filters);

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);

        if (isset($result[$categories]['edges'][0]['node']) && !empty($result[$categories]['edges'][0]['node'])) {
            return Client::toType($returnType, $result[$categories]['edges'][0]['node']);
        }
    }

    /**
     * Method to get all Categories
     * @param  array $args associative array to pass return type and fields
     *
     * Example $args:
     * $args = array(
     *     'filters' => array(
     *          array(
     *               'field' => 'name',
     *               'operation' => 'eq',
     *               'value' => 'Example Category 1',
     *          ),
     *     ),
     *     'paging' => array(
     *           'page' => 1,
     *           'perPage' => 2
     *     ),
     *     'sorting' => array(
     *           'field' => 'name',
     *           'direction' => 'asc'
     *      ),
     *      'returnType' => 'json', //array, obj, json
     *      'fields' => array(
     *            'id',
     *            'name'
     *      ),
     *      'coreApi' => false, //boolean to specify if call is a weblink or a core API call.
     *);
     *
     * @return based on returnType
     */
    public function loadAll($args)
    {
        $defaultArgs = array(
            'filters' => array(),
            'paging' => self::$paging,
            'sorting' => self::$sorting,
            'fields' => self::$defaultFields,
            'returnType' => 'json', //array, obj, json,
            'coreApi' => false,
        );

        $categories = 'categories';
        $categoriesOrder = 'CategoryFieldOrder';
        $categoriesFilters = 'CategoryFieldFilter';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $categories = 'learningCategories';
            $categoriesOrder = 'LearningCategoryFieldGraphOrder';
            $categoriesFilters = 'LearningCategoryFieldGraphFilter';
        }

        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        //set paging variables
        $perPage = $paging['perPage'];
        $page = $paging['page'];

        $node = QueryBuilder::buildNode($fields);

        $first = $perPage;
        if ($page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;

        $builder = (new QueryBuilder($categories))
            ->setVariable('order', $categoriesOrder, false)
                ->setArgument('first', $first)
                ->setArgument('offset', $offset)
                ->setArgument('order', '$order')
            ->setVariable('filters', "[$categoriesFilters]", true)
                ->setArgument('filters', '$filters')
            ->selectField(
                (new QueryBuilder('pageInfo'))
                    ->selectField('startCursor')
                    ->selectField('endCursor')
                    ->selectField('totalRecords')
                    ->selectField('hasNextPage')
                    ->selectField('hasPreviousPage')
            )
            ->selectField(
                (new QueryBuilder('edges'))->selectField($node)
            );

        $gqlQuery = $builder->getQuery();

        $variablesArray = array(
            'filters' => $filters,
            'order' => Helper::toObject($sorting),
        );

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);
        return Client::toType($returnType, $result);
    }
}
