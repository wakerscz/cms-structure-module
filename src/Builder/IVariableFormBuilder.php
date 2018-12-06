<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Builder;


use Nette\ComponentModel\IComponent;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValue;


/**
 * TODO: umožnit rozšíření o vlastní proměnné (odstranit konstanty, možnost přidávat teplates a zpracovávat formulář)
 * Interface IValueFormBuilder
 * @package Wakers\StructureModule\Builder
 */
interface IVariableFormBuilder
{
    /**
     * @param Recipe $recipe
     * @param RecipeVariable $recipeVariable
     * @param Structure|null $structure
     * @param StructureValue|null $structureValue
     * @return IComponent
     */
    public function buildComponent(
        Recipe $recipe,
        RecipeVariable $recipeVariable,
        ?Structure $structure,
        ?StructureValue $structureValue
    ) : IComponent;
}