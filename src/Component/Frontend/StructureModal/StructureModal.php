<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\StructureModal;


use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Controls\UploadControl;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Propel\Runtime\Collection\ObjectCollection;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\BaseModule\Util\SetDisabledForm;
use Wakers\CategoryModule\Repository\CategoryRepository;
use Wakers\PageModule\Database\Page;
use Wakers\PageModule\Repository\PageRepository;
use Wakers\StructureModule\Builder\IVariableFormBuilder;
use Wakers\StructureModule\Database\RecipeSlug;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValueFile;
use Wakers\StructureModule\Manager\StructureInCategoryManager;
use Wakers\StructureModule\Manager\StructureInPageManager;
use Wakers\StructureModule\Manager\StructureManager;
use Wakers\StructureModule\Manager\StructureValueFileManager;
use Wakers\StructureModule\Manager\StructureValueManager;
use Wakers\StructureModule\Repository\RecipeSlugRepository;
use Wakers\StructureModule\Repository\StructureInCategoryRepository;
use Wakers\StructureModule\Repository\StructureRepository;
use Wakers\StructureModule\Repository\StructureValueFileRepository;
use Wakers\StructureModule\Repository\StructureValueRepository;
use Wakers\StructureModule\Repository\RecipeVariableRepository;
use Wakers\StructureModule\Security\StructureAuthorizator;
use Wakers\UserModule\Database\User;


class StructureModal extends BaseControl
{
    use AjaxValidate;
    use SetDisabledForm;


    /**
     *  Regulární výraz pro formát: 21.3.2018 05:31:01
     */
    const REGEX_DATETIME = '([1-9]|1[0-9]|2[0-9]|3[0-1])\.([1-9]|1[0-2])\.([0-2]{1}[0-9]{3})\040(0[0-9]|1[0-9]|2[0-3])(\:(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])){2}';


    /**
     * Formát data
     */
    const FORMAT_DATETIME = 'j.n.Y H:i:s';


    /**
     * @var RecipeSlugRepository
     */
    protected $recipeSlugRepository;


    /**
     * @var RecipeVariableRepository
     */
    protected $recipeVariableRepository;


    /**
     * @var StructureRepository
     */
    protected $structureRepository;


    /**
     * @var StructureValueRepository
     */
    protected $structureValueRepository;


    /**
     * @var StructureValueFileRepository
     */
    protected $structureValueFileRepository;


    /**
     * @var StructureInCategoryRepository
     */
    protected $structureInCategoryRepository;


    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;


    /**
     * @var StructureManager
     */
    protected $structureManager;


    /**
     * @var StructureValueManager
     */
    protected $structureValueManager;


    /**
     * @var StructureValueFileManager
     */
    protected $structureValueFileManager;


    /**
     * @var StructureInPageManager
     */
    protected $structureInPageManager;


    /**
     * @var StructureInCategoryManager
     */
    protected $structureInCategoryManager;


    /**
     * @var IVariableFormBuilder
     */
    protected $IVariableFormBuilder;


    /**
     * Aktuální stránka
     * @var Page
     */
    protected $page;


    /**
     * Aktuální uživatel
     * @var User
     */
    protected $user;


    /**
     * Entita
     * @var RecipeSlug
     */
    protected $recipeSlug;


    /**
     * Entita
     * @var Structure
     */
    protected $structure;


    /**
     * Entity
     * @var ObjectCollection|RecipeVariable[]
     */
    protected $fileVariables;


    /**
     * Entity
     * @var ObjectCollection|RecipeVariable[]
     */
    protected $noFileVariables;


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * @var callable
     */
    public $onSaveNoFile = [];


    /**
     * @var callable
     */
    public $onSaveFile = [];


    /**
     * @var callable
     */
    public $onSaveMain = [];


    /**
     * @var string
     * @persistent
     */
    public $recipeSlugString;


    /**
     * @var int $structureId
     * @persistent
     */
    public $structureId;


