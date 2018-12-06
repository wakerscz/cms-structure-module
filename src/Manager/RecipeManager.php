<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Repository\RecipeRepository;


class RecipeManager extends AbstractDatabase
{
    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * RecipeManager constructor.
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }


    /**
     * @param Recipe|NULL $recipe
     * @param string $name
     * @param bool $isDynamic
     * @param int $maxInstances
     * @param int $maxCategories
     * @param int $maxDepth
     * @param int $allowedParentId
     * @return Recipe
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function save(
        ?Recipe $recipe,
        string $name,
        bool $isDynamic,
        int $maxInstances,
        int $maxCategories,
        int $maxDepth,
        int $allowedParentId
    ) : Recipe
    {
        if (!$recipe)
        {
            $recipe = new Recipe;
        }

        $recipe->setName($name);
        $recipe->setIsDynamic($isDynamic);
        $recipe->setMaxInstances($maxInstances);
        $recipe->setMaxCategories($maxCategories);
        $recipe->setMaxDepth($maxDepth);

        if ($allowedParentId <= 0)
        {
            $allowedParentId = NULL;
        }

        $recipe->setAllowedParentId($allowedParentId);
        $recipe->save();

        return $recipe;
    }


    /**
     * @param Recipe $recipe
     * @param ObjectCollection $allowedCategories
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveAllowedCategories(Recipe $recipe, ObjectCollection $allowedCategories) : void
    {
        $recipe->setRecipeCategoryAlloweds($allowedCategories);
        $recipe->save();
    }


    /**
     * @param Recipe $recipe
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(Recipe $recipe) : void
    {
        $recipe->delete();
    }
}