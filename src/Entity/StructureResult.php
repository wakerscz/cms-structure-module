<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Entity;


use Nette\Utils\DateTime;
use Wakers\PageModule\Database\PageUrl;
use Wakers\StructureModule\Database\Base\StructureValue;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValueFile;


class StructureResult
{
    /**
     * @var Structure
     */
    protected $structure;


    /**
     * @var StructureValue[]
     */
    protected $structureValues = [];


    /**
     * StructureResult constructor.
     * @param Structure $structure
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function __construct(Structure $structure)
    {
        $this->structure = $structure;

        foreach ($structure->getStructureValues() as $structureValue)
        {
            $slug = $structureValue->getRecipeVariable()->getSlug();
            $this->structureValues[$slug] = $structureValue;
        }
    }


    /**
     * @param string $slug
     * @return string
     */
    public function getContent(string $slug) : ?string
    {
        if (key_exists($slug, $this->structureValues))
        {
            return $this->structureValues[$slug]->getContent();
        }

        return NULL;
    }


    /**
     * @param string $slug
     * @return PageUrl
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getPageUrl(string $slug) : ?PageUrl
    {
        if (key_exists($slug, $this->structureValues))
        {
            return $this->structureValues[$slug]->getLinkToUrl();
        }

        return NULL;
    }


    /**
     * @param string $slug
     * @return StructureValueFile[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getFiles(string $slug) : array
    {
        if (key_exists($slug, $this->structureValues))
        {
            $files = $this->structureValues[$slug]->getStructureValueFiles();

            foreach ($files as  $file)
            {
                if ($file->isDeleted())
                {
                    $files->removeObject($file);
                }
            }

            $result = $files->toKeyIndex();

            usort($result, function (StructureValueFile $a, StructureValueFile $b)
            {
                return $a->getUploadedAt() > $b->getUploadedAt();
            });

            return $result;
        }

        return [];
    }


    /**
     * Shortcut
     * @return int
     */
    public function getId() : int
    {
        return $this->structure->getId();
    }


    /**
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getSlug() : string
    {
        return $this->structure->getRecipeSlug()->getSlug();
    }


    /**
     * @return Structure
     */
    public function getStructure() : Structure
    {
        return $this->structure;
    }


    /**
     * @return StructureValue[]
     */
    public function getStructureValues() : array
    {
        return $this->structureValues;
    }


    /**
     * @return int
     */
    public function getLeftValue() : int
    {
        return $this->structure->getLeftValue();
    }


    /**
     * @return DateTime
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getCreatedAt() : DateTime
    {
        return $this->structure->getCreatedAt();
    }


    /**
     * @return int
     */
    public function getRightValue() : int
    {
        return $this->structure->getRightValue();
    }
}