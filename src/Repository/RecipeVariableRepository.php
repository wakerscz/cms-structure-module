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
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Database\RecipeVariableQuery;


class RecipeVariableRepository
{
    /**
     * @param Recipe $recipe
     * @return ObjectCollection|RecipeVariable[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipe(Recipe $recipe) : ObjectCollection
    {
        return $recipe->getRecipeVariables();
    }


    /**
     * @param Recipe $recipe
     * @return ObjectCollection|RecipeVariable[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findNoFileVariables(Recipe $recipe) : ObjectCollection
    {
        return RecipeVariableQuery::create()
            ->filterByRecipe($recipe)
            ->filterByType([RecipeVariable::TYPE_IMAGES, RecipeVariable::TYPE_FILES], Criteria::NOT_IN)
            ->orderById()
            ->find();
    }


    /**
     * @param Recipe $recipe
     * @return ObjectCollection
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findFileVariables(Recipe $recipe) : ObjectCollection
    {
        $result = RecipeVariableQuery::create()
            ->filterByRecipe($recipe)
            ->filterByType([RecipeVariable::TYPE_IMAGES, RecipeVariable::TYPE_FILES])
            ->orderById()
            ->find();

        foreach ($result as $value)
        {
            $value->setVirtualColumn('Files', []);
        }

        return $result;
    }


    /**
     * @param int $id
     * @return RecipeVariable|NULL
     */
    public function findOneById(int $id) : ?RecipeVariable
    {
        return RecipeVariableQuery::create()
            ->findOneById($id);
    }


    /**
     * @param Recipe $recipe
     * @param string $slug
     * @return RecipeVariable|NULL
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findOneByRecipeSlug(Recipe $recipe, string $slug) : ?RecipeVariable
    {
        return RecipeVariableQuery::create()
            ->filterByRecipe($recipe)
            ->findOneBySlug($slug);
    }
}