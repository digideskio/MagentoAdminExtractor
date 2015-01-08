AKENEO Component : MagentoAdminExtractor
========================================

## What MagentoAdminExtractor is about ?

The component use Goutte to connect to the Magento back-end and extracts data which are human-visible.
Its main purpose is to be included in the Magento Connector Bundle (https://github.com/akeneo/MagentoConnectorBundle)
test suite in order to check that data sent to Magento are well received.

## What can I do with MagentoAdminExtractor ?

- Allows to connect to Magento Admin
- Allows to navigate in Magento backend
- Allows to change the number of rows you can view par page in grid
- Allows to extract products with their attributes and associations
- Allows to extract attributes with their parameters and options
- Allows to extract categories tree

## How to install MagentoAdminExtractor ?

Clone the project :
``git clone git@github.com:akeneo/MagentoAdminExtractor.git``

And then create a symlink in your project with ``ln -s path_to_MagentoAdminExtractor path_to_your_project``

## How to use the extractor ?

### Prerequisite

Remove the secret keys to url to be able to extract data.
System -> Configuration -> Advanced -> Admin -> Security -> Add secret key to urls : NO

### Try to extract

You can have a try and see examples of code with test.php, add print_r() on data you want to see in terminal and
``$ php test.php``

### Connect to a Magento back-end

``` php
use Akeneo\Component\MagentoAdminExtractor\Manager\LogInException;
use Akeneo\Component\MagentoAdminExtractor\Manager\MagentoAdminConnectionManager;

$connectionManager = new MagentoAdminConnectionManager(
    MAGENTO_ADMIN_URL,
    MAGENTO_ADMIN_LOGIN,
    MAGENTO_ADMIN_PWD
);

try {
    $mainPageCrawler = $connectionManager->connectToAdminPage();
    $client          = $connectionManager->getClient();
} catch (LogInException $e) {
    die($e->getMessage() . PHP_EOL);
}
```

### Navigate with navigation manager

Second argument of ->goToXXXCatalog() is a numeric and allows you specify the number of rows per page you want to have in your grid. It's 20 by default.

``` php
use Akeneo\Component\MagentoAdminExtractor\Manager\NavigationManager;

$client            = $connectionManager->getClient();
$navigationManager = new NavigationManager($client);

$productCatalogCrawler   = $navigationManager->goToProductCatalog($mainPageCrawler, 50);
$attributeCatalogCrawler = $navigationManager->goToAttributeCatalog($mainPageCrawler, 50);
$myURLCrawler            = $navigationManager->goToUri('GET', 'http://magento.local/index.php/admin/catalog_product/edit/id/5/key/secret_key/store/1');
```

### How to extract products and their attributes ?

``` php
use Akeneo\Component\MagentoAdminExtractor\Extractor\ProductAttributeExtractor;

$productAttributeExtractor = new ProductAttributeExtractor($navigationManager);
$productCatalogCrawler = $navigationManager->goToProductCatalog($mainPageCrawler, 50);
$products = $productAttributeExtractor->filterRowsAndExtract($productCatalogCrawler);
```

### How to extract attributes ?

``` php
use Akeneo\Component\MagentoAdminExtractor\Extractor\AttributeExtractor;

$attributeExtractor = new AttributeExtractor($navigationManager);
$attributeCatalogCrawler = $navigationManager->goToAttributeCatalog($mainPageCrawler, 50);
$attributes = $attributeExtractor->filterRowsAndExtract($attributeCatalogCrawler);
```

### How to extract categories ?

``` php
use Akeneo\Component\MagentoAdminExtractor\Extractor\CategoriesExtractor;

$categoriesExtractor = new CategoriesExtractor($navigationManager);
$categories = $categoriesExtractor->extract($mainPageCrawler);
```

## How to create my own extractor ?

### New extractor for entities in a grid

``` php
class MyEntityExtractor extends AbstractGridExtractor
{
    public function extract(Crawler $myEntityNodeCrawler, $myEntityName = '')
    {
        // Your stuff here
    }

    protected function getExtractedEntity()
    {
        return 'myEntity';
    }
}
```

### New extractor for entities not in a grid

``` php
class MyEntityExtractor extends AbstractExtractor
{
    public function extract(Crawler $mainPageCrawler)
    {
        // Your stuff here
    }
}
```

## What Magento versions are supported by Akeneo MagentoAdminExtractor ?

For now the component is only tested with **Magento 1.8**.
