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
     * @param bool $filterByPagePublished
     * @return int
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countByCategorySlugs(Lang $lang, array $categorySlugs, bool $filterByPagePublished) : int
    {
        return StructureQuery::create()
            ->useStructureInCategoryQuery()
                ->useCategoryQuery()
                    ->filterByLang($lang)
                    ->filterBySlug($categorySlugs, Criteria::IN)
                ->endUse()
            ->endUse()

            ->_if($filterByPagePublished)
                ->useStructureInPageQuery()
                    ->usePageQuery()
                        ->filterByPublished(TRUE)
                    ->endUse()
                ->endUse()
            ->_endif()

            ->count();
    }


    /**
     * @param Lang $lang
     * @param array $categorySlugs
     * @param int|null $paginationOffset
     * @param int|null $paginationLimit
     * @param string $sort
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByCategorySlugs(Lang $lang, array $categorySlugs, ?int $paginationOffset, ?int $paginationLimit, string $sort, bool $filterByPagePublished) : array
    {
        $preQuery = StructureQuery::create()

            ->useStructureInCategoryQuery()
                ->useCategoryQuery()
                    ->filterByLang($lang)
                    ->filterBySlug($categorySlugs, Criteria::IN)
                ->endUse()
            ->endUse()

            ->_if($filterByPagePublished)
                ->useStructureInPageQuery()
                    ->usePageQuery()
                        ->filterByPublished(TRUE)
                    ->endUse()
                ->endUse()
            ->_endif()

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

            ->_if($filterByPagePublished)
                ->useStructureInPageQuery()
                    ->usePageQuery()
                        ->filterByPublished(TRUE)
                    ->endUse()
                ->endUse()
            ->_endif()

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
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findRecursiveByCategorySlugs(Lang $lang, array $categorySlugs, string $sort, bool $filterByPagePublished) : array
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

            ->_if($filterByPagePublished)
                ->useStructureInPageQuery()
                    ->usePageQuery()
                        ->filterByPublished(TRUE)
                    ->endUse()
                ->endUse()
            ->_endif()

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

                ->_if($filterByPagePublished)
                    ->useStructureInPageQuery()
                        ->usePageQuery()
                            ->filterByPublished(TRUE)
                        ->endUse()
                    ->endUse()
                ->_endif()

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
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipeSlugsAndPage(array $recipeSlugs, string $sort, ?Page $page, bool $filterByPagePublished) : array
    {
        $result = [];

        foreach (
            StructureQuery::create()
                ->_if($page !== NULL)
                    ->useStructureInPageQuery()
                        ->filterByPage($page)

                        ->_if($filterByPagePublished)
                            ->usePageQuery()
                                ->filterByPublished(TRUE)
                            ->endUse()
                        ->_endif()

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
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findRecursiveByRecipeSlugsAndPage(array $recipeSlugs, string $sort, ?Page $page, bool $filterByPagePublished) : array
    {
        $trees = [
            'item' => NULL,
            'descendants' => []
        ];

        $roots = StructureQuery::create()
            ->_if($page !== NULL)
                ->useStructureInPageQuery()
                    ->filterByPage($page)

                    ->_if($filterByPagePublished)
                        ->usePageQuery()
                            ->filterByPublished(TRUE)
                        ->endUse()
                    ->_endif()

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

                ->_if($filterByPagePublished)
                    ->useStructureInPageQuery()
                        ->usePageQuery()
                            ->filterByPublished(TRUE)
                        ->endUse()
                    ->endUse()
                ->_endif()

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