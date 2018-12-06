<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSummaryModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\StructureModule\Repository\RecipeRepository;


class RecipeSummaryModal extends BaseControl
{
    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * RecipeSummaryModal constructor.
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->recipes = $this->recipeRepository->findAllJoinVariables();
        $this->template->setFile(__DIR__ . '/templates/recipeSummaryModal.latte');
        $this->template->render();
    }
}