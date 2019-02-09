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
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\CategoryModule\Repository\CategoryRepository;
use Wakers\LangModule\Database\Lang;
use Wakers\LangModule\Repository\LangRepository;
use Wakers\StructureModule\Database\Recipe;
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
     */
    public function __construct(
        RecipeRepository $recipeRepository,
        CategoryRepository $categoryRepository,
        RecipeCategoryAllowedRepository $recipeCategoryAllowedRepository,
        LangRepository $langRepository,

        RecipeManager $recipeManager,
        RecipeCategoryAllowedManager $recipeCategoryAllowedManager,
        StructureInCategoryManager $structureInCategoryManager,
        StructureManager $structureManager
    ) {
        $this->recipeRepository = $recipeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->recipeCategoryAllowedRepository = $recipeCategoryAllowedRepository;

        $this->recipeManager = $recipeManager;
        $this->recipeCategoryAllowedManager = $recipeCategoryAllowedManager;
        $this->structureInCategoryManager = $structureInCategoryManager;
        $this->structureManager = $structureManager;

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
            ->setRequired('Jméno je povinné.')
            ->addRule(Form::MIN_LENGTH, 'Min. délka jména jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Max. délka jména je %d znaků.', 32);

        $form->addSelect('isDynamic', NULL, $types)
            ->setRequired('Typ struktury je povinný.');

        $form->addText('maxInstances')
            ->setRequired('Max. počet instancí je povinné.')
            ->addRule(Form::INTEGER, 'Max. počet instancí musí být celé číslo.')
            ->addRule(Form::MIN, 'Minimální hodnota max. počtu instancí je: %d', 0);

        $form->addCheckboxList('allowedCategories', NULL, $allowedCategories)
            ->setRequired('Kategorie je povinná.');

        $form->addText('maxCategories')
            ->setRequired('Max. počet kategorií je povinný.')
            ->addRule(Form::INTEGER, 'Max. počet kategorií musí být celé číslo.')
            ->addRule(Form::MIN, 'Minimální hodnota Max. počtu kategorií je: %d.', 0);

        $form->addSelect('allowedParent', NULL, $this->getAllowedParents())
            ->setRequired(FALSE);

        $form->addText('maxDepth')
            ->setRequired('Max. hloubka zanoření je povinná.')
            ->addRule(Form::INTEGER, 'Max. hloubka zanoření musí být celé číslo.')
            ->addRule(Form::MIN, 'Minimální hodnota max. hloubky zanoření je: %d.', 0);

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
                    'Předpis uložen',
                    'Předpis byl úspěšně uložen.',
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