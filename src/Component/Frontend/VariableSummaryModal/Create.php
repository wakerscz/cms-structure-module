<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableSummaryModal;


trait Create
{
    /**
     * @var IVariableSummaryModal
     * @inject
     */
    public $IStructure_VariableSummaryModal;


    /**
     * Editace proměnných struktury
     * @return VariableSummaryModal
     */
    protected function createComponentStructureVariableSummaryModal() : object
    {
        $control = $this->IStructure_VariableSummaryModal->create();

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}