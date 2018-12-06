<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSummaryModal;


interface IRecipeSummaryModal
{
    /**
     * @return RecipeSummaryModal
     */
    public function create() : RecipeSummaryModal;
}