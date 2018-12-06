<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\StructureRemoveModal;


trait Create
{
    /**
     * @var IStructureRemoveModal
     * @inject
     */
    public $IStructure_StructureRemoveModal;


    /**
     * Modální okno pro odstranění
     * @return StructureRemoveModal
     */
    protected function createComponentStructureStructureRemoveModal() : object
    {
        $control = $this->IStructure_StructureRemoveModal->create();

        $control->onRemove[] = function () use ($control)
        {
            $control->redrawControl();
            $this->redrawPrinters();
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}