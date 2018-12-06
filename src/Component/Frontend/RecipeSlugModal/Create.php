<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSlugModal;


trait Create
{
    /**
     * @var IRecipeSlugModal
     * @inject
     */
    public $IStructure_RecipeSlugModal;


    protected function createComponentStructureRecipeSlugModal() : object
    {
        $control = $this->IStructure_RecipeSlugModal->create();

        $control->onSave[] = function () use ($control)
        {
            $control->redrawControl('modal'); // SA
            $control->redrawControl('slugForm');
            $control->redrawControl('slugSummary');

            $this->getComponent('structureRecipeSummaryModal')->redrawControl('modal');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal'); // SA
            $control->redrawControl('slugForm');
            $control->redrawControl('slugSummary');
        };

        return $control;
    }
}