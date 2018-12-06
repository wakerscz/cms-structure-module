<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableModal;


interface IVariableModal
{
    /**
     * @return VariableModal
     */
    public function create() : VariableModal;
}