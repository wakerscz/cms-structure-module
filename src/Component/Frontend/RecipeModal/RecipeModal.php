<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\RecipeModal;


use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\CategoryModule\Repository\CategoryRepository;
use Wakers\LangModule\Database\Lang;
use Wakers\LangModule\Repository\LangRepository;
use Wakers\StructureModule\Database\Recipe;
use Wakers\LangModule\Translator\Translate;
use Wakers\StructureModule\Manager\RecipeCategoryAllowedManager;
use Wakers\StructureModule\Manager\RecipeManager;
use Wakers\StructureModule\Manager\StructureInCategoryManager;
use Wakers\StructureModule\Manager\StructureManager;
use Wakers\StructureModule\Repository\RecipeCategoryAllowedRepository;
use Wakers\StructureModule\Repository\RecipeRepository;


class RecipeModal extends BaseControl
{
    use AjaxValidate;


    /**
     * Defaultní hodnoty formuláře
     */
    const FORM_DEFAULTS = [
        'id' => 0,
        'maxInstances' => 0,
        'maxCategories' => 0,
        'maxDepth' => 0,
        'paginationLimit' => 0
    ];


    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;


    /**
     * @var RecipeCategoryAllowedRepository
     */
    protected $recipeCategoryAllowedRepository;


    /**
     * @var RecipeManager
     */
    protected $recipeManager;


    /**
     * @var RecipeCategoryAllowedManager
     */
    protected $recipeCategoryAllowedManager;


    /**
     * @var StructureInCategoryManager
     */
    protected $structureInCategoryManager;


    /**
     * @var StructureManager
     */
    protected $structureManager;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * @var Lang
     */
    protected $activeLang;


    /**
     * @var callable
     */
    public $onSave = [];


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * @var int
     * @persistent
     */
    public $recipeId;


    /**
     * RecipeModal constructor.
     * @param RecipeRepository $recipeRepository
     * @param CategoryRepository $categoryRepository
     * @param RecipeCategoryAllowedRepository $recipeCategoryAllowedRepository
     * @param LangRepository $langRepository
     * @param RecipeManager $recipeManager
     * @param RecipeCategoryAllowedManager $recipeCategoryAllowedManager
     * @param StructureInCategoryManager $structureInCategoryManager
     * @param StructureManager $structureManager
     * @param Translate $translate
     */
    public function __construct(
        RecipeRepository $recipeRepository,
        CategoryRepository $categoryRepository,
        RecipeCategoryAllowedRepository $recipeCategoryAllowedRepository,
        LangRepository $langRepository,

        RecipeManager $recipeManager,
        RecipeCategoryAllowedManager $recipeCategoryAllowedManager,
        StructureInCategoryManager $structureInCategoryManager,
        StructureManager $structureManager,

        Translate $translate
    ) {
        $this->recipeRepository = $recipeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->recipeCategoryAllowedRepository = $recipeCategoryAllowedRepository;

        $this->recipeManager = $recipeManager;
        $this->recipeCategoryAllowedManager = $recipeCategoryAllowedManager;
        $this->structureInCategoryManager = $structureInCategoryManager;
        $this->structureManager = $structureManager;
        $this->translate = $translate;

        $this->activeLang = $langRepository->getActiveLang();
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/recipeModal.latte');
        $this->template->render();
    }


