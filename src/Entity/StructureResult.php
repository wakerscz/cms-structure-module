<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Entity;


use Nette\Utils\DateTime;
use Wakers\PageModule\Database\Page;
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
     * @var Page|NULL
     */
    protected $page;


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

        if ($structure->getRecipeSlug()->getRecipe()->isDynamic())
        {
            $pages = $structure->getStructureInPages();
            $this->page = $pages[0]->getPage();
        }
    }


    /**
     * @param string $slug
     * @return string
     */
    public function getValContent(string $slug) : ?string
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
    public function getValUrl(string $slug) : ?PageUrl
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
    public function getValFiles(string $slug) : array
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
     * @return Page|NULL
     */
    public function getPage() : ?Page
    {
        return $this->page;
    }


    /**
     * @return bool
     */
    public function isPagePublished() : bool
    {
        if ($this->page)
        {
            return $this->page->isPublished();
        }

        return TRUE;
    }


    /**
     * @return string|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getPageUrl() : ?string
    {
        if ($this->page)
        {
            return $this->page->getPageUrl()->getUrl();
        }

        return NULL;
    }


    /**
     * Shortcut
     * @return int
     */
    public function getStructureId() : int
    {
        return $this->structure->getId();
    }


    /**
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getRecipeSlug() : string
    {
        return $this->structure->getRecipeSlug()->getSlug();
    }


    /**
     * Metoda se musí jmenovat přesně takto (kvůli NestedSetu)
     * @return DateTime
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getCreatedAt() : DateTime
    {
        return $this->structure->getCreatedAt();
    }


    /**
     * Metoda existuje pouze kvůli řazení NestedSetu
     * @return int
     */
    public function getLeftValue() : int
    {
        return $this->structure->getLeftValue();
    }


    /**
     * Metoda existuje pouze kvůli řazení NestedSetu
     * @return int
     */
    public function getRightValue() : int
    {
        return $this->structure->getRightValue();
    }
}