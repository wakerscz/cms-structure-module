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
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValue;
use Wakers\StructureModule\Database\StructureValueFile;
use Wakers\StructureModule\Database\StructureValueQuery;


class StructureValueRepository
{
    /**
     * @param Structure $structure
     * @return ObjectCollection|StructureValue[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByStructure(Structure $structure) : ObjectCollection
    {
        return StructureValueQuery::create()
            ->filterByStructure($structure)
            ->joinRecipeVariable()
            ->find();
    }


    /**
     * @param Structure $structure
     * @param string $variableSlug
     * @return StructureValue
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findOneByStructureAndVariableSlug(Structure $structure, string $variableSlug) : ?StructureValue
    {
        return StructureValueQuery::create()
            ->filterByStructure($structure)
            ->joinRecipeVariable()
            ->useRecipeVariableQuery()
                ->filterBySlug($variableSlug)
            ->endUse()
            ->findOne();
    }


    /**
     * @param Structure $structure
     * @return array|ObjectCollection[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findStructureVariableFilesByStructure(Structure $structure) : array
    {
        $sv = [];

        $structureValues = StructureValueQuery::create()
            ->filterByStructure($structure)

            // TODO: Ostaní JOINY načítájí smazaná data (s flagem deleted) a po uložení ignorují nastavené hodnoty - WTF (nějaké cashování) ?
            //->joinWithStructureValueFile() // TODO: ignoruje řazení v JOIN column - proč ??
            ->joinStructureValueFile(NULL, Criteria::INNER_JOIN)
            ->joinWithRecipeVariable()
            ->find();

        foreach ($structureValues as $structureValue)
        {
            $slug = $structureValue->getRecipeVariable()->getSlug();

            // TODO: Odstranit řazení (po výměně ORM) - Hofix pro řazení
            $files = $structureValue->getStructureValueFiles()->toKeyIndex();

            // OrderByUploadedAt ASC
            usort($files, function (StructureValueFile $a, StructureValueFile $b)
            {
                return $a->getUploadedAt() > $b->getUploadedAt();
            });

            $sv[$slug] = new ObjectCollection($files);
        }

        return $sv;
    }
}