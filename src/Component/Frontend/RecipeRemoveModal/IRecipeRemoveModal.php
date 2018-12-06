<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeRemoveModal;


interface IRecipeRemoveModal
{
    /**
     * @return RecipeRemoveModal
     */
    public function create() : RecipeRemoveModal;
}