<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Database;


use Wakers\BaseModule\Util\ProtectedFile;
use Wakers\BaseModule\Util\Validator;
use Wakers\StructureModule\Database\Base\StructureValueFile as BaseStructureValueFile;
use Wakers\StructureModule\Repository\StructureValueFileRepository;


class StructureValueFile extends BaseStructureValueFile
{
    /**
     * @return ProtectedFile
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getProtectedFile() : ProtectedFile
    {
        $structure = $this->getStructureValue()->getStructure();
        $structureValue = $this->getStructureValue();

        $path = StructureValueFileRepository::FILE_PATH . "/structure-{$structure->getId()}/value-{$structureValue->getId()}/";

        $pf = new ProtectedFile($path, $this->getName());
        
        return $pf;
    }


    /**
     * @param string $v
     * @return BaseStructureValueFile|StructureValueFile
     */
    public function setTitle($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setTitle($v);
    }
}