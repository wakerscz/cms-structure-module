# Structure Module
Modul umožňuje definovat datové struktury. Podle zvolených pravidel generuje editační formuláře. Na základě dalších pravidel předává data z formulářů do šablon.

## Jak to funguje?
Viz video: [Jak implementovat one-page web (na míru) za 15 minut](http://www.github.com/wakerscz/cms-sandbox#o-projektu).

## Komponenty
1. `Frontend\Printer` - Vypisuje data ze struktur.
1. `Frontend\RecipeSummaryModal` - Přehled všech definovaných předpisů struktur.
1. `Frontend\RecipeModal` - Vytváření a editace předpisů struktur.
1. `Frontend\RecipeRemoveModal` - Odstranění celého předpisu struktury.
1. `Frontend\RecipeSlugModal` - Vytváření, editace a přehled slugů (klíčů) u předpisu struktury.
1. `Frontend\RecipeSlugRemoveModal` - Odstranění slugu (klíče) z předpisu struktury.
1. `Frontend\VariableSummaryModal` - Přehled všech proměnných v předpisu struktury.
1. `Frontend\VariableModal` - Vytvoření a editace proměnné v předpisu struktury.
1. `Frontend\VariableRemoveModal` - Odstranění proměnné z předpisu struktury.
1. `Frontend\StructureModal` - Automaticky generovaný formulář pro přidávání a editaci hodnot struktury.
1. `Frontend\StructureRemoveModal` - Odstranění struktury.


## Rozdělení struktur

- **Statické** jsou opakující se části webu - menu, hlavička, patička, atp.
- **Dynamické** se neopakují, jsou to například aktuality, články, atp.


## Výpis struktur

Stuktury lze zařazovat i do kategorií. Pro výpis struktur existují 4 základní metody viz `Wakers\StructureModule\Repository\PrinterRepository`.

**Je vyloženě nutné** se s metodami podrobně seznámit - je velmi důležité znát, jaké parametry přejímají a jak získávají data z databáze.

### Metody pro výpis dle kategorií (categorySlugs)
1. `findByCategorySlugs`
1. `findRecursiveByCategorySlugs`

### Metody pro výpis dle klíče předpisu (recipeSlugs)
1. `findByRecipeSlugsAndPage`
1. `findRecursiveByRecipeSlugsAndPage`

## V šabloně lze použít
Zrychlený zápis, který zavolá příslušnou metodu, předá parametry a výsledné struktury vrátí 
jako **(flat / tree) array** objektů typu `Wakers\StructureModule\Entity\StructureResult`.

```latte
{* Rekurzivní výpis statických struktur dle recipeSlugs *}

{control structurePrinter [
    'method' => 'findRecursiveByRecipeSlugsAndPage',
    'params' => [
        'recipeSlugs' => [
            'staticke-menu'
        ],
        'sort' => 'ASC',

    ],
    'template' => 'static/navbar.latte'
]}
``` 

```latte
{* Výpis dynamických struktur dle kategorií (categorySlugs) *}

{control structurePrinter [
    'method' => 'findByCategorySlugs',
    'params' => [
        'categorySlugs' => [
            'aktuality'
        ],
        'paginationLimit' => 1,
        'sort' => 'DESC',
        'filterByPagePublished' => TRUE
    ],
    'template' => 'dynamic/homepageNews.latte'
]}
``` 


```latte
{* Výpis dynamických struktur dle recipeSlugs - svázaných s určitou page *}

{control structurePrinter [
    'method' => 'findByRecipeSlugsAndPage',
    'params' => [
        'recipeSlugs' => [
            'news'
        ],
        'sort' => 'DESC',
        'page' => $presenter->template->pageEntity,
    ],
    'template' => 'dynamic/newsDetail.latte'
]}
``` 

## Přidávání a editace struktur
Struktury se musejí nějakým způsobem vytvářet a editovat proto je v sandboxu předpřipraven soubor 
[_buttons.latte](https://github.com/wakerscz/cms-sandbox/blob/master/app/template/structure/_buttons.latte),
který obsahuje předdefinované bloky pro snazší použití.

- [Vytvoření struktury](https://github.com/wakerscz/cms-sandbox/blob/master/app/template/page/%40layout.latte#L20-L23)
- [Editace / odstranění struktury](https://github.com/wakerscz/cms-sandbox/blob/master/app/template/structure/dynamic/newsDetail.latte#L13-L18)
- [Přidání pod-stránky](https://github.com/wakerscz/cms-sandbox/blob/master/app/template/structure/dynamic/homepageNews.latte#L10-L14) - někdy potřebné před vytvořením dynamické struktury.