<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Manager;


use Nette\Utils\DateTime;
use Propel\Runtime\Collection\ObjectCollection;
use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\StructureModule\Database\Base\StructureInCategory;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeSlug;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Repository\StructureRepository;
use Wakers\StructureModule\Repository\StructureValueFileRepository;
use Wakers\UserModule\Database\User;


class StructureManager extends AbstractDatabase
{
    /**
     * @var StructureRepository
     */
    protected $structureRepository;


    /**
     * @var StructureValueFileRepository
     */
    protected $structureValueFileRepository;


    /**
     * StructureManager constructor.
     * @param StructureRepository $structureRepository
     * @param StructureValueFileRepository $structureValueFileRepository
     */
    public function __construct(
        StructureRepository $structureRepository,
        StructureValueFileRepository $structureValueFileRepository
    ) {
        $this->structureRepository = $structureRepository;
        $this->structureValueFileRepository = $structureValueFileRepository;
    }


    /**
     * @param Structure|NULL $structure
     * @param RecipeSlug $recipeSlug
     * @param User $user
     * @param Structure|null $parent
     * @param DateTime|null $createdAt
     * @return Structure
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Exception
     */
    public function save(
        ?Structure $structure,
        RecipeSlug $recipeSlug,
        User $user,
        Structure $parent = NULL,
        DateTime $createdAt = NULL
    ) : Structure
    {
        $root = $this->structureRepository->findRoot();

        if (!$structure)
        {
            if (!$root)
            {
                $root = new Structure;
                $root->makeRoot();
                $root->save();
            }

            $structure = new Structure;
            $structure->setRecipeSlug($recipeSlug);
            $structure->setParent($root);
            $structure->insertAsLastChildOf($root);
        }

        if ($createdAt)
        {
            $structure->setCreatedAt($createdAt);
        }

        $parent = $parent ? $parent : $root;

        $structure->setParent($parent);
        $structure->moveToLastChildOf($parent);

        $structure->setUpdatedBy($user);
        $structure->setUpdatedAt(new DateTime);
        $structure->save();

        return $structure;
    }


    /**
     * @param Structure $structure
     * @param ObjectCollection|StructureInCategory[] $structureInCategories
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveCategories(Structure $structure, ObjectCollection $structureInCategories) : void
    {
        $structure->setStructureInCategories($structureInCategories);
        $structure->save();
    }


    /**
     * @param Recipe $recipe
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Exception
     */
    public function resetParents(Recipe $recipe) : void
    {
        $structures = $this->structureRepository->findByRecipe($recipe);
        $root = $this->structureRepository->findRoot();

        $this->getConnection()->beginTransaction();

        try
        {
            foreach ($structures as $structure)
            {
                $structure->setParent($root);
                $structure->moveToLastChildOf($root);
                $structure->save();
            }

            $this->getConnection()->commit();
        }
        catch (\Exception $exception)
        {
            $this->getConnection()->rollBack();
            throw $exception;
        }
    }


    /**
     * @param Recipe $recipe
     * @param int $fromLevel
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Exception
     */
    public function resetParentLevels(Recipe $recipe, int $fromLevel) : void
    {
        $structures = $this->structureRepository->findByRecipeAndLevel($recipe, $fromLevel);
        $root = $this->structureRepository->findRoot();

        $this->getConnection()->beginTransaction();

        try
        {
            foreach ($structures as $structure)
            {
                $structure->setParent($root);
                $structure->moveToLastChildOf($root);
                $structure->save();
            }

            $this->getConnection()->commit();
        }
        catch (\Exception $exception)
        {
            $this->getConnection()->rollBack();
            throw $exception;
        }
    }


    /**
     * @param Structure $structure
     * @throws \Exception
     */
    public function delete(Structure $structure) : void
    {
        $this->getConnection()->beginTransaction();

        try
        {
            foreach ($structure->getChildren() as $child)
            {
                $child->setParent($structure->getParent());
                $child->moveToLastChildOf($structure->getParent());
                $child->save();
            }

            foreach ($this->structureValueFileRepository->findByStructure($structure) as $structureValueFile)
            {
                $structureValueFile->getProtectedFile()->remove();
            }

            $structure->delete();

            $this->getConnection()->commit();
        }
        catch (\Exception $exception)
        {
            $this->getConnection()->rollBack();
            throw $exception;
        }
    }

}