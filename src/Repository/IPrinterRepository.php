<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Repository;


use Wakers\LangModule\Database\Lang;
use Wakers\PageModule\Database\Page;
use Wakers\StructureModule\Entity\StructureResult;


interface IPrinterRepository
{
    /**
     * @param Lang $lang
     * @param array $categorySlugs
     * @return int
     */
    public function countByCategorySlugs(Lang $lang, array $categorySlugs) : int;


    /**
     * @param Lang $lang
     * @param array|string[] $categorySlugs
     * @param int|NULL $paginationOffset
     * @param int|NULL $paginationLimit
     * @param string $sort
     * @return StructureResult[]
     */
    public function findByCategorySlugs(Lang $lang, array $categorySlugs, ?int $paginationOffset, ?int $paginationLimit, string $sort) : array;


    /**
     * @param Lang $lang
     * @param array|string[] $categorySlugs
     * @param string $sort
     * @return StructureResult[]
     */
    public function findRecursiveByCategorySlugs(Lang $lang, array $categorySlugs, string $sort) : array;


    /**
     * @param array $recipeSlugs
     * @param string $sort
     * @param Page|NULL $page
     * @return StructureResult[]
     */
    public function findByRecipeSlugsAndPage(array $recipeSlugs, string $sort, Page $page = NULL) : array;


    /**
     * @param array $recipeSlugs
     * @param string $sort
     * @param Page|NULL $page
     * @return StructureResult[]
     */
    public function findRecursiveByRecipeSlugsAndPage(array $recipeSlugs, string $sort, Page $page = NULL) : array;
}