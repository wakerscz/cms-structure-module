<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeModal;


trait Create
{
    /**
     * @var IRecipeModal
     * @inject
     */
    public $IStructure_RecipeModal;


    /**
     * Modální okno pro definování struktur
     * @return RecipeModal
     */
    protected function createComponentStructureRecipeModal() : object
    {
        $control = $this->IStructure_RecipeModal->create();


        $control->onSave[] = function () use ($control)
        {
            $control->redrawControl('recipeForm');

            $this->getComponent('structureRecipeSummaryModal')->redrawControl('modal');
        };


        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('recipeForm');
        };

        return $control;
    }
}