<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

namespace Wakers\StructureModule\Manager;


use Wakers\PageModule\Database\Page;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureInPage;


class StructureInPageManager
{
    /**
     * @param Page $page
     * @param Structure $structure
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function add(Page $page, Structure $structure)
    {
        $structureInPage = new StructureInPage;
        $structureInPage->setPage($page);
        $structureInPage->setStructure($structure);
        $structureInPage->save();
    }
}