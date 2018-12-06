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
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValueFile;
use Wakers\StructureModule\Database\StructureValueFileQuery;


class StructureValueFileRepository
{
    const FILE_PATH = 'structure';

    /**
     * @param int $id
     * @return StructureValueFile|NULL
     */
    public function findOneById(int $id) : ?StructureValueFile
    {
        return StructureValueFileQuery::create()
            ->joinWithStructureValue()
            ->useStructureValueQuery()
                ->joinWithStructure()
            ->endUse()
            ->findOneById($id);
    }


    /**
     * @param Structure $structure
     * @return ObjectCollection|StructureValueFile[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByStructure(Structure $structure) : ObjectCollection
    {
        return StructureValueFileQuery::create()
            ->joinWithStructureValue()
            ->useStructureValueQuery()
                ->filterByStructure($structure)
            ->endUse()
            ->find();
    }


    /**
     * @param Recipe $recipe
     * @param Structure $structure
     * @return ObjectCollection|StructureValueFile[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByRecipeAndStructure(Recipe $recipe, Structure $structure) : ObjectCollection
    {
        return StructureValueFileQuery::create()
            ->joinWithStructureValue()
            ->useStructureValueQuery()
                ->joinWithRecipeVariable()
                ->useRecipeVariableQuery()
                    ->filterByRecipe($recipe)
                ->endUse()
                ->filterByStructure($structure)
            ->endUse()
            ->find();
    }
}