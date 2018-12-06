<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSlugRemoveModal;


interface IRecipeSlugRemoveModal
{
    /**
     * @return RecipeSlugRemoveModal
     */
    public function create() : RecipeSlugRemoveModal;
}