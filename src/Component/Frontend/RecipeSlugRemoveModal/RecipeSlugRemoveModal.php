<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSlugRemoveModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\StructureModule\Database\RecipeSlug;
use Wakers\StructureModule\Manager\RecipeSlugManager;
use Wakers\StructureModule\Repository\RecipeSlugRepository;


class RecipeSlugRemoveModal extends BaseControl
{
    /**
     * @var RecipeSlugRepository
     */
    protected $recipeSlugRepository;


    /**
     * @var RecipeSlugManager
     */
    protected $recipeSlugManager;


    /**
     * Entita načtena při otevření modálního okna
     * @var RecipeSlug
     */
    protected $recipeSlugEntity;


    /**
     * @var callable
     */
    public $onRemove = [];


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * RecipeSlugRemoveModal constructor.
     * @param RecipeSlugRepository $recipeSlugRepository
     * @param RecipeSlugManager $recipeSlugManager
     */
    public function __construct(RecipeSlugRepository $recipeSlugRepository, RecipeSlugManager $recipeSlugManager)
    {
        $this->recipeSlugRepository = $recipeSlugRepository;
        $this->recipeSlugManager = $recipeSlugManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->recipeSlug = $this->recipeSlugEntity;
        $this->template->setFile(__DIR__.'/templates/recipeSlugRemoveModal.latte');
        $this->template->render();
    }


    /**
     * Handler pro otevření modálního okna
     * @param int $id
     */
    public function handleOpen(int $id) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeSlugEntity = $this->recipeSlugRepository->findOneById($id);

            $this->presenter->handleModalToggle('show', '#wakers_structure_recipe_slug_remove_modal', FALSE);
            $this->onOpen();
        }
    }


    /**
     * Handler pro odstranění
     * @param int $id
     * @throws \Exception
     */
    public function handleRemove(int $id) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeSlugEntity = $this->recipeSlugRepository->findOneById($id);

            $this->recipeSlugManager->delete($this->recipeSlugEntity);

            $this->presenter->notificationAjax(
                'Slug odstraněn',
                "Slug '{$this->recipeSlugEntity->getSlug()}' byl úspěšně odstraněn.",
                'success',
                FALSE
            );

            $this->presenter->handleModalToggle('hide', '#wakers_structure_recipe_slug_remove_modal', FALSE);

            $this->onRemove();
        }
    }
}