    /**
     * StructureModal constructor.
     * @param RecipeSlugRepository $recipeSlugRepository
     * @param RecipeVariableRepository $recipeVariableRepository
     * @param StructureRepository $structureRepository
     * @param StructureValueRepository $structureValueRepository
     * @param StructureValueFileRepository $valueFileRepository
     * @param StructureInCategoryRepository $structureInCategoryRepository
     * @param CategoryRepository $categoryRepository
     * @param StructureManager $structureManager
     * @param StructureValueManager $structureValueManager
     * @param StructureInPageManager $structureInPageManager
     * @param StructureValueFileManager $structureValueFileManager
     * @param StructureInCategoryManager $structureInCategoryManager
     * @param IVariableFormBuilder $IValueFormBuilder
     * @param PageRepository $pageRepository
     * @param \Nette\Security\User $user
     */
    public function __construct(
        RecipeSlugRepository $recipeSlugRepository,
        RecipeVariableRepository $recipeVariableRepository,

        StructureRepository $structureRepository,
        StructureValueRepository $structureValueRepository,
        StructureValueFileRepository $valueFileRepository,
        StructureInCategoryRepository $structureInCategoryRepository,
        CategoryRepository $categoryRepository,

        StructureManager $structureManager,
        StructureValueManager $structureValueManager,
        StructureInPageManager $structureInPageManager,
        StructureValueFileManager $structureValueFileManager,
        StructureInCategoryManager $structureInCategoryManager,

        IVariableFormBuilder $IValueFormBuilder,

        PageRepository $pageRepository,
        \Nette\Security\User $user
    ) {
        $this->recipeSlugRepository = $recipeSlugRepository;
        $this->recipeVariableRepository = $recipeVariableRepository;

        $this->structureRepository = $structureRepository;
        $this->structureValueRepository = $structureValueRepository;
        $this->structureValueFileRepository = $valueFileRepository;
        $this->structureInCategoryRepository = $structureInCategoryRepository;
        $this->categoryRepository = $categoryRepository;

        $this->structureManager = $structureManager;
        $this->structureValueManager = $structureValueManager;
        $this->structureInPageManager = $structureInPageManager;
        $this->structureValueFileManager = $structureValueFileManager;
        $this->structureInCategoryManager = $structureInCategoryManager;

        $this->IVariableFormBuilder = $IValueFormBuilder;

        $this->page = $pageRepository->getActivePage();
        $this->user = $user->getIdentity()->getData()['userEntity'];
    }


    /**
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function render() : void
    {
        $recipeSlug = $this->getRecipeSlug();

        $this->template->recipe = $recipeSlug ? $recipeSlug->getRecipe() : NULL;
        $this->template->structure =  $this->getStructure();
        $this->template->fileVariables = $this->getFileVariables();
        $this->template->setFile(__DIR__.'/templates/modal.latte');
        $this->template->render();
    }


    /**
     * Variables form
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentFormNoFile() : Form
    {
        $variables = $this->getNoFileVariables();
        $recipeSlug = $this->getRecipeSlug();
        $structure = $this->getStructure();
        $recipeVariables = [];

        if ($structure)
        {
            $structureValues = $this->structureValueRepository->findByStructure($structure);

            foreach ($structureValues as $structureValue)
            {
                $recipeVariableSlug = $structureValue->getRecipeVariable()->getSlug();
                $recipeVariables[$recipeVariableSlug] = $structureValue;
            }
        }

        $form = new Form;

        /**
         * @var string $variableSlug
         * @var RecipeVariable $recipeVariable
         */
        foreach ($variables->toKeyIndex('Slug') as $variableSlug => $recipeVariable)
        {
            $value = key_exists($variableSlug, $recipeVariables) ? $recipeVariables[$variableSlug] : NULL;

            $component = $this->IVariableFormBuilder->buildComponent($recipeSlug->getRecipe(), $recipeVariable, $structure, $value);
            $form->addComponent($component, $recipeVariable->getSlug());
        }

