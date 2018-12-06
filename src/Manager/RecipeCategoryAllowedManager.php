<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\LangModule\Database\Lang;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeCategoryAllowed;
use Wakers\StructureModule\Repository\RecipeCategoryAllowedRepository;


class RecipeCategoryAllowedManager
{
    /**
     * @var RecipeCategoryAllowedRepository
     */
    protected $recipeCategoryAllowedRepository;


    /**
     * RecipeCategoryAllowedManager constructor.
     * @param RecipeCategoryAllowedRepository $recipeCategoryAllowedRepository
     */
    public function __construct(RecipeCategoryAllowedRepository $recipeCategoryAllowedRepository)
    {
        $this->recipeCategoryAllowedRepository = $recipeCategoryAllowedRepository;
    }


    /**
     * @param Recipe $recipe
     * @param Lang $lang
     * @param array $allowedIds
     * @return ObjectCollection|RecipeCategoryAllowed[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function makeDiff(Recipe $recipe, Lang $lang, array $allowedIds = []) : ObjectCollection
    {
        $categories = $this->recipeCategoryAllowedRepository->findByRecipe($recipe);
        $categoriesById = $categories->toKeyIndex('CategoryId');
        $categoriesKeys = array_keys($categoriesById);

        $toRemove = array_diff($categoriesKeys, $allowedIds);
        $toAdd = array_diff($allowedIds, $categoriesKeys);

        foreach ($toAdd as $id)
        {
            $category = new RecipeCategoryAllowed;
            $category->setRecipe($recipe);
            $category->setCategoryId($id);
            $categories->append($category);
        }

        foreach ($toRemove as $id)
        {
            /**
             * @var RecipeCategoryAllowed $category
             */
            $category = $categoriesById[$id];

            if ($category->getCategory()->getLang() === $lang)
            {
                $categories->removeObject($category);
            }
        }

        return $categories;
    }
}