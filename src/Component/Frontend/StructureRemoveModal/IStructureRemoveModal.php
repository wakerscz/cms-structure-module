<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\StructureRemoveModal;


interface IStructureRemoveModal
{
    /**
     * @return StructureRemoveModal
     */
    public function create() : StructureRemoveModal;
}