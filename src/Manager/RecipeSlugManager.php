<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeSlug;
use Wakers\StructureModule\Repository\RecipeSlugRepository;


class RecipeSlugManager extends AbstractDatabase
{
    /**
     * @var RecipeSlugRepository
     */
    protected $recipeSlugRepository;


    /**
     * RecipeSlugManager constructor.
     * @param RecipeSlugRepository $recipeSlugRepository
     */
    public function __construct(RecipeSlugRepository $recipeSlugRepository)
    {
        $this->recipeSlugRepository = $recipeSlugRepository;
    }


    /**
     * @param Recipe $recipe
     * @param RecipeSlug|null $recipeSlug
     * @param string $slug
     * @return RecipeSlug
     * @throws DatabaseException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function save(Recipe $recipe, ?RecipeSlug $recipeSlug, string $slug) : RecipeSlug
    {
        if (!$recipeSlug)
        {
            $recipeSlug = new RecipeSlug;
        }

        $recipeSlugBySlug = $this->recipeSlugRepository->findOneBySlug($slug);

        if ($recipeSlugBySlug && $recipeSlug !== $recipeSlugBySlug)
        {
            throw new DatabaseException("Slug '{$slug}' již existuje.'");
        }

        $recipeSlug->setRecipe($recipe);
        $recipeSlug->setSlug($slug);
        $recipeSlug->save();

        return $recipeSlug;
    }


    /**
     * @param RecipeSlug $recipeSlug
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(RecipeSlug $recipeSlug)
    {
        $recipeSlug->delete();
    }
}