<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeRemoveModal;


trait Create
{
    /**
     * @var IRecipeRemoveModal
     * @inject
     */
    public $IStructure_RecipeRemoveModal;


    /**
     * Modální okno pro odstranění
     * @return RecipeRemoveModal
     */
    protected function createComponentStructureRecipeRemoveModal() : object
    {
        $control = $this->IStructure_RecipeRemoveModal->create();

        $control->onRemove[] = function ()
        {
            $this->getComponent('structureRecipeModal')->redrawControl('modal');
            $this->getComponent('structureRecipeSummaryModal')->redrawControl('modal');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}