        $form->addSubmit('save');

        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->successNoFile($form); };

        if (!$this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $this->setDisabledForm($form, TRUE);
        }

        return $form;
    }


    /**
     * Success - non-file inputs form
     * @param Form $form
     * @throws \Propel\Runtime\Exception\PropelException
     * @throws \Exception
     */
    public function successNoFile(Form $form) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $recipeSlug = $this->getRecipeSlug();
            $structure = $this->getStructure();
            $parentStructure = $structure ? $structure->getParent() : NULL;
            $noFileVariables = $this->getNoFileVariables();

            $this->structureManager->getConnection()->beginTransaction();

            try
            {
                $structure = $this->structureManager->save(
                    $structure,
                    $recipeSlug,
                    $this->user,
                    $parentStructure
                );

                $this->structureValueManager->saveNoFileFields(
                    $structure,
                    $noFileVariables,
                    $form->getValues()
                );

                if ($recipeSlug->getRecipe()->isDynamic() && !$this->structure)
                {
                    $this->structureInPageManager->add($this->page, $structure);
                    $this->presenter->notificationAjax('Nezapomeňte', 'Nezapomeňte položku zařadit do příslušných kategorií.', 'warning', FALSE);
                }

                $this->structureManager->getConnection()->commit();
            }
            catch (\Exception $exception)
            {
                $this->structureManager->getConnection()->rollBack();
                throw $exception;
            }

            $this->structure = $structure;
            $this->structureId = $structure->getId();

            $this->presenter->notificationAjax(
                "{$recipeSlug->getRecipe()->getName()} uložena",
                "{$recipeSlug->getRecipe()->getName()} byla úspěšně uložena.",
                'success',
                FALSE
            );

            $this->onSaveNoFile();
        }
    }


    /**
     * File Forms
     * @return Multiplier
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentFormFile() : Multiplier
    {
        $recipeSlug = $this->getRecipeSlug();
        $structure = $this->getStructure();
        $recipeVariables = $this->getFileVariables()->toKeyIndex('Slug');

        return new Multiplier(function ($variableSlug) use ($recipeVariables, $recipeSlug, $structure)
        {
            /** @var RecipeVariable $recipeVariable */
            $recipeVariable = $recipeVariables[$variableSlug];

            $component = $this->IVariableFormBuilder->buildComponent($recipeSlug->getRecipe(), $recipeVariable, $structure,NULL);

            $form = new Form;
            $form->addComponent($component, $recipeVariable->getSlug());
            $form->addSubmit('save');


            if (!$this->getStructure())
            {
                foreach ($form->getComponents() as $component)
                {
                    $component->setDisabled(TRUE);
                }
            }

            $form->onValidate[] = function (Form $form) { $this->validate($form); };
            $form->onSuccess[] = function (Form $form) { $this->successFile($form); };

            if (!$this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
            {
                $this->setDisabledForm($form, TRUE);
            }

            return $form;

        });
    }


    /**
     * Success - file input form
     * @param Form $form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function successFile(Form $form) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $recipeSlug = $this->getRecipeSlug();
            $structure = $this->getStructure();

            foreach ($form->getValues() as $recipeVariableSlug => $files)
            {
                $recipeVariable = $this->recipeVariableRepository->findOneByRecipeSlug($recipeSlug->getRecipe(), $recipeVariableSlug);
                $structureValue = $this->structureValueRepository->findOneByStructureAndVariableSlug($structure, $recipeVariableSlug);

                try
                {
                    if (!$structureValue)
                    {
                        $structureValue = $this->structureValueManager->createEmptyVariable($structure, $recipeVariable);
                    }

                    /** @var FileUpload $file */
                    foreach ($files as $file)
                    {
                        $this->structureValueFileManager->add($structureValue, $file);

                        $this->presenter->notificationAjax(
                            'Soubor nahrán',
                            "Soubor '{$file->getName()}' byl úspěšně nahrán.",
                            'success',
                            FALSE
                        );
                    }

                    // Reset rules
                    $component = $this->IVariableFormBuilder->buildComponent($recipeSlug->getRecipe(), $recipeVariable, $structure, $structureValue);
                    /** @var UploadControl $component */
                    $form->removeComponent($form->getComponent($recipeVariableSlug));
                    $form->addComponent($component, $recipeVariableSlug);

                }
                catch (\Exception $exception)
                {
                    $this->presenter->notificationAjax(
                        'Chyba při nahrávání',
                        "Soubor '{$file->getName()}' se nepodařilo nahrát. Chyba: {$exception->getMessage()}",
                        'error',
                        FALSE
                    );
                }
            }

            $this->onSaveFile();
        }
    }


    /**
     * File property forms
     * @return Multiplier
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentFormFileProperty() : Multiplier
    {
        $structureValueFiles = [];

        // TODO: Vyřešit přes SQL i když to nějak moc nezpomaluje :-D

        /** @var RecipeVariable $recipeVariable */
        foreach ($this->getFileVariables()->toKeyIndex('Slug') as $recipeVariable)
        {
            foreach ($recipeVariable->getStructureValues() as $structureValue)
            {
                foreach ($structureValue->getStructureValueFiles() as $structureValueFile)
                {
                    $structureValueFiles[$structureValueFile->getId()] = $structureValueFile;
                }
            }
        }

        return new Multiplier(function ($structureValueFileId) use ($structureValueFiles)
        {
            /** @var StructureValueFile $structureValueFile */
            $structureValueFile = $structureValueFiles[$structureValueFileId];

            $form = new Form;

            $form->addText('title')
                ->setRequired('Titulek je povinný.')
                ->addRule(Form::MAX_LENGTH, 'Maximální délka titulku je %d znaků.', 128);

            $form->addText('uploadedAt')
                ->setRequired('Datum nahrání je povinný.')
                ->addRule(Form::PATTERN, "Datum vytvoření musí být ve formátu: 24.8.2018 08:59:01.", self::REGEX_DATETIME);

            $form->addHidden('structureValueFileId', $structureValueFileId);

            $form->addSubmit('save');


            if (!$this->getStructure())
            {
                foreach ($form->getComponents() as $component)
                {
                    $component->setDisabled(TRUE);
                }
            }

            $form->setDefaults([
                'title' => $structureValueFile->getTitle(),
                'uploadedAt' => $structureValueFile->getUploadedAt()->format(self::FORMAT_DATETIME),
            ]);

            $form->onValidate[] = function (Form $form) { $this->validate($form); };
            $form->onSuccess[] = function (Form $form) { $this->successFileProperty($form); };

            if (!$this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
            {
                $this->setDisabledForm($form, TRUE);
            }

            return $form;
        });
    }


    /**
     * Success - file property
     * @param Form $form
     * @throws \Exception
     */
    public function successFileProperty(Form $form) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $values = $form->getValues();

            $uploadedAt = new DateTime($values->uploadedAt);

            $this->structureValueFileManager->update(
                $values->structureValueFileId,
                $uploadedAt,
                $values->title
            );

            $this->presenter->notificationAjax(
                'Atributy aktualizovány',
                'Dodatečné atributy souboru byly aktualizovány.',
                'success',
                FALSE
            );


            $this->onSaveFile();
        }
    }


    /**
     * Main form
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentFormMain() : Form
    {
        $recipeSlug = $this->getRecipeSlug();
        $structure = $this->getStructure();
        $parents = $this->getParentsExceptSubParents();

        $categories = $this->getCategories();

        $form = new Form;

        $form->addText('createdAt')
            ->setRequired('Datum vytvoření je povinný.')
            ->addRule(Form::PATTERN, "Datum vytvoření musí být ve formátu: 24.8.2018 08:59:01.", self::REGEX_DATETIME);

        $form->addText('updatedAt')
            ->setDisabled(TRUE);

        $form->addCheckboxList('categories', NULL, $categories)
            ->setRequired(FALSE);
            //->setRequired('Minimálně jedna kategorie je povinná.');

        if ($recipeSlug->getRecipe()->getMaxCategories() > 0)
        {
            $form->getComponent('categories')
                ->addRule(Form::MAX_LENGTH, 'Maximální počet kategorií je %d.', $recipeSlug->getRecipe()->getMaxCategories());
        }

        $form->addSelect('parent', NULL, $parents)
            ->setRequired(FALSE);

        $form->addSubmit('save');

        if (!$this->getStructure())
        {
            foreach ($form->getComponents() as $component)
            {
                $component->setDisabled(TRUE);
            }
        }

        if ($structure)
        {
            $parent = $structure->getParent();

            $parentId = $parent && !$parent->isRoot() ? $parent->getId() : -1;

            //$parentId = key_exists($parentId, $parents) ? $parentId : -1;

            $form->setDefaults([
                'createdAt' => $structure->getCreatedAt()->format(self::FORMAT_DATETIME),
                'updatedAt' => $structure->getUpdatedAt()->format(self::FORMAT_DATETIME),
                'categories' => array_keys(
                    $this->structureInCategoryRepository
                        ->findByStructure($structure)
                        ->toKeyIndex('CategoryId')
                ),
                'parent' => $parentId,
            ]);
        }

        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->successMain($form); };

        if (!$this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $this->setDisabledForm($form, TRUE);
        }

        return $form;
    }


    /**
     * Success - main form
     * @param Form $form
     * @throws \Exception
     */
    public function successMain(Form $form) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $recipeSlug = $this->getRecipeSlug();
            $structure = $this->getStructure();
            $values = $form->getValues();

            $createdAt = new DateTime($values->createdAt);
            $parent = ($values->parent > 0) ? $this->structureRepository->findOneById($values->parent) : NULL;

            $this->structureManager->getConnection()->beginTransaction();

            try
            {
                $structure = $this->structureManager->save(
                    $structure,
                    $recipeSlug,
                    $this->user,
                    $parent,
                    $createdAt
                );

                $diffCategories = $this->structureInCategoryManager->makeDiff($structure, $values->categories);
                $this->structureManager->saveCategories($structure, $diffCategories);

                $this->structureManager->getConnection()->commit();

                $parents = $this->getParentsExceptSubParents();

                $form->getComponent('parent')->setItems($parents);
                $form->getComponent('updatedAt')->setValue($structure->getUpdatedAt()->format(self::FORMAT_DATETIME));

                $this->presenter->notificationAjax(
                    'Nastavení aktualizováno',
                    'Další nastavení bylo aktualizováno.',
                    'success',
                    FALSE
                );

                $this->onSaveMain();
            }
            catch (\Exception $exception)
            {
                $this->structureManager->getConnection()->rollBack();
                throw $exception;
            }
        }
    }


    /**
     * @param int $structureValueFileId
     * @throws \Exception
     */
    public function handleRemoveFile(int $structureValueFileId) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $this->structureValueFileManager->remove($structureValueFileId);
            $this->presenter->notificationAjax('Soubor odstraněn', 'Soubor byl úspěšně odstraněn.', 'info', FALSE);
            $this->onSaveFile();
        }
    }


    /**
     * Open add modal (edit existing structure)
     * @param string $recipeSlug
     * @param int $structureId
     * @param bool $isNew
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function handleOpen(string $recipeSlug, int $structureId = NULL, bool $isNew = FALSE) : void
    {
        if ($this->presenter->isAjax() && $this->presenter->user->isAllowed(StructureAuthorizator::RES_STRUCTURE_MODAL))
        {
            $recipeSlugString = $recipeSlug;
            $this->recipeSlugString = $recipeSlugString;

            $recipeSlug = $this->getRecipeSlug();

            if ($recipeSlug)
            {
                if ($isNew)
                {
                    if ($recipeSlug->getRecipe()->isDynamic())
                    {
                        $count = $this->structureRepository->countByRecipeSlugAndPage($recipeSlugString, $this->page);
                    }
                    else
                    {
                        $count = $this->structureRepository->countByRecipeSlug($recipeSlugString);
                    }

                    if ($count >= $recipeSlug->getRecipe()->getMaxInstances() && $recipeSlug->getRecipe()->getMaxInstances() !== 0)
                    {
                        $message = $recipeSlug->getRecipe()->isDynamic() ? 'na této stránce' : '';

                        $this->presenter->notificationAjax(
                            'Dosažení limitu',
                            "Pro položku '{$recipeSlug->getRecipe()->getName()} [slug: {$recipeSlugString}]' byl {$message} dosažen limit. Maximální počet položek: {$recipeSlug->getRecipe()->getMaxInstances()}.",
                            'error',
                            TRUE
                        );
                    }
                }

                if ($structureId !== NULL)
                {
                    $structure = $this->getStructure();

                    if (!$structure)
                    {
                        $this->presenter->notificationAjax(
                            'Položka neexistuje',
                            "Položka s id '{$structureId}' neexistuje.",
                            'error',
                            TRUE
                        );
                    }
                }

                $this->presenter->handleModalToggle('show', '#wakers_structure_structure_modal', FALSE);
                $this->onOpen();
            }
            else
            {
                $this->presenter->notificationAjax('Chyba', "Předpis se slugem '{$recipeSlugString}' neexistuje", 'error', TRUE);
            }
        }
    }


    /**
     * @return ObjectCollection|RecipeVariable[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function getFileVariables() : ObjectCollection
    {
        $recipeSlug = $this->getRecipeSlug();

        if (!$this->fileVariables && $recipeSlug)
        {
            $this->fileVariables = $this->recipeVariableRepository->findFileVariables($recipeSlug->getRecipe());
        }

        if (!$this->fileVariables instanceof ObjectCollection)
        {
            $this->fileVariables = new ObjectCollection;
        }

        $structure = $this->getStructure();

        if ($structure)
        {
            $structureValueFileArray = $this->structureValueRepository->findStructureVariableFilesByStructure($structure);

            foreach ($this->fileVariables as $fileVariable)
            {
                $collection = [];

                $slug = $fileVariable->getSlug();

                if (key_exists($slug, $structureValueFileArray))
                {
                    $collection = $structureValueFileArray[$slug];
                }

                $fileVariable->setVirtualColumn('Files', $collection);
            }
        }

        return $this->fileVariables;
    }


    /**
     * @return ObjectCollection|RecipeVariable[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function getNoFileVariables() : ObjectCollection
    {
        $recipeSlug = $this->getRecipeSlug();

        if (!$this->noFileVariables && $recipeSlug)
        {
            $this->noFileVariables = $this->recipeVariableRepository->findNoFileVariables($recipeSlug->getRecipe());
        }

        if (!$this->noFileVariables instanceof ObjectCollection)
        {
            $this->noFileVariables = new ObjectCollection;
        }

        return $this->noFileVariables;
    }


    /**
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function getCategories() : array
    {
        $recipeSlug = $this->getRecipeSlug();
        $categories = [];

        foreach($this->categoryRepository->findByRecipe($recipeSlug->getRecipe()) as $category)
        {
            $level = $category->getLevel() - 2;
            $categories[$category->getId()] = $level . '|' .  str_repeat('––', $level) . ' ' . $category->getName();
        }

        return $categories;
    }


    /**
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function getParentsExceptSubParents() : array
    {
        $recipeSlug = $this->getRecipeSlug();
        $structure = $this->getStructure();
        $recipeId = $recipeSlug->getRecipe()->getAllowedParentId();

        $parents = [-1 => 'Bez nadřazené položky'];

        if ($recipeId && $structure)
        {
            $recipeSlugs = $this->recipeSlugRepository->findByRecipeId($recipeId)->toKeyIndex('Slug');
            $dbParents = $this->structureRepository->findParentsExceptSubParents(array_keys($recipeSlugs), $structure);
            $currentSubLevels = $this->structureRepository->countSubLevels($structure);

            foreach ($dbParents as $parent)
            {
                // TODO: načítat sub-levels count již v hlavním dotazu (zde se opakuje SQL v cyklu)
                $targetSubLevels = $this->structureRepository->countSubLevels($parent);

                if ($currentSubLevels <= $targetSubLevels && $parent->getLevel() + $currentSubLevels <= $recipeSlug->getRecipe()->getMaxDepth())
                {
                    $parents[$parent->getId()] = str_repeat('––', $parent->getLevel() - 1) .
                        "{$parent->getRecipeSlug()->getRecipe()->getName()} (# {$parent->getId()})";
                }
            }
        }

        return $parents;
    }


    /**
     * Optimalizace SQL
     * @return RecipeSlug|NULL
     */
    protected function getRecipeSlug() : ?RecipeSlug
    {
        if (!$this->recipeSlug && $this->recipeSlugString)
        {
            $this->recipeSlug = $this->recipeSlugRepository->findOneBySlug($this->recipeSlugString);
        }

        return $this->recipeSlug;
    }


    /**
     * Optimalizace SQL
     * @return Structure|NULL
     */
    protected function getStructure() : ?Structure
    {
        if (!$this->structure && $this->structureId)
        {
            $this->structure = $this->structureRepository->findOneById($this->structureId);
        }

        return $this->structure;
    }
}