<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Database;


use Wakers\StructureModule\Database\Base\Recipe as BaseRecipe;


class Recipe extends BaseRecipe
{
    /**
     * Statický
     */
    const TYPE_STATIC = 0;


    /**
     * Dynamický
     */
    const TYPE_DYNAMIC = 1;
}