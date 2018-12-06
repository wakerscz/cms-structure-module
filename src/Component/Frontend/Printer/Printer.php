<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\StructureModule\Component\Frontend\Printer;


use Nette\InvalidArgumentException;
use Nette\Utils\Paginator;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\LangModule\Repository\LangRepository;
use Wakers\PageModule\Repository\PageRepository;
use Wakers\StructureModule\Entity\StructureResult;
use Wakers\StructureModule\Repository\IPrinterRepository;
use Wakers\StructureModule\Repository\StructureRepository;


class Printer extends BaseControl
{
    /**
     * @var IPrinterRepository
     */
    protected $IPrinterRepository;


    /**
     * @var LangRepository
     */
    protected $lanRepository;


    /**
     * @var PageRepository
     */
    protected $pageRepository;


    /**
     * @var Paginator
     */
    protected $paginator;


    /**
     * @var int
     */
    public $pagination;


    /**
     * Printer constructor.
     * @param IPrinterRepository $IPrinterRepository
     * @param LangRepository $langRepository
     * @param PageRepository $pageRepository
     * @param int $pagination
     */
    public function __construct(
        IPrinterRepository $IPrinterRepository,
        LangRepository $langRepository,
        PageRepository $pageRepository,
        int $pagination
    ) {
        $this->IPrinterRepository = $IPrinterRepository;
        $this->lanRepository = $langRepository;
        $this->pageRepository = $pageRepository;
        $this->pagination = $pagination;
    }


    /**
     * @param array $attrs
     * @throws \ReflectionException
     */
    public function render(array $attrs) : void
    {
        $this->template->rows = $this->getResult($attrs);
        $this->template->paginator = $this->paginator;

        $this->template->setFile(StructureRepository::TEMPLATE_PATH . $attrs['template']);
        $this->template->render();
    }


    /**
     * @param array $data
     * @throws \Nette\Application\AbortException
     * @throws \ReflectionException
     */
    public function handleGetResult(array $data) : void
    {
        $structures = $this->getResult($data);
        $this->presenter->sendJson($structures);
    }


    /**
     * @param array $attrs
     * @return array|StructureResult[]
     * @throws \ReflectionException
     */
    protected function getResult(array $attrs)
    {
        $method = $attrs['method'];
        $params = $attrs['params'];

        // Lang
        if (isset($params['lang']) && $params['lang']->getName() !== $params['lang'])
        {
            $params['lang'] = $this->lanRepository->findOneByName($params['lang']);
        }
        else
        {
            $params['lang'] =  $this->lanRepository->getActiveLang();
        }

        // Sort
        if (!isset($params['sort']))
        {
            $params['sort'] = 'ASC';
        }

        // Pagination
        if (isset($params['paginationLimit']))
        {
            $count = $this->IPrinterRepository->countByCategorySlugs($params['lang'], $params['categorySlugs']);

            // Paginator
            $this->paginator = new Paginator;
            $this->paginator->setItemCount($count);
            $this->paginator->setItemsPerPage($params['paginationLimit']);
            $this->paginator->setPage($this->pagination);

            // Limit & Offset
            $params['paginationLimit'] = $this->paginator->getLength();
            $params['paginationOffset'] = $this->paginator->getOffset();
        }
        else
        {
            $params['paginationLimit'] = NULL;
            $params['paginationOffset'] = NULL;
        }

        if (!isset($params['page']))
        {
            $params['page'] = NULL;
        }

        // Call Method
        $rcParams = [];
        $rc = new \ReflectionClass($this->IPrinterRepository);

        if(!$rc->hasMethod($method))
        {
            $class = $rc->getName() . '::' . $method;
            throw new InvalidArgumentException("Method '{$method}' used in template does not exist. It must exists in '{$class}'.");
        }


        foreach ($rc->getMethod($method)->getParameters() as $parameter)
        {
            if (!key_exists($parameter->getName(), $params))
            {
                $class = $rc->getName() . '::' . $method;
                throw new InvalidArgumentException("Parameter '{$parameter->getName()}' needed by '{$class}' does not used in your template.");
            }

            $rcParams[] = $params[$parameter->getName()];
        }

        // Result
        $result = $this->IPrinterRepository->$method(...$rcParams);

        return $result;
    }


    public function handlePaginate(int $page) : void
    {
        $this->pagination = $page;
    }
}