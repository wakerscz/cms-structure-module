<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Database;


use Wakers\BaseModule\Util\Validator;
use Wakers\StructureModule\Database\Base\RecipeVariable as BaseRecipeVariable;


class RecipeVariable extends BaseRecipeVariable
{
    /**
     * Required
     */
    const
        REQUIRED_YES = 1,
        REQUIRED_NO = 0;

    /**
     * Type
     */
    const
        TYPE_TEXT_PLAIN = 'TEXT_PLAIN',
        TYPE_TEXT_FORMATTED = 'TEXT_FORMATTED',
        TYPE_PHONE = 'PHONE',
        TYPE_EMAIL = 'EMAIL',
        TYPE_DATE = 'DATE',
        TYPE_DATETIME = 'DATETIME',
        TYPE_LINK_INTERNAL = 'LINK_INTERNAL',
        TYPE_LINK_EXTERNAL = 'LINK_EXTERNAL',
        TYPE_FILES = 'FILES',
        TYPE_IMAGES = 'IMAGES',
        TYPE_SELECT_BOX = 'SELECT_BOX';


    /**
     * @param string $v
     * @return BaseRecipeVariable|RecipeVariable
     * @throws \Exception
     */
    public function setType($v)
    {
        $constant = self::class . '::TYPE_' . $v;

        if (!defined($constant))
        {
            throw new \Exception("Constant: {$constant} does not exists.");
        }

        return parent::setType($v);
    }


    /**
     * @param string $v
     * @return BaseRecipeVariable|RecipeVariable
     */
    public function setTooltip($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setTooltip($v);
    }

    /**
     * @param string $v
     * @return BaseRecipeVariable|RecipeVariable
     */
    public function setRegexPattern($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setRegexPattern($v);
    }


    /**
     * @param string $v
     * @return BaseRecipeVariable|RecipeVariable
     */
    public function setRegexMessage($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setRegexMessage($v);
    }


    /**
     * @param string $v
     * @return BaseRecipeVariable|RecipeVariable
     */
    public function setAllowedTypes($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setAllowedTypes($v);
    }


    /**
     * @param string $v
     * @return BaseRecipeVariable|RecipeVariable
     */
    public function setItems($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setItems($v);
    }
}