    /**
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentRecipeForm() : Form
    {
        $types = [
            Recipe::TYPE_DYNAMIC => 'Dynamická',
            Recipe::TYPE_STATIC => 'Statická'
        ];


        $allowedCategories = [];
        $root = $this->categoryRepository->findLangRoot($this->activeLang);

        if ($root)
        {
            foreach ($this->categoryRepository->findChildren($root) as $category)
            {
                $allowedCategories[$category->getId()] = $category->getName();
            }
        }

        $form = new Form;

        $form->addText('name')
            ->setRequired($this->translate->translate('Name is required.'))
            ->addRule(Form::MIN_LENGTH, $this->translate->translate('Minimal length of name is %count% characters.', ['count' => 3]), 3)
            ->addRule(Form::MAX_LENGTH, $this->translate->translate('Maximal length of name is %count% characters.', ['count' => 32]), 32);

        $form->addSelect('isDynamic', NULL, $types)
            ->setRequired($this->translate->translate('Type is required.'));

        $form->addText('maxInstances')
            ->setRequired($this->translate->translate('Max instances is required.'))
            ->addRule(Form::INTEGER, $this->translate->translate('Max instances must be integer.'))
            ->addRule(Form::MIN, $this->translate->translate('Minimum of Max instances must be %min%.', ['min' => 0]), 0);

        $form->addCheckboxList('allowedCategories', NULL, $allowedCategories)
            ->setRequired($this->translate->translate('Category is required.'));

        $form->addText('maxCategories')
            ->setRequired($this->translate->translate('Max categories is required.'))
            ->addRule(Form::INTEGER, $this->translate->translate('Max categories must be integer.'))
            ->addRule(Form::MIN, $this->translate->translate('Minimum of Max categories must be %min%.', ['min' => 0]), 0);

        $form->addSelect('allowedParent', NULL, $this->getAllowedParents())
            ->setRequired(FALSE);

        $form->addText('maxDepth')
            ->setRequired($this->translate->translate('Max depth is required.'))
            ->addRule(Form::INTEGER, $this->translate->translate('Max depth must be integer.'))
            ->addRule(Form::MIN, $this->translate->translate('Minimum of Max depth must be %min%.', ['min' => 0]), 0);

        $form->addHidden('id');

        $form->addSubmit('save');


        $form->setDefaults(self::FORM_DEFAULTS);

        if ($this->recipeId)
        {
            $recipe = $this->recipeRepository->findOneById($this->recipeId);

            if ($recipe)
            {
                $form->getComponent('isDynamic')->setDisabled(TRUE);

                $form->setDefaults([
                    'id' => $recipe->getId(),
                    'name' => $recipe->getName(),
                    'isDynamic' => (int) $recipe->getIsDynamic(),
                    'maxInstances' => $recipe->getMaxInstances(),
                    'maxCategories' => $recipe->getMaxCategories(),
                    'maxDepth' => $recipe->getMaxDepth(),
                    'allowedParent' => $recipe->getAllowedParentId(),
                    'allowedCategories' => array_keys($this->recipeCategoryAllowedRepository->findByRecipeLang(
                        $recipe,
                        $this->activeLang)
                        ->toKeyIndex('CategoryId')
                    )
                ]);
            }
        }


        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

        return $form;
    }


    /**
     * @param Form $form
     * @throws \Exception
     */
    public function success(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            $recipe = NULL;

            if ($values->id > 0)
            {
                $recipe = $this->recipeRepository->findOneById($values->id);
            }

            $this->recipeManager->getConnection()->beginTransaction();

            try
            {
                if($recipe && $recipe->getMaxDepth() !== $values->maxDepth)
                {
                    $this->structureManager->resetParentLevels($recipe, $values->maxDepth + 2);
                }

                // TODO: Hodně dotazů, když $values->allowedParent === -1
                if ($recipe && $recipe->getAllowedParentId() !== $values->allowedParent)
                {
                    $this->structureManager->resetParents($recipe);
                }

                $recipe = $this->recipeManager->save(
                    $recipe,
                    $values->name,
                    ($recipe) ? $recipe->getIsDynamic() : $values->isDynamic,
                    $values->maxInstances,
                    $values->maxCategories,
                    $values->maxDepth,
                    $values->allowedParent
                );

                $this->recipeId = $recipe->getId();

                $this->structureInCategoryManager->deleteRelatedExceptRemaining($recipe, $values->allowedCategories);

                $diffCategories = $this->recipeCategoryAllowedManager->makeDiff($recipe, $this->activeLang, $values->allowedCategories);
                $this->recipeManager->saveAllowedCategories($recipe, $diffCategories);

                $this->recipeManager->getConnection()->commit();

                // Set defaults
                $form->getComponent('allowedParent')->setItems($this->getAllowedParents());
                $form->getComponent('id')->setValue($recipe->getId());
                $form->getComponent('isDynamic')->setDisabled(TRUE)->setValue((int)$recipe->getIsDynamic());

                // Flash
                $this->presenter->notificationAjax(
                    $this->translate->translate('Recipe saved'),
                    $this->translate->translate('Recipe was successfully saved.'),
                    'success',
                    FALSE
                );

                // Redraw controls
                $this->onSave();
            }
            catch (\Exception $exception)
            {
                $this->recipeManager->getConnection()->rollBack();
                throw $exception;
            }
        }
    }


    /**
     * Handle edit
     * @param int|NULL $id
     */
    public function handleOpen(int $id = NULL) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeId = $id;

            $this->presenter->handleModalToggle('show', '#wakers_structure_recipe_modal', FALSE);

            $this->onOpen();
        }
    }


    /**
     * @return array
     */
    public function getAllowedParents() : array
    {
        $allowedParents = [ -1 => 'Bez rodičovské struktury'];

        foreach ($this->recipeRepository->findAll() as $recipe)
        {
            $allowedParents[$recipe->getId()] = $recipe->getName();
        }

        return $allowedParents;
    }
}