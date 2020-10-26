<?php

namespace Administrate\PhpSdk\GraphQl;

use GraphQL\QueryBuilder\QueryBuilder as GqlQueryBuilder;

class QueryBuilder extends GqlQueryBuilder
{
    static function buildNode($fields)
    {
        $node = (new QueryBuilder('node'));
        foreach ($fields as $fieldKey => $fieldVal) {
            if (is_array($fieldVal)) {
                if (isset($fieldVal['type']) && $fieldVal['type'] == 'edges') {
                    $fields = $fieldVal['fields'];
                    $subNode = (new QueryBuilder('' . $fieldKey . ''));
                    $edgesNode = (new QueryBuilder('edges'));
                    $innerNode = (new QueryBuilder('node'));
                    foreach ($fields as $subFieldKey => $subFielfieldVal) {
                        if (is_array($subFielfieldVal)) {
                            $subInnerNode = self::buildSubNode($subFieldKey, $subFielfieldVal);
                            $innerNode->selectField($subInnerNode);
                        } else {
                            $innerNode->selectField($subFielfieldVal);
                        }
                    }
                    $edgesNode->selectField($innerNode);
                    $subNode->selectField($edgesNode);
                } else {
                    $subNode = self::buildSubNode($fieldKey, $fieldVal);
                }
                $node->selectField($subNode);
            } else {
                $node->selectField($fieldVal);
            }
        }
        return $node;
    }

    static function buildSubNode($subFieldKey, $subFielfieldVal)
    {
        $subInnerNode = (new QueryBuilder('' . $subFieldKey . ''));
        foreach ($subFielfieldVal as $fieldKey => $fieldVal) {
            if (is_array($fieldVal)) {
                $subInnerNode->selectField(self::buildSubNode($fieldKey, $fieldVal));
            } else {
                $subInnerNode->selectField($fieldVal);
            }
        }
        return $subInnerNode;
    }
}
