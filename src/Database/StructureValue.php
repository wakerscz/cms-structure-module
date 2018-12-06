<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Database;


use Wakers\BaseModule\Util\Validator;
use Wakers\PageModule\Database\PageUrl;
use Wakers\StructureModule\Database\Base\StructureValue as BaseStructureValue;


class StructureValue extends BaseStructureValue
{
    /**
     * @param string $v
     * @return BaseStructureValue|StructureValue
     */
    public function setContent($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setContent($v);
    }


    /**
     * @param int $v
     * @return BaseStructureValue|StructureValue
     */
    public function setLinkToUrlId($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        $v = $v <= 0 ? NULL : $v;

        return parent::setLinkToUrlId($v);
    }
}