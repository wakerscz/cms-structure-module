<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeSlugModal;


use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\StructureModule\Manager\RecipeSlugManager;
use Wakers\StructureModule\Repository\RecipeRepository;
use Wakers\StructureModule\Repository\RecipeSlugRepository;


class RecipeSlugModal extends BaseControl
{
    use AjaxValidate;


    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * @var RecipeSlugRepository
     */
    protected $recipeSlugRepository;


    /**
     * @var RecipeSlugManager
     */
    protected $recipeSlugManager;


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * @var callable
     */
    public $onSave = [];


    /**
     * @var int
     * @persistent
     */
    public $recipeId;


    /**
     * @var int
     */
    public $recipeSlugId;


    /**
     * RecipeSlugModal constructor.
     * @param RecipeRepository $recipeRepository
     * @param RecipeSlugRepository $recipeSlugRepository
     * @param RecipeSlugManager $recipeSlugManager
     */
    public function __construct(
        RecipeRepository $recipeRepository,
        RecipeSlugRepository $recipeSlugRepository,
        RecipeSlugManager $recipeSlugManager
    ) {
        $this->recipeRepository = $recipeRepository;
        $this->recipeSlugRepository = $recipeSlugRepository;
        $this->recipeSlugManager = $recipeSlugManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $slugs = [];

        if ($this->recipeId)
        {
            $slugs = $this->recipeSlugRepository->findByRecipeId($this->recipeId);
        }

        $this->template->slugs = $slugs;
        $this->template->setFile(__DIR__ . '/templates/recipeSlugModal.latte');
        $this->template->render();
    }


    protected function createComponentSlugForm() : Form
    {
        $form = new Form;

        $form->addText('slug')
            ->setRequired('Slug je povinný.')
            ->addRule(Form::PATTERN, 'Slug může obsahovat pouze znaky a-z 0-9 a pomlčku.', '[a-zA-Z0-9\-]*')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka slugu je %d znaků.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka slugu je %d znaků', 32);

        $form->addHidden('id');

        $form->addSubmit('save');


        if ($this->recipeSlugId)
        {
            $recipeSlug = $this->recipeSlugRepository->findOneById($this->recipeSlugId);

            $form->setDefaults([
                'recipeId' => $recipeSlug->getRecipeId(),
                'id' => $recipeSlug->getId(),
                'slug' => $recipeSlug->getSlug()
            ]);
        }

        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->successSlug($form); };

        return $form;
    }


    /**
     * @param Form $form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function successSlug(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            $recipe = $this->recipeRepository->findOneById($this->recipeId);

            $recipeSlug = $values->id > 0 ?  $this->recipeSlugRepository->findOneById($values->id) : NULL;

            try
            {
                $result = $this->recipeSlugManager->save($recipe, $recipeSlug, $values->slug);

                $form->reset();

                $this->presenter->notificationAjax('Slug uložen', "Slug '{$result->getSlug()}' byl úspěšně uložen.", 'success', FALSE);
                $this->onSave();
            }
            catch (DatabaseException $exception)
            {
                $this->presenter->notificationAjax('Chyba', $exception->getMessage(), 'error');
            }
        }
    }


    /**
     * Handle edit
     * @param int|NULL $recipeId
     */
    public function handleOpen(int $recipeId = NULL) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeId = $recipeId;

            $this->presenter->handleModalToggle('show', '#wakers_structure_recipe_slug_modal', FALSE);

            $this->onOpen();
        }
    }


    /**
     * @param int $id
     */
    public function handleEdit(int $id) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeSlugId = $id;

            $this->onOpen();
        }
    }
}