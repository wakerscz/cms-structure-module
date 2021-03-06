<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableRemoveModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Manager\RecipeVariableManager;
use Wakers\StructureModule\Repository\RecipeVariableRepository;
use Wakers\StructureModule\Security\StructureAuthorizator;


class VariableRemoveModal extends BaseControl
{
    /**
     * @var RecipeVariableRepository
     */
    protected $variableRepository;


    /**
     * @var RecipeVariableManager
     */
    protected $variableManager;


    /**
     * Entita načtena při otevření modálního okna
     * @var RecipeVariable
     */
    protected $variableEntity;


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
     * @param RecipeVariableRepository $variableRepository
     * @param RecipeVariableManager $recipeVariableManager
     */
    public function __construct(RecipeVariableRepository $variableRepository, RecipeVariableManager $recipeVariableManager)
    {
        $this->variableRepository = $variableRepository;
        $this->variableManager = $recipeVariableManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->variable = $this->variableEntity;
        $this->template->setFile(__DIR__.'/templates/variableRemoveModal.latte');
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
            $this->variableEntity = $this->variableRepository->findOneById($id);

            $this->presenter->handleModalToggle('show', '#wakers_structure_variable_remove_modal', FALSE);
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
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_VARIABLE_REMOVE_MODAL))
        {
            $this->variableEntity = $this->variableRepository->findOneById($id);

            $this->variableManager->delete($this->variableEntity);

            $this->presenter->notificationAjax(
                'Proměnná odstraněna.',
                "Proměnná '{$this->variableEntity->getLabel()} byla úspěšně odstraněna.'",
                'success',
                FALSE
            );

            $this->presenter->handleModalToggle('hide', '#wakers_structure_variable_remove_modal', FALSE);

            $this->onRemove();
        }
    }
}