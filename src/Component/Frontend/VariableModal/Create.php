<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableModal;


trait Create
{
    /**
     * @var IVariableModal
     * @inject
     */
    public $IStructure_VariableModal;


    /**
     * Editace proměnných ve struktuře
     * @return VariableModal
     */
    protected function createComponentStructureVariableModal() : object
    {
        $control = $this->IStructure_VariableModal->create();

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        $control->onSave[] = function () use ($control)
        {
            $control->redrawControl('modal');

            $this->getComponent('structureVariableSummaryModal')->redrawControl('modal');
            $this->getComponent('structureRecipeSummaryModal')->redrawControl('modal');
        };

        return $control;
    }
}