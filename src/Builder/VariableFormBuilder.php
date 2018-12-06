<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Builder;


use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;
use Nette\Utils\Json;
use Wakers\PageModule\Repository\PageRepository;
use Wakers\StructureModule\Database\Recipe;
use Wakers\StructureModule\Database\RecipeVariable;
use Wakers\StructureModule\Database\Structure;
use Wakers\StructureModule\Database\StructureValue;
use Wakers\StructureModule\Repository\StructureValueFileRepository;


class VariableFormBuilder implements IVariableFormBuilder
{
    /**
     * Cesty k templates
     */
    const
        TEMPLATE_PATH = __DIR__.'/../Component/Frontend/StructureModal/templates/variable/',
        DEFAULT_TEMPLATE_PATH = __DIR__.'/../Component/Frontend/StructureModal/templates/variable/text_plain.latte';


    /**
     * @var PageRepository
     */
    protected $pageRepository;


    /**
     * @var StructureValueFileRepository
     */
    protected $structureValueFileRepository;


    /**
     * Nastavuje se při volání buildComponent()
     * @var Recipe
     */
    protected $recipe;


    /**
     * Nastavuje se při volání buildComponent()
     * @var Structure
     */
    protected $structure;


    /**
     * Nastavuje se při volání buildComponent()
     * @var RecipeVariable
     */
    protected $recipeVariable;


    /**
     * Nastavuje se při volání buildComponent()
     * @var StructureValue
     */
    protected $structureValue;


    /**
     * Cashed array pro link_internal
     * @var array
     */
    protected $pages;


    /**
     * Cashed array pro files
     * @var array
     */
    protected $filesCountBySlug = [];


    /**
     * TODO: Remove dependencies, create component by component->create()
     * ValueFormBuilder constructor.
     * @param PageRepository $pageRepository
     * @param StructureValueFileRepository $structureValueFileRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        StructureValueFileRepository $structureValueFileRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->structureValueFileRepository = $structureValueFileRepository;
    }


    /**
     * @param Recipe $recipe
     * @param RecipeVariable $recipeVariable
     * @param Structure|null $structure
     * @param StructureValue|NULL $structureValue
     * @return IComponent
     */
    public function buildComponent(
        Recipe $recipe,
        RecipeVariable $recipeVariable,
        ?Structure $structure,
        ?StructureValue $structureValue
    ) : IComponent
    {
        $this->recipe = $recipe;
        $this->structure = $structure;
        $this->recipeVariable = $recipeVariable;
        $this->structureValue = $structureValue;

        $type = strtolower($recipeVariable->getType());

        /** @var IComponent|TextInput|TextArea|UploadControl|SelectBox $component */
        $component = $this->$type();

        $path = self::TEMPLATE_PATH . strtolower($recipeVariable->getType()) . '.latte';

        if (!file_exists($path))
        {
            $path = self::DEFAULT_TEMPLATE_PATH;
        }

        $component->setOption('path', $path);
        $component->setOption('variable', $recipeVariable);

        return $component;
    }


