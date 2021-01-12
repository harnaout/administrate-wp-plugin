<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;
use Administrate\PhpSdk\GraphQl\Client;

/**
 * QueryTest
 *
 * @package Administrate\PhpSdk
 * @author Ali Habib <ahh@administrate.co>
 * @author Jad Khater <jck@administrate.co>
 */
final class QueryTest extends TestCase
{

    public function testQueryBuilder(): void
    {
        // Core API Params
        $coreApiActivationParams = array(
            'clientId' => $_GET['clientId'],
            'clientSecret' => $_GET['clientSecret'],
            'instance' => $_GET['instance'],
            'oauthServer' => $_GET['coreOauthServer'],
            'apiUri' => $_GET['coreApiUri'],
            'redirectUri' => $_GET['baseURL'] . '/examples/authentication/oauth-callback.php',
            'accessToken' => $_GET['accessToken'],
            'refreshToken' => $_GET['refreshToken'],
        );

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
            "value" => $_GET['learnerId'],
        )
        )
        );

        // $coreApiActivationParams Set this value in config.php
        $authorizationHeaders = Client::setHeaders($coreApiActivationParams);
        $client = new Client($coreApiActivationParams['apiUri'], $authorizationHeaders);
        $results = $client->runQuery($gqlQuery, true, $variablesArray);
        $this->assertArrayHasKey('learners', $results->getData(), 'The returned array has invalid format');
    }
}
