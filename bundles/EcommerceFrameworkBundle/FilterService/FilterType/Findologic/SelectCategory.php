<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Bundle\EcommerceFrameworkBundle\FilterService\FilterType\Findologic;

use Pimcore\Bundle\EcommerceFrameworkBundle\FilterService\FilterType\AbstractFilterType;
use Pimcore\Bundle\EcommerceFrameworkBundle\IndexService\ProductList\ProductListInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\AbstractCategory;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\AbstractFilterDefinitionType;

class SelectCategory extends \Pimcore\Bundle\EcommerceFrameworkBundle\FilterService\FilterType\SelectCategory
{
    const FIELDNAME = 'cat';

    public function prepareGroupByValues(AbstractFilterDefinitionType $filterDefinition, ProductListInterface $productList)
    {
        //$productList->prepareGroupBySystemValues($filterDefinition->getField(), true);
    }

    public function getFilterFrontend(AbstractFilterDefinitionType $filterDefinition, ProductListInterface $productList, $currentFilter)
    {
        $rawValues = $productList->getGroupByValues(self::FIELDNAME, true);
        $values = [];

        foreach ($rawValues as $v) {
            $values[$v['label']] = ['value' => $v['label'], 'count' => $v['count']];
        }

        return $this->render($this->getTemplate($filterDefinition), [
            'hideFilter' => $filterDefinition->getRequiredFilterField() && empty($currentFilter[$filterDefinition->getRequiredFilterField()]),
            'label' => $filterDefinition->getLabel(),
            'currentValue' => $currentFilter[$filterDefinition->getField()],
            'values' => array_values($values),
            'fieldname' => self::FIELDNAME,
            'rootCategory' => $filterDefinition->getRootCategory(),
            'resultCount' => $productList->count()
        ]);
    }

    public function addCondition(AbstractFilterDefinitionType $filterDefinition, ProductListInterface $productList, $currentFilter, $params, $isPrecondition = false)
    {
        $value = $params[$filterDefinition->getField()] ?? null;
        $isReload = $params['is_reload'] ?? null;

        if ($value == AbstractFilterType::EMPTY_STRING) {
            $value = null;
        } elseif (empty($value) && !$isReload) {
            $value = $filterDefinition->getPreSelect();
            if (is_object($value)) {
                $value = $value->getId();
            }
        }

        $currentFilter[$filterDefinition->getField()] = $value;

        if (!empty($value)) {
            $value = trim($value);
            if (AbstractCategory::getById($value)) {
                $productList->setCategory(AbstractCategory::getById($value));
            }
        }

        return $currentFilter;
    }
}
