<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQl\Client;

/**
 * Catalogue
 *
 * @package Administrate\PhpSdk
 * @author Jad Khater <jck@administrate.co>
 * @author Omaya Noureddine <orn@administrate.co>
 */
 class Catalogue {
    public $params;
    private static $paging = array('page' => 1, 'perPage' => 25);
    private static $sorting = array('field' => 'name', 'direction' => 'asc');


    private static $defaultFields = array(
        '__typename',
        '... on Course' => array(
            'id',
            'code',
            'name',
            'description',
            'category',
            'imageUrl'
        ),
        '... on LearningPath' => array(
            'id',
            'name',
            'description',
            'lifecycleState',
            'category',
            'price' => array(
                'amount',
            ),
        ),
        
    );

    private static $defaultCoreFields = array(
        '__typename',
        '... on CourseTemplate' => array(
            'id',
            'legacyId',
            'lifecycleState',
            'code',
            'title',
            'image' => array(
                'id',
                'name',
                'description',
                'folder' => array('id', 'name')
            ),
            'imageGallery' => array(
                'type' => 'edges',
                'fields' => array('id', 'name', 'description')
            ),
            'learningCategories' => array(
                'type' => 'edges',
                'fields' => array('id', 'legacyId', 'name'),
            ),
            'publicPrices' => array(
                'type' => 'edges',
                'fields' => array(
                    'id',
                    'amount',
                    'priceLevel' => array('id', 'legacyId', 'name'),
                    'financialUnit' => array('name', '__typename'),
                    'region' => array(
                        'id',
                        'name',
                        'code',
                        'company' => array('id', 'name'),
                    ),
                ),
            ),
        ),
        '... on LearningPath' => array(
            'id',
            'name',
            'description',
            'learningObjectives' => array(
                'pageInfo' => array(
                    'totalRecords'
                )
            ),
        )
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
     * Method to get all Courses & Learning Paths
     * @param  array $args associative array to pass return type and fields
     *
     * Example $args:
     * $args = array(
     *     'filters' => array(
     *          array(
     *               'field' => 'name',
     *               'operation' => 'eq',
     *               'value' => 'Example 1',
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

        $nodeOrder = 'CatalogueFieldOrder';
        $nodeFilters = 'CatalogueFieldFilter';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $nodeOrder = 'CatalogueItemFieldGraphOrder';
            $nodeFilters = 'CatalogueItemFieldGraphFilter';
        }

        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        $perPage = $paging['perPage'];
        $page = $paging['page'];

        $node = QueryBuilder::buildNode($fields);

        $first = $perPage;
        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        $builder = (new QueryBuilder('catalogue'))
        ->setVariable('order', $nodeOrder, false)
         ->setArgument('order', '$order')
        ->setArgument('first', $first)
        ->setArgument('offset', $offset)
        ->setVariable('filters', "[$nodeFilters]", true)
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
            (new QueryBuilder('edges'))
                ->selectField($node)
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

