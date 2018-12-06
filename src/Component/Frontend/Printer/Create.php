<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\StructureModule\Component\Frontend\Printer;


trait Create
{
    /**
     * @var IPrinter
     * @inject
     */
    public $IStructure_Printer;


    /**
     * Komponenta pro výpis struktur
     * @return Printer
     */
    protected function createComponentStructurePrinter() : object
    {
        return $this->IStructure_Printer->create($this->pagination);
    }
}