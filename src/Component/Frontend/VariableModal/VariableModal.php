<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\VariableModal;


use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\BaseModule\Util\Validator;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Manager\RecipeVariableManager;
use Wakers\StructureModule\Repository\RecipeRepository;
use Wakers\StructureModule\Repository\RecipeVariableRepository;


class VariableModal extends BaseControl
{
    use AjaxValidate;


    /**
     * Defaultni hodnoty
     */
    const FORM_DEFAULTS = [
        'isRequired' => RecipeVariable::REQUIRED_YES,
        'maxFiles' => 0,
        'maxFileSize' => 0,
    ];


    /**
     * @var RecipeRepository
     */
    protected $recipeRepository;


    /**
     * @var RecipeVariableRepository
     */
    protected $variableRepository;


    /**
     * @var RecipeVariableManager
     */
    protected $variableManager;


    /**
     * Entita načtená při otevření modálního okna
     * @var Recipe
     */
    protected $recipe;


    /**
     * Entita načtena při otevření editace
     * @var RecipeVariable
     */
    protected $variable;


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
     * @persistent
     */
    public $variableId;


    /**
     * VariableModal constructor.
     * @param RecipeRepository $recipeRepository
     * @param RecipeVariableRepository $recipeVariableRepository
     * @param RecipeVariableManager $variableManager
     */
    public function __construct(
        RecipeRepository $recipeRepository,
        RecipeVariableRepository $recipeVariableRepository,
        RecipeVariableManager $variableManager
    ) {
        $this->recipeRepository = $recipeRepository;
        $this->variableRepository = $recipeVariableRepository;
        $this->variableManager = $variableManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        if ($this->recipeId && !$this->recipe)
        {
            $this->recipe = $this->recipeRepository->findOneById($this->recipeId);
        }

        $this->template->recipe = $this->recipe;
        $this->template->setFile(__DIR__.'/templates/variableModal.latte');
        $this->template->render();
    }


    /**
     * Variable Form
     * @return Form
     */
    protected function createComponentForm() : Form
    {
        $iniMaxSize = min((int) ini_get('upload_max_filesize'), (int) ini_get('post_max_size'));
        $maxSize = $iniMaxSize / ini_get('max_file_uploads') ; // MB


        $required = [
            RecipeVariable::REQUIRED_YES => 'Povinné',
            RecipeVariable::REQUIRED_NO  => 'Nepovinné',
        ];

        $types = [
            RecipeVariable::TYPE_TEXT_PLAIN     => 'Standardní text',
            RecipeVariable::TYPE_TEXT_FORMATTED => 'Formátovaný text',
            RecipeVariable::TYPE_PHONE          => 'Telefonní číslo',
            RecipeVariable::TYPE_EMAIL          => 'E-mailová adresa',
            RecipeVariable::TYPE_DATE           => 'Datum',
            RecipeVariable::TYPE_DATETIME       => 'Datum a čas',
            RecipeVariable::TYPE_LINK_INTERNAL  => 'Interní odkaz',
            RecipeVariable::TYPE_LINK_EXTERNAL  => 'Externí odkaz',
            RecipeVariable::TYPE_FILES          => 'Soubory',
            RecipeVariable::TYPE_IMAGES         => 'Obrázky',
            RecipeVariable::TYPE_SELECT_BOX     => 'Select box'
        ];

        $form = new Form;

        $form->addSelect('type', NULL, $types)
            ->setRequired('Typ je povinný.');

        $form->addText('label')
            ->setRequired('Label je povinný.')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka labelu jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka labelu je %d znaků.', 64);

        $form->addText('slug')
            ->setRequired('Slug je povinný.')
            ->addRule(Form::PATTERN,'Slug může obsahovat pouze znaky: a-z A-Z 0-9.', '[a-zA-Z0-9]*')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka slugu jsou %d znaky.', 3)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka slugu je %d znaků.', 32);

        $form->addText('tooltip')
            ->setRequired(FALSE)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka nápovědy je %d znaků.', 256);

        $form->addSelect('isRequired', NULL, $required)
            ->setRequired(TRUE);

        $form->addText('regexPattern')
            ->setRequired(FALSE)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka regexu je %d znaků.', 256);

