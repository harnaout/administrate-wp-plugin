<?php

namespace Administrate\PhpSdk\GraphQl;

use GraphQL\QueryBuilder\QueryBuilder as GqlQueryBuilder;

class QueryBuilder extends GqlQueryBuilder
{
    static function buildNode($fields)
    {
        $filters = array();
        $node = (new QueryBuilder('node'));
        $result = array(
            'node' => $node,
            'filters' => $filters
        );
        foreach ($fields as $fieldKey => $fieldVal) {
            if (is_array($fieldVal)) {
                if (isset($fieldVal['type']) && $fieldVal['type'] == 'edges') {
                    $fields = $fieldVal['fields'];
                    $subNode = (new QueryBuilder('' . $fieldKey . ''));
                    if (isset($fieldVal['filters']) && isset($fieldVal['filtersType'])) {
                        $filterKey = $fieldKey . 'Filters';
                        $filterType = $fieldVal['filtersType'];
                        $subNode->setArgument('filters', '$' . $filterKey);
                        $filters[$filterType][$filterKey] = $fieldVal['filters'];
                    }
                    $edgesNode = (new QueryBuilder('edges'));
                    $innerNode = (new QueryBuilder('node'));
                    foreach ($fields as $subFieldKey => $subFielfieldVal) {
                        if (is_array($subFielfieldVal)) {
                            $subInnerNode = self::buildSubNode($subFieldKey, $subFielfieldVal);
                            $innerNode->selectField($subInnerNode['subNode']);
                            foreach ($subInnerNode['filters'] as $filterKey => $value) {
                                $filters[$filterKey] = $value;
                            }
                        } else {
                            $innerNode->selectField($subFielfieldVal);
                        }
                    }
                    $edgesNode->selectField($innerNode);
                    $subNode->selectField($edgesNode);
                } else {
                    $innerNode = self::buildSubNode($fieldKey, $fieldVal);
                    $subNode = $innerNode['subNode'];
                    foreach ($innerNode['filters'] as $filterKey => $value) {
                        $filters[$filterKey] = $value;
                    }
                }
                $node->selectField($subNode);
            } else {
                $node->selectField($fieldVal);
            }
        }
        return $result = array(
            'node' => $node,
            'filters' => $filters
        );
    }

    static function buildSubNode($subFieldKey, $subFielfieldVal)
    {
        $filters = array();
        $subInnerNode = (new QueryBuilder('' . $subFieldKey . ''));
        $result = array(
            'subNode' => $subInnerNode,
            'filters' => $filters
        );
        foreach ($subFielfieldVal as $fieldKey => $fieldVal) {
            if (is_array($fieldVal)) {
                if (isset($fieldVal['type']) && $fieldVal['type'] == 'edges') {
                    $fields = $fieldVal['fields'];
                    $subNode = (new QueryBuilder('' . $fieldKey . ''));
                    if (isset($fieldVal['filters']) && isset($fieldVal['filtersType'])) {
                        $filterKey = $fieldKey . 'Filters';
                        $filterType = $fieldVal['filtersType'];
                        $subNode->setArgument('filters', '$' . $filterKey);
                        $filters[$filterType][$filterKey] = $fieldVal['filters'];
                    }
                    $edgesNode = (new QueryBuilder('edges'));
                    $innerNode = self::buildNode($fields);
                    foreach ($innerNode['filters'] as $filterKey => $value) {
                        $filters[$filterKey] = $value;
                    }
                    $edgesNode->selectField($innerNode['node']);
                    $subNode->selectField($edgesNode);
                    $subInnerNode->selectField($subNode);
                } else {
                    $node = self::buildSubNode($fieldKey, $fieldVal);
                    $subInnerNode->selectField($node['subNode']);
                    foreach ($node['filters'] as $filterKey => $value) {
                        $filters[$filterKey] = $value;
                    }
                }
            } else {
                $subInnerNode->selectField($fieldVal);
            }
        }
        return $result = array(
            'subNode' => $subInnerNode,
            'filters' => $filters
        );
    }
}
