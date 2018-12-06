<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableRemoveModal;


trait Create
{
    /**
     * @var IVariableRemoveModal
     * @inject
     */
    public $IStructure_VariableRemoveModal;


    /**
     * Modální okno pro odstranění
     * @return VariableRemoveModal
     */
    protected function createComponentStructureVariableRemoveModal() : object
    {
        $control = $this->IStructure_VariableRemoveModal->create();

        $control->onRemove[] = function ()
        {
            $this->getComponent('structureVariableSummaryModal')->redrawControl('modal');
            $this->getComponent('structureRecipeSummaryModal')->redrawControl('modal');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}