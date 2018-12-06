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
use Wakers\BaseModule\Util\NestedSet;
use Wakers\LangModule\Database\Lang;
use Wakers\PageModule\Database\Page;
use Wakers\StructureModule\Database\StructureQuery;
use Wakers\StructureModule\Entity\StructureResult;


class PrinterRepository
{
    /**
     * @param Lang $lang
     * @param array $categorySlugs
     * @return int
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countByCategorySlugs(Lang $lang, array $categorySlugs) : int
    {
        return StructureQuery::create()
            ->useStructureInCategoryQuery()
                ->useCategoryQuery()
                    ->filterByLang($lang)
                    ->filterBySlug($categorySlugs, Criteria::IN)
                ->endUse()
            ->endUse()
            ->count();
    }


    /**
     * @param Lang $lang
     * @param array|string[] $categorySlugs
     * @param int|NULL $paginationOffset
     * @param int|NULL $paginationLimit
     * @param string $sort
     * @return StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByCategorySlugs(
        Lang $lang,
        array $categorySlugs,
        ?int $paginationOffset,
        ?int $paginationLimit,
        string $sort
    ) : array
    {
        $preQuery = StructureQuery::create()

            ->useStructureInCategoryQuery()
                ->useCategoryQuery()
                    ->filterByLang($lang)
                    ->filterBySlug($categorySlugs, Criteria::IN)
                ->endUse()
            ->endUse()

            ->_if($paginationLimit !== NULL && $paginationOffset !== NULL)
                ->setOffset($paginationOffset)
                ->setLimit($paginationLimit)
            ->_endif()

            ->orderByCreatedAt($sort)
            ->find();

        $mainQuery = StructureQuery::create()
            ->filterById(array_keys($preQuery->toKeyIndex()), Criteria::IN)
            ->joinWithStructureInCategory()

            ->joinWithRecipeSlug()
            ->useRecipeSlugQuery()
                ->joinWithRecipe()
            ->endUse()

            ->joinWithStructureValue()
            ->useStructureValueQuery()
                ->joinWithRecipeVariable()
                ->leftJoinWithStructureValueFile()
                ->leftJoinWithLinkToUrl()
            ->endUse()

            ->orderByCreatedAt($sort);

        $result = [];

        foreach ($mainQuery->find() as $structure)
        {
            $result[] = new StructureResult($structure);
        }

        return $result;
    }

    /**
     * @param Lang $lang
     * @param array $categorySlugs
     * @param string $sort
     * @return StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findRecursiveByCategorySlugs(Lang $lang, array $categorySlugs, string $sort) : array
    {
        $trees = [
            'item' => NULL,
            'descendants' => []
        ];

        $roots = StructureQuery::create()

            ->joinWithStructureInCategory()
            ->useStructureInCategoryQuery()
                ->useCategoryQuery()
                    ->filterByLang($lang)
                    ->filterBySlug($categorySlugs, Criteria::IN)
                ->endUse()
            ->endUse()

            ->joinWithRecipeSlug()
            ->useRecipeSlugQuery()
                ->joinWithRecipe()
            ->endUse()

            ->joinWithStructureValue()
            ->useStructureValueQuery()
                ->joinWithRecipeVariable()
                ->leftJoinWithStructureValueFile()
                ->leftJoinWithLinkToUrl()
            ->endUse()

            ->orderByCreatedAt($sort)
            ->filterByTreeLevel(1)
            ->find();

        // TODO: Lepší řešení, než dotaz na každého parenta
        foreach ($roots as $root)
        {
            $ns = new NestedSet('CreatedAt', $sort);
            $structureResults = new ObjectCollection;
            $structureResults->append(new StructureResult($root));

            $descendants = StructureQuery::create()
                ->joinWithStructureInCategory()

                ->joinWithRecipeSlug()
                ->useRecipeSlugQuery()
                    ->joinWithRecipe()
                ->endUse()

                ->joinWithStructureValue()
                ->useStructureValueQuery()
                    ->joinWithRecipeVariable()
                    ->leftJoinWithStructureValueFile()
                    ->leftJoinWithLinkToUrl()
                ->endUse()

                ->filterByTreeLeft($root->getTreeLeft(), Criteria::GREATER_THAN)
                ->filterByTreeRight($root->getTreeRight(), Criteria::LESS_THAN)
                ->orderByTreeLeft()
                ->find();

            if ($descendants)
            {
                foreach ($descendants as $descendant)
                {
                    $structureResults->append(new StructureResult($descendant));
                }
            }

            $trees['descendants'][] = $ns->getTree($structureResults, $root->getLeftValue() - 1)[0];
        }

        return $trees;
    }


    /**
     * @param array $recipeSlugs
     * @param string $sort
     * @param Page|NULL $page
     * @return array|StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipeSlugsAndPage(array $recipeSlugs, string $sort, Page $page = NULL) : array
    {
        $result = [];

        foreach (
            StructureQuery::create()
                ->_if($page !== NULL)
                    ->useStructureInPageQuery()
                        ->filterByPage($page)
                    ->endUse()
                ->_endif()

                ->joinWithRecipeSlug()
                ->useRecipeSlugQuery()
                    ->filterBySlug($recipeSlugs, Criteria::IN)
                    ->joinWithRecipe()
                ->endUse()

                ->joinWithStructureValue()
                ->useStructureValueQuery()
                    ->joinWithRecipeVariable()
                    ->leftJoinWithStructureValueFile()
                    ->leftJoinWithLinkToUrl()
                ->endUse()

                ->orderByCreatedAt($sort)
                ->find() as $structure)
        {
            $result[] = new StructureResult($structure);
        }

        return $result;
    }


    /**
     * @param array $recipeSlugs
     * @param string $sort
     * @param Page|NULL $page
     * @return array|StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findRecursiveByRecipeSlugsAndPage(array $recipeSlugs, string $sort, Page $page = NULL) : array
    {
        $trees = [
            'item' => NULL,
            'descendants' => []
        ];

        $roots = StructureQuery::create()
            ->_if($page !== NULL)
                ->useStructureInPageQuery()
                    ->filterByPage($page)
                ->endUse()
            ->_endif()

            ->joinWithRecipeSlug()
            ->useRecipeSlugQuery()
                ->filterBySlug($recipeSlugs, Criteria::IN)
                ->joinWithRecipe()
            ->endUse()

            ->joinWithStructureValue()
            ->useStructureValueQuery()
                ->joinWithRecipeVariable()
                ->leftJoinWithStructureValueFile()
                ->leftJoinWithLinkToUrl()
            ->endUse()

            ->orderByCreatedAt($sort)
            ->filterByTreeLevel(1)
            ->find();

        foreach ($roots as $root)
        {
            $ns = new NestedSet('CreatedAt', $sort);
            $structureResults = new ObjectCollection;
            $structureResults->append(new StructureResult($root));

            $descendants = StructureQuery::create()
                ->joinWithRecipeSlug()
                ->useRecipeSlugQuery()
                    ->filterBySlug($recipeSlugs, Criteria::IN)
                    ->joinWithRecipe()
                ->endUse()

                ->joinWithStructureValue()
                ->useStructureValueQuery()
                    ->joinWithRecipeVariable()
                    ->leftJoinWithStructureValueFile()
                    ->leftJoinWithLinkToUrl()
                ->endUse()

                ->filterByTreeLeft($root->getTreeLeft(), Criteria::GREATER_THAN)
                ->filterByTreeRight($root->getTreeRight(), Criteria::LESS_THAN)
                ->orderByTreeLeft()
                ->find();

            if ($descendants)
            {
                foreach ($descendants as $descendant)
                {
                    $structureResults->append(new StructureResult($descendant));
                }
            }

            $trees['descendants'][] = $ns->getTree($structureResults, $root->getLeftValue() - 1)[0];
        }

        return $trees;
    }
}