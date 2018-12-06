<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\StructureModule\Component\Frontend\Printer;


interface IPrinter
{
    /**
     * @var int $pagination
     * @return Printer
     */
    public function create(int $pagination) : Printer;
}