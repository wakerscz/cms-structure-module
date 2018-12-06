<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Nette\Utils\ArrayHash;
use Propel\Runtime\Collection\ObjectCollection;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValue;
use Wakers\StructureModule\Repository\StructureValueRepository;


class StructureValueManager
{
    /**
     * @var StructureValueRepository
     */
    protected $structureValueRepository;


    /**
     * StructureValueManager constructor.
     * @param StructureValueRepository $structureValueRepository
     */
    public function __construct(StructureValueRepository $structureValueRepository)
    {
        $this->structureValueRepository = $structureValueRepository;
    }


    /**
     * @param Structure $structure
     * @param ObjectCollection $noFileVariables
     * @param ArrayHash $values
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveNoFileFields(Structure $structure, ObjectCollection $noFileVariables, ArrayHash $values) : void
    {
        $recipeVariables = $noFileVariables->toKeyIndex('Slug');
        $structureValues = $this->structureValueRepository->findByStructure($structure);

        $structureValuesBySlugs = [];

        foreach ($structureValues as $structureValue)
        {
            $variableSlug = $structureValue->getRecipeVariable()->getSlug();
            $structureValuesBySlugs[$variableSlug] = $structureValue;
        }

        foreach ($values as $variableSlug => $value)
        {
            $structureValue = new StructureValue;

            if (key_exists($variableSlug, $structureValuesBySlugs))
            {
                $structureValue = $structureValuesBySlugs[$variableSlug];
            }

            /** @var RecipeVariable $recipeVariable */
            $recipeVariable = $recipeVariables[$variableSlug];

            // Pokud se jedná o interní odkaz
            if ($recipeVariable->getType() === RecipeVariable::TYPE_LINK_INTERNAL)
            {
                $structureValue->setLinkToUrlId($value);
                $value = NULL;
            }

            $structureValue->setContent($value);
            $structureValue->setStructure($structure);
            $structureValue->setRecipeVariable($recipeVariable);
            $structureValue->save();
        }
    }


    /**
     * @param Structure $structure
     * @param RecipeVariable $recipeVariable
     * @return StructureValue
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function createEmptyVariable(Structure $structure, RecipeVariable $recipeVariable) : StructureValue
    {
        $structureValue = new StructureValue;
        $structureValue->setStructure($structure);
        $structureValue->setRecipeVariable($recipeVariable);
        $structureValue->save();

        return $structureValue;
    }
}