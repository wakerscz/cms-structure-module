<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSummaryModal;


trait Create
{
    /**
     * @var IRecipeSummaryModal
     * @inject
     */
    public $IStructure_RecipeSummaryModal;


    /**
     * Přehled struktur
     * @return RecipeSummaryModal
     */
    protected function createComponentStructureRecipeSummaryModal() : object
    {
        return $this->IStructure_RecipeSummaryModal->create();
    }
}