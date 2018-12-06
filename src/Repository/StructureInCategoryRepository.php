<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Repository;


use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Wakers\StructureModule\Database\Base\StructureInCategory;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureInCategoryQuery;


class StructureInCategoryRepository
{
    /**
     * @param Structure $structure
     * @return ObjectCollection|StructureInCategory[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByStructure(Structure $structure) : ObjectCollection
    {
        return StructureInCategoryQuery::create()
            ->filterByStructure($structure)
            ->find();
    }


    /**
     * @param Recipe $recipe
     * @param array $remainingRootIds
     * @return ObjectCollection|StructureInCategory[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findRelatedExceptRemaining(Recipe $recipe, array $remainingRootIds = [])
    {
        $rootsToRemove = StructureInCategoryQuery::create()
            ->joinWithCategory()
            ->useCategoryQuery()
                ->joinWithRecipeCategoryAllowed()
                ->useRecipeCategoryAllowedQuery()
                    ->filterByRecipe($recipe)
                    ->filterByCategoryId($remainingRootIds, Criteria::NOT_IN)
                ->endUse()
            ->endUse()
            ->find();


        $idsToRemove = [];

        // Roots descendants to remove
        foreach ($rootsToRemove as $root)
        {
            $idsToRemove[] = $root->getCategory()->getId();

            foreach ($root->getCategory()->getDescendants() as $category)
            {
                $idsToRemove[] = $category->getId();
            }
        }

        $structureInCategoriesForRemove = StructureInCategoryQuery::create()
            ->filterByCategoryId($idsToRemove, Criteria::IN)
            ->joinWithStructure()
            ->useStructureQuery()
                ->useRecipeSlugQuery()
                    ->filterByRecipe($recipe)
                ->endUse()
            ->endUse()
            ->find();

        return $structureInCategoriesForRemove;
    }
}