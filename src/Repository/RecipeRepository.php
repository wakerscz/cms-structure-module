<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Repository;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeQuery;


class RecipeRepository
{
    /**
     * @param string $slug
     * @return Recipe|NULL
     */
    public function findOneBySlug(string $slug) : ?Recipe
    {
        return RecipeQuery::create()
            ->useRecipeSlugQuery()
                ->filterBySlug($slug)
            ->endUse()
            ->findOne();
    }


    /**
     * @param int $id
     * @return Recipe|NULL
     */
    public function findOneById(int $id) : ?Recipe
    {
        return RecipeQuery::create()
            ->findOneById($id);
    }


    /**
     * @return ObjectCollection|Recipe[]
     */
    public function findAll() : ObjectCollection
    {
        return RecipeQuery::create()
            ->find();
    }


    /**
     * @return ObjectCollection|Recipe[]
     */
    public function findAllJoinVariables() : ObjectCollection
    {
        return RecipeQuery::create()
            ->leftJoinWithRecipeVariable()
            ->leftJoinWithRecipeSlug()
            ->orderByName()
            ->find();
    }
}