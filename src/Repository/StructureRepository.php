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
use Wakers\PageModule\Database\Page;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeSlug;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureQuery;


class StructureRepository
{
    /**
     * Cesta k templates v app
     */
    const TEMPLATE_PATH = __DIR__ . '/../../../../../app/template/structure/';


    /**
     * @param int $id
     * @return Structure|NULL
     */
    public function findOneById(int $id) : ?Structure
    {
        return StructureQuery::create()
            ->findOneById($id);
    }


    /**
     * @param int $id
     * @return Structure|NULL
     */
    public function findOneByIdWithRecipe(int $id) : ?Structure
    {
        return StructureQuery::create()
            ->joinWithRecipeSlug()
            ->useRecipeSlugQuery()
                ->joinWithRecipe()
            ->endUse()
            ->findOneById($id);
    }


    /**
     * @param Recipe $recipe
     * @param int $fromLevel
     * @return ObjectCollection|Structure[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipeAndLevel(Recipe $recipe, int $fromLevel) : ObjectCollection
    {
        return StructureQuery::create()
            ->useRecipeSlugQuery()
                ->filterByRecipe($recipe)
            ->endUse()
            ->filterByTreeLevel($fromLevel, Criteria::GREATER_EQUAL)
            ->find();
    }


    /**
     * @param Recipe $recipe
     * @return ObjectCollection|Recipe[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipe(Recipe $recipe) : ObjectCollection
    {
        return StructureQuery::create()
            ->useRecipeSlugQuery()
                ->filterByRecipe($recipe)
            ->endUse()
            ->find();
    }


    /**
     * @param string $recipeSlug
     * @return int
     */
    public function countByRecipeSlug(string $recipeSlug) : int
    {
        return StructureQuery::create()
            ->useRecipeSlugQuery()
                ->filterBySlug($recipeSlug)
            ->endUse()
            ->count();
    }


    /**
     * @param string $recipeSlug
     * @param Page $page
     * @return int
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countByRecipeSlugAndPage(string $recipeSlug, Page $page) : int
    {
        return StructureQuery::create()
            ->useRecipeSlugQuery()
                ->filterBySlug($recipeSlug)
            ->endUse()
            ->useStructureInPageQuery()
                ->filterByPage($page)
            ->endUse()
            ->count();
    }


    /**
     * @return Structure|NULL ?Structure
     */
    public function findRoot() : ?Structure
    {
        return StructureQuery::create()
            ->findRoot();
    }


    /**
     * @param Structure $structure
     * @return ObjectCollection|Structure[]
     */
    public function findDescendants(Structure $structure)
    {
        return StructureQuery::create()
            ->filterByTreeLeft($structure->getTreeLeft(), Criteria::GREATER_THAN)
            ->filterByTreeLeft($structure->getTreeRight(), Criteria::LESS_THAN)
            ->joinWithRecipeSlug()
            ->useRecipeSlugQuery()
                ->joinWithRecipe()
            ->endUse()
            ->orderByTreeLeft()
            ->find();
    }


    /**
     * @param array|string[] $recipeSlugs
     * @param Structure $structure
     * @return ObjectCollection|Structure[]
     */
    public function findParentsExceptSubParents(array $recipeSlugs, ?Structure $structure) : ObjectCollection
    {
        $result = new ObjectCollection;
        $excludedIds = $structure ? array_keys($this->findDescendants($structure)->toKeyIndex('Id') + [$structure->getId() => $structure]) : [];

        $roots =  StructureQuery::create()
            ->joinWithRecipeSlug()
            ->useRecipeSlugQuery()
                ->filterBySlug($recipeSlugs, Criteria::IN)
            ->endUse()

            ->useRecipeSlugQuery()
                ->joinWithRecipe()
            ->endUse()

            ->orderByTreeLeft()
            ->find();

        foreach ($roots as $root)
        {
            if (!in_array($root->getId(), $excludedIds))
            {
                $result[] = $root;
            }

            foreach ($this->findDescendants($root) as $descendant)
            {
                if (!in_array($descendant->getId(), $excludedIds))
                {
                    $result[] = $descendant;
                }
            }
        }

        return $result;
    }


    /**
     * @param Structure $structure
     * @return int
     */
    public function countSubLevels(Structure $structure)
    {
        $result =  StructureQuery::create()
            ->filterByTreeLeft($structure->getTreeLeft(), Criteria::GREATER_THAN)
            ->filterByTreeLeft($structure->getTreeRight(), Criteria::LESS_THAN)
            ->orderByLevel(Criteria::DESC)
            ->findOne();

        if ($result)
        {
            return $result->getLevel() - $structure->getLevel();
        }

        return 0;
    }
}