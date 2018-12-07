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
     * @param bool $filterByPagePublished
     * @return int
     */
    public function countByCategorySlugs(Lang $lang, array $categorySlugs, bool $filterByPagePublished) : int;


    /**
     * @param Lang $lang
     * @param array $categorySlugs
     * @param int|NULL $paginationOffset
     * @param int|NULL $paginationLimit
     * @param string $sort
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     */
    public function findByCategorySlugs(Lang $lang, array $categorySlugs, ?int $paginationOffset, ?int $paginationLimit, string $sort, bool $filterByPagePublished) : array;


    /**
     * @param Lang $lang
     * @param array $categorySlugs
     * @param string $sort
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     */
    public function findRecursiveByCategorySlugs(Lang $lang, array $categorySlugs, string $sort, bool $filterByPagePublished) : array;


    /**
     * @param array $recipeSlugs
     * @param string $sort
     * @param Page|NULL $page
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     */
    public function findByRecipeSlugsAndPage(array $recipeSlugs, string $sort, ?Page $page, bool $filterByPagePublished) : array;


    /**
     * @param array $recipeSlugs
     * @param string $sort
     * @param Page|NULL $page
     * @param bool $filterByPagePublished
     * @return StructureResult[]
     */
    public function findRecursiveByRecipeSlugsAndPage(array $recipeSlugs, string $sort, ?Page $page, bool $filterByPagePublished) : array;
}