<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\StructureModal;


trait Create
{
    /**
     * @var IStructureModal
     * @inject
     */
    public $IStructure_StructureModal;


    /**
     * Modální okno pro nastavení hodnot struktur
     * @return StructureModal
     */
    protected function createComponentStructureStructureModal() : object
    {
        $control = $this->IStructure_StructureModal->create();


        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
            $control->redrawControl('title');

            $control->redrawControl('formNoFile');
            $control->redrawControl('formFile');
            $control->redrawControl('formMain');
        };


        $control->onSaveNoFile[] = function () use ($control)
        {
            $control->redrawControl('modal');
            $control->redrawControl('title');

            $control->redrawControl('formNoFile');
            $control->redrawControl('formFile');
            $control->redrawControl('formMain');

            $this->redrawPrinters();
        };


        $control->onSaveFile[] = function () use ($control)
        {
            $control->redrawControl('modal');
            $control->redrawControl('formFile');

            $this->redrawPrinters();
        };


        $control->onSaveMain[] = function () use ($control)
        {
            $control->redrawControl('modal');
            $control->redrawControl('formMain');

            $this->redrawPrinters();
        };


        return $control;
    }


    /**
     * Překreslí vše obalené snippetem sp-1 až sp-50 (určeno pro komponentu structurePrinter)
     */
    protected function redrawPrinters()
    {
        for ($i = 1; $i <= 50; $i++)
        {
            $this->presenter->redrawControl('sp-' . $i);
        }
    }
}