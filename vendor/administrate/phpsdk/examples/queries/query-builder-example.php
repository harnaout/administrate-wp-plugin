<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\GraphQL\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQL\Client;

$fields = array(
    'id',
    'legacyId',
    'lifecycleState',
    'createdAt',
    'isActive',
    'isCancelled',
    'expiresAt',
    array(
        'key' => 'event',
        'fields' => array(
            'id',
            'legacyId',
            'title',
            'price'
        ),
    ),
);

$node = (new QueryBuilder('node'));
foreach ($fields as $fieldKey) {
    if (is_array($fieldKey)) {
        $subFields = $fieldKey;
        $subFieldsQuery = (new QueryBuilder($subFields['key']));
        foreach ($subFields['fields'] as $subfieldKey) {
            $subFieldsQuery->selectField($subfieldKey);
        }
        $node->selectField($subFieldsQuery);
    } else {
       $node->selectField($fieldKey);
    }
}

$builder = (new QueryBuilder('learners'))
    ->setVariable('filters', '[LearnerFieldGraphFilter]', true)
    ->setArgument('filters', '$filters')
    ->selectField(
        (new QueryBuilder('edges'))
            ->selectField($node)
    );

$gqlQuery = $builder->getQuery();

$variablesArray = array(
    "filters" => array(
        0 => array(
            "field" => "id",
            "operation" => "eq",
            "value" => $learnerId,
        )
    )
);

// $coreApiActivationParams Set this value in config.php
$authorizationHeaders = Client::setHeaders($coreApiActivationParams);
$client = new Client($coreApiActivationParams['apiUri'], $authorizationHeaders);
$results = $client->runQuery($gqlQuery, true, $variablesArray);

echo json_encode($results->getData());
