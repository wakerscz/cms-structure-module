<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\StructureModule\Manager;


use Wakers\BaseModule\Database\DatabaseException;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Repository\RecipeVariableRepository;

class RecipeVariableManager
{
    /**
     * @var RecipeVariableRepository
     */
    protected $recipeVariableRepository;


    /**
     * RecipeVariableManager constructor.
     * @param RecipeVariableRepository $recipeVariableRepository
     */
    public function __construct(RecipeVariableRepository $recipeVariableRepository)
    {
        $this->recipeVariableRepository = $recipeVariableRepository;
    }


    /**
     * @param Recipe $recipe
     * @param RecipeVariable|NULL $variable
     * @param string $type
     * @param string $label
     * @param string $slug
     * @param bool $isRequired
     * @param string|NULL $tooltip
     * @param string|NULL $regexPattern
     * @param string|NULL $regexMessage
     * @param string|NULL $allowedTypes
     * @param int|NULL $maxFiles
     * @param float|NULL $maxFileSize
     * @param string|NULL $items
     * @return RecipeVariable
     * @throws DatabaseException
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Exception
     */
    public function save(
        Recipe $recipe,
        ?RecipeVariable $variable,
        string $type,
        string $label,
        string $slug,
        bool $isRequired,
        ?string $tooltip,
        ?string $regexPattern,
        ?string $regexMessage,
        ?string $allowedTypes,
        int $maxFiles,
        float $maxFileSize,
        ?string $items
    ) : RecipeVariable
    {
        $variableBySlug = $this->recipeVariableRepository->findOneByRecipeSlug($recipe, $slug);

        if ($variableBySlug && $variable !== $variableBySlug)
        {
            throw new DatabaseException("Slug '{$slug}' v této struktuře již existuje.");
        }

        if ($variable === NULL)
        {
            $variable = new RecipeVariable;
            $variable->setRecipe($recipe);
        }

        $variable->setType($type);
        $variable->setLabel($label);
        $variable->setSlug($slug);
        $variable->setIsRequired($isRequired);
        $variable->setTooltip($tooltip);
        $variable->setRegexPattern($regexPattern);
        $variable->setRegexMessage($regexMessage);
        $variable->setAllowedTypes($allowedTypes);
        $variable->setMaxFiles($maxFiles);
        $variable->setMaxFileSize($maxFileSize);
        $variable->setItems($items);

        $variable->save();

        return $variable;
    }


    /**
     * @param RecipeVariable $variable
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(RecipeVariable $variable) : void
    {
        $variable->delete();
    }
}