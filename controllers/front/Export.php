<?php

class TrovaprezziExportModuleFrontController extends ModuleFrontController
{
    public function __construct() 
    {
        $this->module = 'trovaprezzi';
        parent::__construct();
    }

    public function initContent()
    {
        $endrecord = '<endrecord>';

        $sql = new DbQuery();
        $sql->select("
            p.id_product,
            pl.name,
            p.id_category_default,
            m.name as manufacturer,
            pl.description,
            p.reference,
            pl.link_rewrite,
            p.quantity,
            0 as shipping_cost,
            '' as manufacturer_reference,
            p.ean13,
            p.weight,
            i.id_image");
        $sql->from('product', 'p');
        $sql->innerJoin('product_lang', 'pl', 'p.id_product = pl.id_product');
        $sql->leftOuterJoin('manufacturer', 'm', 'p.id_manufacturer = m.id_manufacturer');
        $sql->leftOuterJoin('image', 'i', 'p.id_product = i.id_product AND i.cover = 1');

        $products = Db::getInstance()->executeS($sql);

        foreach($products as $product)
        {
            $product['description'] = Tools::getDescriptionClean($product['description']);
            $product['image_url'] = $context->link->getImageLink($product['link_rewrite'], $item['id_image']);
            $product['price'] = Product::getPriceStatic($product['id_product']);
            $product['avaibility'] = 'Disponibile';

            $currentCategory = new Category($product['id_category_default']);
            $parents = $currentCategory->getParentsCategories();
            $product['category_tree'] = implode(',', $parents);
        }

        $this->context->smarty->assign(array(
            'products' => $products,
            'endrecord' => $endrecord
        ));

        $this->setTemplate('module:trovaprezzi/views/templates/front/export.tpl');
    }
} 