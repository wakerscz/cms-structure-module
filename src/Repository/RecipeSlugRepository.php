<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Repository;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\StructureModule\Database\Base\RecipeSlugQuery;
use Wakers\StructureModule\Database\RecipeSlug;


class RecipeSlugRepository
{
    /**
     * @param string $slug
     * @return RecipeSlug|NULL
     */
    public function findOneBySlug(string $slug) : ?RecipeSlug
    {
        return RecipeSlugQuery::create()
            ->findOneBySlug($slug);
    }


    /**
     * @param int $id
     * @return RecipeSlug|null
     */
    public function findOneById(int $id) : ?RecipeSlug
    {
        return RecipeSlugQuery::create()
            ->findOneById($id);
    }


    /**
     * @param int $recipeId
     * @return ObjectCollection|RecipeSlug[]
     */
    public function findByRecipeId(int $recipeId) : ObjectCollection
    {
        return RecipeSlugQuery::create()
            ->filterByRecipeId($recipeId)
            ->find();
    }
}