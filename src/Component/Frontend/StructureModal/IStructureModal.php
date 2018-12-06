<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\StructureModal;


interface IStructureModal
{
    /**
     * @return StructureModal
     */
    public function create() : StructureModal;
}