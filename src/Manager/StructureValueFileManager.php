<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\BaseModule\Util\ProtectedFile;
use Wakers\StructureModule\Database\StructureValue;
use Wakers\StructureModule\Database\StructureValueFile;
use Wakers\StructureModule\Repository\StructureValueFileRepository;


class StructureValueFileManager extends AbstractDatabase
{
    /**
     * @var StructureValueFileRepository
     */
    protected $structureValueFileRepository;


    /**
     * StructureValueFileManager constructor.
     * @param StructureValueFileRepository $structureValueFileRepository
     */
    public function __construct(StructureValueFileRepository $structureValueFileRepository)
    {
        $this->structureValueFileRepository = $structureValueFileRepository;
    }


    /**
     * @param StructureValue|null $structureValue
     * @param FileUpload $fileUpload
     * @throws \Exception
     */
    public function add(StructureValue $structureValue, FileUpload $fileUpload) : void
    {
        $path = StructureValueFileRepository::FILE_PATH . "/structure-{$structureValue->getStructure()->getId()}/value-{$structureValue->getId()}/";

        $this->getConnection()->beginTransaction();

        try
        {
            $protectedFile = new ProtectedFile($path, NULL);
            $name = $protectedFile->move($fileUpload);

            $structureValueFile = new StructureValueFile;
            $structureValueFile->setStructureValue($structureValue);
            $structureValueFile->setName($name);
            $structureValueFile->setSizeMb($fileUpload->getSize() / (1000 * 1000));
            $structureValue->save();

            $this->getConnection()->commit();
        }
        catch (\Exception $exception)
        {
            $this->getConnection()->rollBack();
            throw  $exception;
        }
    }


    /**
     * @param int $id
     * @param DateTime $uploadedAt
     * @param string $title
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function update(int $id, DateTime $uploadedAt, string $title) : void
    {
        $structureValueFile = $this->structureValueFileRepository->findOneById($id);

        if ($structureValueFile)
        {
            $structureValueFile->setUploadedAt($uploadedAt);
            $structureValueFile->setTitle($title);
            $structureValueFile->save();
        }
    }


    /**
     * @param int $id
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Exception
     */
    public function remove(int $id) : void
    {
        $structureValueFile = $this->structureValueFileRepository->findOneById($id);

        if ($structureValueFile)
        {
            $structureValue = $structureValueFile->getStructureValue();
            $structure = $structureValue->getStructure();
            $path = StructureValueFileRepository::FILE_PATH . "/structure-{$structure->getId()}/value-{$structureValue->getId()}/";

            $this->getConnection()->beginTransaction();

            try
            {
                $pf = new ProtectedFile($path, $structureValueFile->getName());
                $pf->remove();

                $structureValueFile->delete();

                $this->getConnection()->commit();
            }
            catch (\Exception $exception)
            {
                $this->getConnection()->rollBack();
                throw $exception;
            }
        }
    }
}