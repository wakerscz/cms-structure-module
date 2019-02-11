<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\StructureRemoveModal;


use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Manager\StructureManager;
use Wakers\StructureModule\Repository\StructureRepository;
use Wakers\StructureModule\Security\StructureAuthorizator;


class StructureRemoveModal extends BaseControl
{
    /**
     * @var StructureRepository
     */
    protected $structureRepository;


    /**
     * @var StructureManager
     */
    protected $structureManager;


    /**
     * Entita načtena při otevření modálního okna
     * @var Structure
     */
    protected $structureEntity;


    /**
     * @var callable
     */
    public $onRemove = [];


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * StructureRemoveModal constructor.
     * @param StructureRepository $structureRepository
     * @param StructureManager $structureManager
     */
    public function __construct(
        StructureRepository $structureRepository,
        StructureManager $structureManager
    ) {
        $this->structureRepository = $structureRepository;
        $this->structureManager = $structureManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->structure = $this->structureEntity;
        $this->template->setFile(__DIR__.'/templates/structureRemoveModal.latte');
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
            $this->structureEntity = $this->structureRepository->findOneByIdWithRecipe($id);

            if ($this->structureEntity)
            {
                $this->presenter->handleModalToggle('show', '#wakers_structure_structure_remove_modal', FALSE);
                $this->onOpen();
            }
            else
            {
                $this->presenter->notificationAjax(
                    'Položka odstraněna',
                    "Položka s id '{$id}' již byla odstraněna.",
                    'error',
                    TRUE
                );
            }
        }
    }


    /**
     * Handler pro odstranění
     * @param int $id
     * @throws \Exception
     */
    public function handleRemove(int $id) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_REMOVE_MODAL))
        {
            $this->structureEntity = $this->structureRepository->findOneByIdWithRecipe($id);

            $this->structureManager->delete($this->structureEntity);

            $this->presenter->notificationAjax(
                "{$this->structureEntity->getRecipeSlug()->getRecipe()->getName()} odstraněn/a.",
                "Položka '{$this->structureEntity->getRecipeSlug()->getRecipe()->getName()}' s id '{$this->structureEntity->getId()}' byla úspěšně odstraněna.",
                'success',
                FALSE
            );

            $this->presenter->handleModalToggle('hide', '#wakers_structure_structure_remove_modal', FALSE);

            $this->onRemove();
        }
    }
}