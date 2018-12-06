<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSlugRemoveModal;


trait Create
{
    /**
     * @var IRecipeSlugRemoveModal
     * @inject
     */
    public $IStructure_RecipeSlugRemoveModal;


    /**
     * Modální okno pro odstranění
     * @return RecipeSlugRemoveModal
     */
    protected function createComponentStructureRecipeSlugRemoveModal() : object
    {
        $control = $this->IStructure_RecipeSlugRemoveModal->create();

        $control->onRemove[] = function ()
        {
            $this->getComponent('structureRecipeSlugModal')->redrawControl('modal'); // SA
            $this->getComponent('structureRecipeSlugModal')->redrawControl('slugForm');
            $this->getComponent('structureRecipeSlugModal')->redrawControl('slugSummary');

            $this->getComponent('structureRecipeSummaryModal')->redrawControl('modal');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}