        $form->addText('regexMessage')
            ->setRequired(FALSE)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka nápovědy regexu je %d znaků.', 256)
            ->addConditionOn($form['regexPattern'], Form::FILLED)
                ->setRequired('Nápověda k regexu je povinná.')
            ->endCondition();

        $form->addTextArea('allowedTypes')
            ->setRequired(FALSE)
            ->addRule(Validator::class . '::json', 'Konfigurace typů souborů musí být v platném JSON formátu.');

        $form->addText('maxFiles')
            ->setRequired('Max. souborů je povinné pole.')
            ->addRule(Form::INTEGER, 'Max. souborů musí být celé číslo.')
            ->addRule(Form::MIN, 'Minimální hodnota max. souborů musí být %d.', 0);



        $form->addText('maxFileSize')
            ->setRequired('Max. velikost souboru je povinná.')
            ->addRule(Form::FLOAT, 'Max. velikost souboru musí být číslo.')
            ->addRule(Form::MIN, 'Minimální hodnota max. velikosti souboru musí být %d.', 0)
            ->addRule(Form::MAX, 'Maximální hodnota max.velikosti souboru může být %d.', $iniMaxSize)
            ->setOption('iniMaxSize', $iniMaxSize)
            ->setOption('maxSize', $maxSize);

        $form->addTextArea('items')
            ->setRequired(FALSE)
            ->addRule(Validator::class . '::json', 'Konfigurace select boxu musí být v platném JSON formátu.');

        $form->addHidden('recipeId', $this->recipeId)
            ->setRequired(TRUE)
            ->addRule(Form::INTEGER);

        $form->addHidden('variableId', 0)
            ->setRequired(TRUE)
            ->addRule(Form::INTEGER);

        $form->addSubmit('save');

        $form->setDefaults(self::FORM_DEFAULTS);


        if ($this->variableId)
        {
            $variable = $this->variableRepository->findOneById($this->variableId);

            $form->setDefaults([
                'recipeId' => $variable->getRecipeId(),
                'variableId' => $variable->getId(),
                'type' => $variable->getType(),
                'label' => $variable->getLabel(),
                'slug' => $variable->getSlug(),
                'tooltip' => $variable->getTooltip(),
                'isRequired' => (int) $variable->getIsRequired(),
                'regexPattern' => $variable->getRegexPattern(),
                'regexMessage' => $variable->getRegexMessage(),
                'allowedTypes' => $variable->getAllowedTypes(),
                'maxFiles' => $variable->getMaxFiles(),
                'maxFileSize' => $variable->getMaxFileSize(),
                'items' => $variable->getItems()
            ]);
        }
        else
        {
            $form->setDefaults(self::FORM_DEFAULTS);
        }

        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

        return $form;
    }


    /**
     * @param Form $form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function success(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            if (!$this->recipe)
            {
                $this->recipe  = $this->recipeRepository->findOneById($values->recipeId);
            }

            $this->variable = $this->variableRepository->findOneById($values->variableId);

            try
            {
                $variable = $this->variableManager->save(
                    $this->recipe,
                    $this->variable,
                    $values->type,
                    $values->label,
                    $values->slug,
                    $values->isRequired,
                    $values->tooltip,
                    $values->regexPattern,
                    $values->regexMessage,
                    $values->allowedTypes,
                    $values->maxFiles,
                    $values->maxFileSize,
                    $values->items
                );

                $form->setValues(['variableId' => $variable->getId()]);

                $this->presenter->notificationAjax('Proměnná uložena', 'Proměnná byla úspěšně uložena.', 'success', FALSE);
                $this->onSave();
            }
            catch (DatabaseException $exception)
            {
                $this->presenter->notificationAjax('Chyba', $exception->getMessage(), 'error');
            }
        }
    }


    /**
     * Handle open
     * @param int $recipeId
     * @param int|NULL $variableId
     */
    public function handleOpen(int $recipeId, int $variableId = NULL) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->recipeId = $recipeId;
            $this->variableId = $variableId;

            $this->presenter->handleModalToggle('show', '#wakers_structure_variable_modal', FALSE);
            $this->onOpen();
        }
    }
}