<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQl\Client;
use GraphQL\RawObject;

/**
 * LearningPath
 *
 * @package Administrate\PhpSdk
 * @author Jad Khater <jck@administrate.co>
 * @author Ali Habib <ahh@administrate.co>
 */
class LearningPath
{
    public $params;
    private static $paging = array('page' => 1, 'perPage' => 25);
    private static $sorting = array('field' => 'name', 'direction' => 'asc');

    private static $defaultFields = array(
        'id',
        'name',
        'description',
        'lifecycleState',
        'category',
        'price' => array(
            'amount',
        ),
    );

    private static $defaultCoreFields = array(
        'id',
        'name',
        'description',
        'learningObjectives' => array(
            'pageInfo' => array(
                'totalRecords'
            )
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
     * Method to Get a single Learning Path  from ID.
     *
     * @param  string $id learning path ID
     * @param  array $args associative array to pass return type and fields and sorting and paging
     *
     * Example $args:
     * $args = array(
     *     'returnType' => 'json', //array, obj, json
     *     'fields' => array('id','name'),
     *     'sorting' => array(),
     *     'paging' => array(),
     *     'coreApi' => false, //boolean to specify if call is a weblink or a core API call.
     * );
     *
     * @return based on returnType
     */
    public function loadById($id, $args)
    {
        if ($id) {
            $args['filters'] = array(
                array(
                    'field' => 'id',
                    'operation' => 'eq',
                    'value' => $id,
                )
            );
        }
        return self::load($args);
    }

    /**
     * Method to get learning path info.
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

        $lps = 'learningPaths';
        $lpsFilters = 'LearningPathFieldFilter!';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $lps = 'learningPaths';
            $lpsFilters = 'LearningPathFieldGraphFilter';
        }
        $args = Helper::setArgs($defaultArgs, $args);
        extract($args);

        $nodeQueryResults = QueryBuilder::buildNode($fields);
        $node = $nodeQueryResults['node'];
        $nodeFilters = $nodeQueryResults['filters'];

        $builder = (new QueryBuilder($lps))
            ->setVariable('filters', "[$lpsFilters]", true)
            ->setArgument('filters', '$filters')
            ->selectField(
                (new QueryBuilder('edges'))
                    ->selectField(
                        $node
                    )
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

        if (isset($result[$lps]['edges'][0]['node']) && !empty($result[$lps]['edges'][0]['node'])) {
            return Client::toType($returnType, $result[$lps]['edges'][0]['node']);
        }
    }

    /**
     * Method to get all Learning Paths
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

        $lps = 'learningPaths';
        $lpsOrder = 'LearningPathFieldOrder';
        $lpsFilters = 'LearningPathFieldFilter!';

        if (isset($args['coreApi']) && $args['coreApi']) {
            $defaultArgs['fields'] = self::$defaultCoreFields;
            $nodeType = 'learningPaths';
            $lpsOrder = 'LearningPathFieldGraphOrder';
            $lpsFilters = 'LearningPathFieldGraphFilter';
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

        $builder = (new QueryBuilder($lps))
            ->setVariable('order', $lpsOrder, false)
                ->setArgument('first', $first)
                ->setArgument('offset', $offset)
                ->setArgument('order', '$order')
            ->setVariable('filters', "[$lpsFilters]", true)
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
