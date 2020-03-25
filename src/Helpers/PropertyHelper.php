<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/25/15
 * Time: 4:56 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\JsonApi\Helpers;

use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Api\Transformer\Helpers\RecursiveFormatterHelper;
use NilPortugues\Serializer\Serializer;

/**
 * Class PropertyHelper.
 */
class PropertyHelper
{
    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param array                               $value
     *
     * @return array
     */
    public static function setResponseDataTypeAndId(array &$mappings, array &$value)
    {
        if (empty($type = $value[Serializer::CLASS_IDENTIFIER_KEY]) || (empty($mappings[$type]))) {
            return [];
        }

        if (!is_scalar($type)) {
            return self::setResponseDataTypeAndId($mappings, $type);
        }

        $finalType = ($mappings[$type]->getClassAlias()) ? $mappings[$type]->getClassAlias() : $type;
        $ids = self::getIdValues($mappings, $value, $type);

        return [
            JsonApiTransformer::TYPE_KEY => RecursiveFormatterHelper::namespaceAsArrayKey($finalType),
            JsonApiTransformer::ID_KEY => \implode(JsonApiTransformer::ID_SEPARATOR, $ids),
        ];
    }

    /**
     * @param array $mappings
     * @param array $value
     * @param $type
     *
     * @return array
     */
    public static function getIdValues(array $mappings, array $value, $type)
    {
        $ids = [];
        foreach ($value as $propertyName => $v) {
            if (\in_array($propertyName, RecursiveFormatterHelper::getIdProperties($mappings, $type), true)) {
                $id = RecursiveFormatterHelper::getIdValue($value[$propertyName]);
                $ids[] = (\is_array($id)) ? \implode(JsonApiTransformer::ID_SEPARATOR, $id) : $id;
            }
        }

        return $ids;
    }

    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param string                              $propertyName
     * @param string                              $type
     *
     * @return bool
     */
    public static function isAttributeProperty(array &$mappings, $propertyName, $type)
    {
        return Serializer::CLASS_IDENTIFIER_KEY !== $propertyName
        && !in_array($propertyName, RecursiveFormatterHelper::getIdProperties($mappings, $type));
    }
}
