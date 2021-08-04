<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQl\Client;

/**
 * Event
 *
 * @package Administrate\PhpSdk
 * @author Ali Habib <ahh@administrate.co>
 * @author Jad Khater <jck@administrate.co>
 */
class Event
{
    public $params;
    private static $paging = array('page' => 1, 'perPage' => 25);
    private static $sorting = array('field' => 'id', 'direction' => 'desc');

    private static $defaultFields = array(
        'id',
        'name',
        'price' => array(
            'amount'
        ),
        'classroomStart',
        'classroomEnd',
        'lmsStart',
        'lmsEnd',
        'start',
        'end',
        'deliveryMethod',
        'remainingPlaces',
        'location' => array(
            'name',
        ),
        'tax' => array(
            'id',
            'effectiveRate',
            'name',
        ),
        'course' => array(
            'id',
            'code',
        ),
    );

    private static $defaultCoreFields = array(
        'id',
        'title',
        'status',
        'price',
        'classroomStart',
        'classroomEnd',
        'lmsStart',
        'lmsEnd',
        'start',
        'end',
        'bookedPlaces',
        'remainingPlaces',
        'maxPlaces',
        'location' => array(
            'id',
            'name',
            'region' => array(
                'code'
            )
        ),
        'courseTemplate' => array(
            'id',
            'code',
            'title',
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
     * Method to Get a single event Info from ID.
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
     * @return String JSON Object
     */
    public function loadById($eventId, $args)
    {
        if ($eventId) {
            $args['filters'] = array(
                array(
                    'field' => 'id',
                    'operation' => 'eq',
                    'value' => $eventId,
                )
            );
        }
        return self::load($args);
    }

    /**
     * Method to Get Events Info.
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
     *      'coreApi' => false, //boolean to specify if call is a weblink or a core API call.lt: array
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

        $nodeType = "events";
        $eventFilters = "EventFieldFilter";

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $nodeType = 'events';
            $eventFilters = 'EventFieldGraphFilter';
        }

        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        $nodeQueryResults = QueryBuilder::buildNode($fields);
        $node = $nodeQueryResults['node'];
        $nodeFilters = $nodeQueryResults['filters'];

        $builder = (new QueryBuilder($nodeType))
            ->setVariable('filters', "[$eventFilters]", true)
            ->setArgument('filters', '$filters')
            ->selectField(
                (new QueryBuilder('edges'))
                    ->selectField($node)
            );

        $variablesArray = array(
            "filters" => $filters
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
        if (isset($result[$nodeType]['edges'][0]['node']) && !empty($result[$nodeType]['edges'][0]['node'])) {
            return Client::toType($returnType, $result[$nodeType]['edges'][0]['node']);
        }
    }

    /**
     * Method to get all Events
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

        $nodeType = 'events';
        $eventsOrder = 'EventFieldOrder';
        $eventsFilters = 'EventFieldFilter!';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $nodeType = 'events';
            $eventsOrder = 'EventFieldGraphOrder';
            $eventsFilters = 'EventFieldGraphFilter';
        }

        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        //set paging variables
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

        $builder = (new QueryBuilder($nodeType))
            ->setVariable('order', $eventsOrder, false)
                ->setArgument('first', $first)
                ->setArgument('offset', $offset)
                ->setArgument('order', '$order')
            ->setVariable('filters', "[$eventsFilters]", true)
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
            "filters" => array(),
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


    /**
     * Method to get all Events related to a single course
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
     *      'courseCode' => '&gjlVS...HJBgsyst'
     * @return based on returnType
     */
    public function loadByCourseCode($args)
    {
        $defaultArgs = array(
            'filters' => array(),
            'paging' => self::$paging,
            'sorting' => self::$sorting,
            'fields' => self::$defaultFields,
            'returnType' => 'json', //array, obj, json,
            'coreApi' => false,
            'courseCode' => "",
        );

        $nodeType = 'events';
        $nodeOrder = 'EventFieldOrder';
        $nodeFilters = 'EventFieldFilter!';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $nodeType = 'events';
            $nodeOrder = 'EventFieldGraphOrder';
            $nodeFilters = 'EventFieldGraphFilter';
        }

        if (isset($args['courseCode']) && $args['courseCode']) {
            $defaultArgs['courseCode'] = $args['courseCode'];
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

        $builder = (new QueryBuilder($nodeType))
            ->setVariable('order', $nodeOrder, false)
                ->setArgument('first', $first)
                ->setArgument('offset', $offset)
                ->setArgument('order', '$order')
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
            "filters" => array(),
            'order' => Helper::toObject($sorting),
        );

        if (isset($args['courseCode']) && $args['courseCode'] != "") {
            array_push($variablesArray['filters'], array(
                "field" => "courseCode",
                "operation" => "eq",
                "value" => $args['courseCode']
            ));
        }

        $result = Client::sendSecureCall($this, $gqlQuery, $variablesArray);
        return Client::toType($returnType, $result);
    }
}
