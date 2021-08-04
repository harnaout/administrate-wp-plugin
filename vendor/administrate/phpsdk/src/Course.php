<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQl\Client;

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
    private static $paging = array('page' => 1, 'perPage' => 25);
    private static $sorting = array('field' => 'name', 'direction' => 'asc');

    private static $defaultFields = array(
        'id',
        'code',
        'name',
        'description',
        'category',
        'imageUrl'
    );

    private static $defaultCoreFields = array(
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
        'customFieldValues' => array(
            'definitionKey',
            'definitionLocator',
            'value'
        ),
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
     * Method to Get a single course Info from ID.
     * @param  string $id LMS Course ID
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
    public function loadById($courseId, $args)
    {
        if ($courseId) {
            $args['filters'] = array(
                array(
                    'field' => 'id',
                    'operation' => 'eq',
                    'value' => $courseId,
                )
            );
        }
        return self::load($args);
    }

    /**
     * Method to get course Info.
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

        $courses = 'courses';
        $coursesFilters = 'CourseFieldFilter';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $courses = 'courseTemplates';
            $coursesFilters = 'CourseTemplateFieldGraphFilter';
        }
        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        $nodeQueryResults = QueryBuilder::buildNode($fields);
        $node = $nodeQueryResults['node'];
        $nodeFilters = $nodeQueryResults['filters'];

        $builder = (new QueryBuilder($courses))
            ->setVariable('filters', "[$coursesFilters]", true)
            ->setArgument('filters', '$filters')
            ->selectField(
                (new QueryBuilder('edges'))
                    ->selectField($node)
            );

        $variablesArray = array("filters" => $filters);

        if (!empty($nodeFilters)) {
            foreach ($nodeFilters as $filterType => $filterTypeFilters) {
                foreach ($filterTypeFilters as $filterKey => $value) {
                    $builder->setVariable($filterKey, "[" . $filterType . "]", true);
                    $variablesArray[$filterKey] = $value;
                }
            }
        }

        $gqlQuery = $builder->getQuery();

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);

        if (isset($result[$courses]['edges'][0]['node']) && !empty($result[$courses]['edges'][0]['node'])) {
            return Client::toType($returnType, $result[$courses]['edges'][0]['node']);
        }
    }

    /**
     * Method to get all Courses
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

        $courses = 'courses';
        $coursesOrders = 'CourseFieldOrder';
        $coursesFilters = 'CourseFieldFilter';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $courses = 'courseTemplates';
            $coursesOrders = 'CourseTemplateFieldGraphOrder';
            $coursesFilters = 'CourseTemplateFieldGraphFilter';
        }

        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        $perPage = $paging['perPage'];
        $page = $paging['page'];

        $nodeQueryResults = QueryBuilder::buildNode($fields);
        $node = $nodeQueryResults['node'];
        $nodeFilters = $nodeQueryResults['filters'];

        $first = $perPage;
        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        $builder = (new QueryBuilder($courses))
        ->setVariable('order', $coursesOrders, false)
        ->setArgument('order', '$order')
        ->setArgument('first', $first)
        ->setArgument('offset', $offset)
        ->setVariable('filters', "[$coursesFilters]", true)
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

        $variablesArray = array(
            'filters' => $filters,
            'order' => Helper::toObject($sorting),
        );

        if (!empty($nodeFilters)) {
            foreach ($nodeFilters as $filterType => $filterTypeFilters) {
                foreach ($filterTypeFilters as $filterKey => $value) {
                    $builder->setVariable($filterKey, "[" . $filterType . "]", true);
                    $variablesArray[$filterKey] = $value;
                }
            }
        }

        $gqlQuery = $builder->getQuery();

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);
        return Client::toType($returnType, $result);
    }
}
