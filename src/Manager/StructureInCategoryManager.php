<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureInCategory;
use Wakers\StructureModule\Repository\StructureInCategoryRepository;


class StructureInCategoryManager
{
    /**
     * @var StructureInCategoryManager
     */
    protected $structureInCategoryRepository;


    /**
     * StructureInCategoryManager constructor.
     * @param StructureInCategoryRepository $structureInCategoryRepository
     */
    public function __construct(StructureInCategoryRepository $structureInCategoryRepository)
    {
        $this->structureInCategoryRepository = $structureInCategoryRepository;
    }


    /**
     * @param Structure $structure
     * @param array $categoryIds
     * @return ObjectCollection|StructureInCategory[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function makeDiff(Structure $structure, array $categoryIds = []) : ObjectCollection
    {
        $categories = $this->structureInCategoryRepository->findByStructure($structure);

        $categoriesById = $categories->toKeyIndex('CategoryId');
        $categoriesKeys = array_keys($categoriesById);

        $toRemove = array_diff($categoriesKeys, $categoryIds);
        $toAdd = array_diff($categoryIds, $categoriesKeys);

        foreach ($toAdd as $id)
        {
            $category = new StructureInCategory;
            $category->setStructure($structure);
            $category->setCategoryId($id);
            $categories->append($category);
        }

        foreach ($toRemove as $id)
        {
            /**
             * @var StructureInCategory $category
             */
            $category = $categoriesById[$id];
            $categories->removeObject($category);

        }

        return $categories;
    }


    /**
     * @param Recipe $recipe
     * @param array $remainingRootIds
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function deleteRelatedExceptRemaining(Recipe $recipe, array $remainingRootIds = []) : void
    {
        $categories = $this->structureInCategoryRepository->findRelatedExceptRemaining($recipe, $remainingRootIds);
        $categories->delete();
    }
}