    /**
     * @return TextInput
     */
    protected function text_plain() : TextInput
    {
        $component = new TextInput($this->recipeVariable->getLabel());
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @return TextArea
     */
    protected function text_formatted() : TextArea
    {
        $component = new TextArea($this->recipeVariable->getLabel());
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @return TextInput
     */
    protected function phone() : TextInput
    {
        $label = $this->recipeVariable->getLabel();
        $labelLow = strtolower($label);

        $component = new TextInput($label);
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $component->addRule(
            Form::PATTERN,
            "Pole '{$labelLow}' musí být v mezinárodním formátu. Př: +420 000 000 000.",
            '\+[0-9]{1,4}\s[0-9]{1,3}\s[0-9]{1,3}\s[0-9]{1,3}'
        );

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @return TextInput
     */
    protected function email() : TextInput
    {
        $label = $this->recipeVariable->getLabel();
        $labelLow = strtolower($label);

        $component = new TextInput($label);
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $component->addRule(
            Form::EMAIL,
            "Pole '{$labelLow}' musí být platná e-mailová adresa."
        );

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @return TextInput
     */
    protected function date() : TextInput
    {
        $label = $this->recipeVariable->getLabel();
        $labelLow = strtolower($label);

        $component = new TextInput($label);
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $component->addRule(
            Form::PATTERN,
            "Pole '{$labelLow}' musí být ve formátu: 24.8.2018.",
            '([1-9]|1[0-9]|2[0-9]|3[0-1])\.([1-9]|1[0-2])\.([0-2]{1}[0-9]{3})'
        );

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }

    /**
     * @return TextInput
     */
    protected function datetime() : TextInput
    {
        $label = $this->recipeVariable->getLabel();
        $labelLow = strtolower($label);

        $component = new TextInput($label);
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $component->addRule(
            Form::PATTERN,
            "Pole '{$labelLow}' musí být ve formátu: 24.8.2018 08:59",
            '([1-9]|1[0-9]|2[0-9]|3[0-1])\.([1-9]|1[0-2])\.([0-2]{1}[0-9]{3})\040(0[0-9]|1[0-9]|2[0-3])\:(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])'
        );

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @return SelectBox
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function link_internal() : SelectBox
    {
        $component = new SelectBox($this->recipeVariable->getLabel());
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $items = $this->getPages();
        $component->setItems($items);

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getLinkToUrlId());
        }

        return $component;
    }


    /**
     * @return TextInput
     */
    protected function link_external() : TextInput
    {
        $label = $this->recipeVariable->getLabel();
        $labelLow = strtolower($label);

        $component = new TextInput($label);
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $component->addRule(
            Form::PATTERN,
            "Pole '{$labelLow}' musí být platný HTTP/s odkaz.",
            '(https?\:\/\/)(.{4,247})'
        );

        $component->addRule(
            Form::MAX_LENGTH,
            "Pole '{$labelLow}' může obsahovat maximálně %d znaků.",
            255
        );

        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @return UploadControl
     * @throws \Nette\Utils\JsonException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function files() : UploadControl
    {
        $label = $this->recipeVariable->getLabel();
        $labelLow = strtolower($label);

        $component = new UploadControl($label, TRUE);
        $component = $this->setPattern($component);
        $component->setRequired(FALSE);

        $types = Json::decode($this->recipeVariable->getAllowedTypes(), Json::FORCE_ARRAY);

        $mimes = [];
        $extensions = [];

        foreach ($types as $extension => $mimeType)
        {
            if (!in_array($mimeType, $mimes))
            {
                $mimes[] = $mimeType;
            }

            if (!in_array($extension, $extensions))
            {
                $extensions[] = $extension;
            }
        }

        $component->addRule(
            Form::MIME_TYPE,
            "Všechny soubory v poli '{$labelLow}' musí mít mime-type: " . implode(', ', $mimes),
            $mimes
        );

        $implodedExtensions = implode('|', $extensions);

        $component->addRule(
            Form::PATTERN,
            "Všechny soubory v poli '{$labelLow}' musí mít koncovku: " . implode(', ', $extensions),
            ".*\.({$implodedExtensions})"
        );

        // Max velikosti dle PHP
        $iniMaxUpFiles = ini_get('max_file_uploads'); // Count
        $iniMaxSize = min((int) ini_get('upload_max_filesize'), (int) ini_get('post_max_size')); // Max request size in MB

        // Max velikost souboru uložená v DB
        $maxFileSize = $this->recipeVariable->getMaxFileSize(); // MB / NULL

        // Velikost pro jeden nahrávaný soubor
        $maxSize = $iniMaxSize / $iniMaxUpFiles ; // MB

        // Nastav velikost pro jeden nahrávaný soubor dle DB
        if ($maxFileSize > 0)
        {
            $maxSize = $maxFileSize; // MB
        }

        // Přidej pravidlo pro max velikost souboru
        $component->addRule(
            Form::MAX_FILE_SIZE,
            "Maximální velikost jednoho souboru v poli '{$labelLow}' může být " . round($maxSize, 2) ." MB.",
            $maxSize * 1024 * 1024 // bytes
        );

        // Nastav maximální počet nahrávaných souborů dle PHP
        $maxUpFiles = $iniMaxUpFiles;

        // Nastav maximální počet nahrávaných souborů když př (500 mb / 50 < 20mb)
        if ($iniMaxSize / $iniMaxUpFiles < $maxSize)
        {
            // ~ 500 / 20 = 25
            $maxUpFiles = (int) ($iniMaxSize / $maxSize);
        }

        // Přidej pravidlo pro max uploadovaných souborů
        $component->addRule(
            Form::MAX_LENGTH,
            "Pole '{$labelLow}' může najednou nahrávat maximálně %d souborů/y.",
            $maxUpFiles
        );

        // Maximální počet souborů v políčku
        $maxFiles = $this->recipeVariable->getMaxFiles();

        if ($maxFiles > 0)
        {
            $inDb = $this->getFilesCount($this->recipeVariable->getSlug());
            $available = $maxFiles - $inDb;

            $component->addRule(
                Form::MAX_LENGTH,
                "V poli '{$labelLow}' lze nahrát už jen %d soubory/ů.",
                $available
            );
        }

        return $component;
    }


    /**
     * @return UploadControl
     * @throws \Nette\Utils\JsonException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function images() : UploadControl
    {
        $component = $this->files();

        return $component;
    }


    /**
     * @return SelectBox
     * @throws \Nette\Utils\JsonException
     */
    protected function select_box() : SelectBox
    {
        $component = new SelectBox($this->recipeVariable->getLabel());
        $component = $this->setRequired($component);
        $component = $this->setPattern($component);

        $items = Json::decode($this->recipeVariable->getItems(), Json::FORCE_ARRAY);

        $component->setItems($items);


        if ($this->structureValue)
        {
            $component->setDefaultValue($this->structureValue->getContent());
        }

        return $component;
    }


    /**
     * @param IComponent|TextInput|TextArea|UploadControl|SelectBox $IComponent
     * @return IComponent|TextInput|TextArea|UploadControl|SelectBox
     */
    protected function setRequired(IComponent $IComponent) : IComponent
    {
        $IComponent->setRequired(FALSE);

        if ($this->recipeVariable->getIsRequired())
        {
            $label = strtolower($this->recipeVariable->getLabel());
            $IComponent->setRequired("Pole '{$label}' je povinné.");
        }

        return $IComponent;
    }


    /**
     * @param IComponent|TextInput|TextArea|UploadControl|SelectBox $IComponent
     * @return IComponent|TextInput|TextArea|UploadControl|SelectBox
     */
    protected function setPattern(IComponent $IComponent) : IComponent
    {
        if ($this->recipeVariable->getRegexPattern())
        {
            $IComponent->addRule(Form::PATTERN, $this->recipeVariable->getRegexMessage(), $this->recipeVariable->getRegexPattern());
        }

        return $IComponent;
    }

    /**
     * TODO: load in Form Control
     * Cashed SQL
     * @param string $slug
     * @return int
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getFilesCount(string $slug)
    {
        if (/*!$this->filesCountBySlug &&*/ $this->structure)
        {
            $result = [];

            $files = $this->structureValueFileRepository
                ->findByRecipeAndStructure($this->recipe, $this->structure);

            foreach ($files as $structureValueFile)
            {
                $dbSlug = $structureValueFile->getStructureValue()->getRecipeVariable()->getSlug();
                $result[$dbSlug][] = $structureValueFile;
            }

            $this->filesCountBySlug = $result;
        }

        if (key_exists($slug, $this->filesCountBySlug))
        {
            return count($this->filesCountBySlug[$slug]);
        }

        return 0;

    }


    /**
     * TODO: load in Form Control
     * Cashed SQL
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function getPages()
    {
        if (!$this->pages)
        {
            $items = [0 => 'Žádný odkaz'];

            $roots = $this->pageRepository->findAllJoinUrl();

            foreach ($roots as $root)
            {
                $lang = $root->getPageUrl()->getLang();
                $tree = $this->pageRepository->findAllByLevelNameByLangAsTree($lang, TRUE);
                $pages = $this->pageRepository->findAllByLevelNameByLang($tree, TRUE);

                foreach ($pages as $page)
                {
                    $items[$page->getId()] = str_repeat('––', $page->getLevel() - 2) . ' ' . $page->getName();
                }
            }

            $this->pages = $items;
        }

        return $this->pages;
    }
}