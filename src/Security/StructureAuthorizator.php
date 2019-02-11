<?php
/**
 * Copyright (c) 2019 Wakers.cz
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 */


namespace Wakers\StructureModule\Security;


use Wakers\BaseModule\Builder\AclBuilder\AuthorizatorBuilder;
use Wakers\UserModule\Security\UserAuthorizator;


class StructureAuthorizator extends AuthorizatorBuilder
{
    const
        RES_MODULE = 'STRUCTURE_RES_MODULE',

        RES_RECIPE_MODAL = 'STRUCTURE_RES_RECIPE_MODAL',
        RES_RECIPE_REMOVE_MODAL = 'STRUCTURE_RES_RECIPE_REMOVE_MODAL',
        RES_RECIPE_SUMMARY_MODAL = 'STRUCTURE_RES_RECIPE_SUMMARY_MODAL',

        RES_RECIPE_SLUG_MODAL = 'STRUCTURE_RES_RECIPE_SLUG_MODAL',
        RES_RECIPE_SLUG_REMOVE_MODAL = 'STRUCTURE_RES_RECIPE_SLUG_REMOVE_MODAL',

        RES_STRUCTURE_MODAL = 'STRUCTURE_RES_STRUCTURE_MODAL',
        RES_STRUCTURE_REMOVE_MODAL = 'STRUCTURE_RES_STRUCTURE_REMOVE_MODAL',

        RES_VARIABLE_MODAL = 'STRUCTURE_RES_VARIABLE_MODAL',
        RES_VARIABLE_REMOVE_MODAL = 'STRUCTURE_RES_VARIABLE_REMOVE_MODAL',
        RES_VARIABLE_SUMMARY_MODAL = 'STRUCTURE_RES_VARIABLE_SUMMARY_MODAL'
    ;


    public function create() : array
    {
        /*
         * Resources
         */
        $this->addResource(self::RES_MODULE);

        $this->addResource(self::RES_RECIPE_MODAL);
        $this->addResource(self::RES_RECIPE_REMOVE_MODAL);
        $this->addResource(self::RES_RECIPE_SUMMARY_MODAL);

        $this->addResource(self::RES_RECIPE_SLUG_MODAL);
        $this->addResource(self::RES_RECIPE_SLUG_REMOVE_MODAL);

        $this->addResource(self::RES_STRUCTURE_MODAL);
        $this->addResource(self::RES_STRUCTURE_REMOVE_MODAL);

        $this->addResource(self::RES_VARIABLE_MODAL);
        $this->addResource(self::RES_VARIABLE_REMOVE_MODAL);
        $this->addResource(self::RES_VARIABLE_SUMMARY_MODAL);


        /*
         * Privileges
         */
        $this->allow([
            UserAuthorizator::ROLE_EDITOR
        ], [
            self::RES_STRUCTURE_MODAL,
            self::RES_STRUCTURE_REMOVE_MODAL,
        ]);

        $this->allow([
            UserAuthorizator::ROLE_ADMIN
        ], [
            self::RES_MODULE,

            self::RES_RECIPE_MODAL,
            self::RES_RECIPE_REMOVE_MODAL,
            self::RES_RECIPE_SUMMARY_MODAL,

            self::RES_RECIPE_SLUG_MODAL,
            self::RES_RECIPE_SLUG_REMOVE_MODAL,

            self::RES_VARIABLE_MODAL,
            self::RES_VARIABLE_REMOVE_MODAL,
            self::RES_VARIABLE_SUMMARY_MODAL,
        ]);


        return parent::create();
    }
}