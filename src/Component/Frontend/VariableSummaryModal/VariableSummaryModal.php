<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableSummaryModal;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Repository\RecipeRepository;
use Wakers\StructureModule\Repository\RecipeVariableRepository;


class VariableSummaryModal extends BaseControl
{
    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * @var RecipeVariableRepository
     */
    protected $variableRepository;


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * Entita
     * @var Recipe
     */
    protected $recipe;


    /**
     * Variables z Entity $recipe
     * @var ObjectCollection|RecipeVariable[]
     */
    protected $variables;


    /**
     * @var int
     * @persistent
     */
    public $recipeId;


    /**
     * VariableSummaryModal constructor.
     * @param RecipeRepository $recipeRepository
     * @param RecipeVariableRepository $recipeVariableRepository
     */
    public function __construct(RecipeRepository $recipeRepository, RecipeVariableRepository $recipeVariableRepository)
    {
        $this->recipeRepository = $recipeRepository;
        $this->variableRepository = $recipeVariableRepository;
    }


    /**
     * Render
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function render() : void
    {
        if ($this->recipeId)
        {
            $this->recipe = $this->recipeRepository->findOneById($this->recipeId);
            $this->variables = $this->variableRepository->findByRecipe($this->recipe);
        }

        $this->template->recipe = $this->recipe;
        $this->template->variables = $this->variables;
        $this->template->setFile(__DIR__.'/templates/variableSummaryModal.latte');
        $this->template->render();
    }


    /**
     * Handle Open
     * @param int $recipeId
     */
    public function handleOpen(int $recipeId) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeId = $recipeId;
            $this->presenter->handleModalToggle('show', '#wakers_structure_variable_summary_modal', FALSE);
            $this->onOpen();
        }
    }
}