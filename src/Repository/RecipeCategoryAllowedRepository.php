<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\StructureModule\Repository;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\LangModule\Database\Lang;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeCategoryAllowed;
use Wakers\StructureModule\Database\RecipeCategoryAllowedQuery;


class RecipeCategoryAllowedRepository
{
    /**
     * @param Recipe $recipe
     * @return ObjectCollection|RecipeCategoryAllowed[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipe(Recipe $recipe) : ObjectCollection
    {
        return RecipeCategoryAllowedQuery::create()
            ->filterByRecipe($recipe)
            ->joinWithCategory()
            ->find();
    }


    /**
     * @param Recipe $recipe
     * @param Lang $lang
     * @return ObjectCollection|RecipeCategoryAllowed[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipeLang(Recipe $recipe, Lang $lang) : ObjectCollection
    {
        return RecipeCategoryAllowedQuery::create()
            ->filterByRecipe($recipe)
            ->joinWithCategory()
            ->useCategoryQuery()
                ->filterByLang($lang)
            ->endUse()
            ->find();
    }
}