<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeRemoveModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\LangModule\Translator\Translate;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Manager\RecipeManager;
use Wakers\StructureModule\Repository\RecipeRepository;


class RecipeRemoveModal extends BaseControl
{
    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * @var RecipeManager
     */
    protected $recipeManager;


    /**
     * Entita načtena při otevření modálního okna
     * @var Recipe
     */
    protected $recipeEntity;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * @var callable
     */
    public $onRemove = [];


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * RecipeRemoveModal constructor.
     * @param RecipeRepository $recipeRepository
     * @param RecipeManager $recipeManager
     * @param Translate $translate
     */
    public function __construct(RecipeRepository $recipeRepository, RecipeManager $recipeManager, Translate $translate)
    {
        $this->recipeRepository = $recipeRepository;
        $this->recipeManager = $recipeManager;
        $this->translate = $translate;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->recipe = $this->recipeEntity;
        $this->template->setFile(__DIR__.'/templates/recipeRemoveModal.latte');
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
            $this->recipeEntity = $this->recipeRepository->findOneById($id);

            $this->presenter->handleModalToggle('show', '#wakers_structure_recipe_remove_modal', FALSE);
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
            $this->recipeEntity = $this->recipeRepository->findOneById($id);

            $this->recipeManager->delete($this->recipeEntity);

            $this->presenter->notificationAjax(
                $this->translate->translate('Recipe removed'),
                $this->translate->translate("Recipe '%recipe%' successfully removed.", ['recipe' => $this->recipeEntity->getName()]),
                'success',
                FALSE
            );

            $this->presenter->handleModalToggle('hide', '#wakers_structure_recipe_remove_modal', FALSE);

            $this->onRemove();
        }
    }
}