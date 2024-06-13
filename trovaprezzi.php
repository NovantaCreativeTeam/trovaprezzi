<?php

class Trovaprezzi extends Module
{
    public function __construct()
    {
        $this->name = 'trovaprezzi';
        $this->version = '0.9.2';
        $this->author = 'Novanta';
        $this->need_instance = 1; //??
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Trovaprezzi Export & Trusted Program');
        $this->description = $this->l('Enable Trovaprezzi export functionality.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install() {
        // Inizializzo i parametri di configurazione
        Configuration::updateValue('TP_EXPORT_TYPE', 0);
        Configuration::updateValue('TP_TRUSTED_PROGRAM_ENABLED', 0);
        Configuration::updateValue('TP_MERCHANT_KEY', '');
        Configuration::updateValue('TP_CARRIER', Configuration::get('PS_CARRIER_DEFAULT'));
        Configuration::updateValue('TP_PRODUCT_VARIANTS_ENABLED', 0);
        Configuration::updateValue('TP_CATEGORIES', 0);
        Configuration::updateValue('TP_FORCE_UNIT_PRICE', 0);

        if(parent::install() == false || $this->registerHook('displayOrderConfirmation') == false)
        {
            return false;
        }

        return true;
    }

    public function uninstall() {
        // Rimuovo i parametri di configurazione
        Configuration::deleteByName('TP_EXPORT_TYPE');
        Configuration::deleteByName('TP_TRUSTED_PROGRAM_ENABLED');
        Configuration::deleteByName('TP_MERCHANT_KEY');
        Configuration::deleteByName('TP_CARRIER');
        Configuration::deleteByName('TP_PRODUCT_VARIANTS_ENABLED');
        Configuration::deleteByName('TP_CATEGORIES');
        Configuration::deleteByName('TP_FORCE_UNIT_PRICE');

        return parent::uninstall();
    }

    /**
     * Pagina di configurazione del Back Office
     */
    public function getContent() {
        $message = '';

        if(Tools::isSubmit('submitConfiguration'))
        {
            Configuration::updateValue('TP_EXPORT_TYPE', Tools::getValue('tp_export_type'));
            Configuration::updateValue('TP_TRUSTED_PROGRAM_ENABLED', Tools::getValue('tp_trusted_program_enabled'));
            Configuration::updateValue('TP_MERCHANT_KEY', Tools::getValue('tp_merchant_key'));
            Configuration::updateValue('TP_CARRIER', Tools::getValue('tp_carrier'));
            Configuration::updateValue('TP_PRODUCT_VARIANTS_ENABLED', Tools::getValue('tp_product_variants_enabled'));
            Configuration::updateValue('TP_FORCE_UNIT_PRICE', Tools::getValue('tp_force_unit_price'));

            $selected_categories = Tools::getValue('categoryBox');
            Configuration::updateValue('TP_CATEGORIES', serialize($selected_categories));

            $message .= $this->displayConfirmation($this->l('Configuration updated succesfully'));
        }
        else if(Tools::isSubmit('submitExportConfiguration'))
        {
            Configuration::updateValue('TP_EXPORT_TYPE', Tools::getValue('tp_export_type'));
            Configuration::updateValue('TP_TRUSTED_PROGRAM_ENABLED', Tools::getValue('tp_trusted_program_enabled'));
            Configuration::updateValue('TP_MERCHANT_KEY', Tools::getValue('tp_merchant_key'));
            Configuration::updateValue('TP_CARRIER', Tools::getValue('tp_carrier'));
            Configuration::updateValue('TP_PRODUCT_VARIANTS_ENABLED', Tools::getValue('tp_product_variants_enabled'));
            Configuration::updateValue('TP_FORCE_UNIT_PRICE', Tools::getValue('tp_force_unit_price'));

            $selected_categories = Tools::getValue('categoryBox');
            Configuration::updateValue('TP_CATEGORIES', serialize($selected_categories));

            $message .= $this->displayConfirmation($this->l('Configuration updated succesfully'));
            $this->export();
        }

        $export_types = array(
            array('value' => 0, 'name' => 'txt'),
            //array('value' => 1, 'name' => 'csv'),
            //array('value' => 2, 'name' => 'tsv'),
            //array('value' => 3, 'name' => 'xml'),
        );

        // Recupero il paese per il quale effettuare l'esportazione -> al momento è solo l'Italia
        $available_country = Country::getByIso('IT');
        $available_zone = Country::getIdZone($available_country);

        $carriers = Carrier::getCarriers($this->context->language->id, true, false, $available_zone);
        $available_carriers = [];

        foreach($carriers as $carrier) {
            array_push($available_carriers, array('id_carrier' => $carrier['id_carrier'], 'carrier_name' => $carrier['name']));
        }

        // Recupero le categorie dell'esportazione
        $categories = Tools::unSerialize(Configuration::get('TP_CATEGORIES'));

        $tree_categories_helper = new HelperTreeCategories('trovaprezzi');
        $tree_categories_helper
            ->setRootCategory((Shop::getContext() == Shop::CONTEXT_SHOP ? Category::getRootCategory()->id_category : 0))
            ->setUseCheckBox(true);

        if($categories != '') {
            $tree_categories_helper->setSelectedCategories($categories);
        }

        $this->context->smarty->assign(array(
            'message' => $message,
            'action' => AdminController::$currentIndex.'&configure='.$this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'export_types' => $export_types,
            'available_carriers' => $available_carriers,
            'tp_export_type' => Configuration::get('TP_EXPORT_TYPE'),
            'tp_trusted_program_enabled' => Configuration::get('TP_TRUSTED_PROGRAM_ENABLED'),
            'tp_merchant_key' => Configuration::get('TP_MERCHANT_KEY'),
            'tp_carrier' => Configuration::get('TP_CARRIER'),
            'tp_product_variants_enabled' => Configuration::get('TP_PRODUCT_VARIANTS_ENABLED'),
            'tp_force_unit_price' => Configuration::get('TP_FORCE_UNIT_PRICE'),
            'tp_categories_tree' => $tree_categories_helper->render()
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/configuration.tpl');
    }

    /**
     * Funzione che mostra un messaggio di errore
     */
    private function displayInfo($string) {
        $output = '
        <div class="bootstrap">
        <div class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            '.$string.'
        </div>
        </div>';
        return $output;
    }

    /**
     * Funzione che effettua l'esportazione a seconda del tipo scelto
     */
    public function export() {
        $export_type = Configuration::get('TP_EXPORT_TYPE');

        if($export_type == 0) { //txt export
            return $this->exportTxt();
        }
        else {
            return false;
        }
    }

    /**
     * Funzione che esporta il file in formato txt
     */
    private function exportTxt() {
        // 1. Costruisco il file .txt
        $export_file_path = dirname(__FILE__).'/../../trovaprezzi_export.txt';

        $endrecord = '<endrecord>';
        $newline = PHP_EOL;
        $separator = '|';
        $txt = 'Nome|Marca|Descrizione|Prezzo Vendita|Codice Interno|Link all’offerta|Disponibilità|Albero Categorie|Link Immagine|Spese di Spedizione|Codice Produttore|Codice EAN|Peso|Ulteriore Link Immagine 1| Ulteriore Link Immagine 2' . $endrecord . $newline;

        // 2. Estraggo i valori
        $sql = new DbQuery();
        $sql->select('
            p.id_product,
            pl.name,
            p.id_category_default,
            m.name as manufacturer,
            pl.description,
            p.reference,
            p.advanced_stock_management,
            p.quantity,
            pl.link_rewrite,
            p.quantity,
            0 as shipping_cost,
            p.supplier_reference as supplier_reference,
            p.ean13,
            p.weight,
            i.id_image,
            p.unit_price_ratio');
        $sql->from('product', 'p');
        $sql->innerJoin('product_lang', 'pl', 'p.id_product = pl.id_product');
        $sql->innerJoin('category_product', 'cp', 'p.id_product = cp.id_product');
        $sql->leftOuterJoin('supplier', 's', 'p.id_supplier = s.id_supplier');
        $sql->leftOuterJoin('manufacturer', 'm', 'p.id_manufacturer = m.id_manufacturer');
        
        // 2.1 Ne caso in cui ho abilitato le varianti vado a creare i vari prodotti
        if(Configuration::get('TP_PRODUCT_VARIANTS_ENABLED')) {
            $sql->leftOuterJoin('product_attribute', 'pa', 'p.id_product = pa.id_product');
            
            $sql->leftOuterJoin('product_attribute_image', 'pai', 'pa.id_product_attribute = pai.id_product_attribute');
            $sql->leftOuterJoin('image', 'i', 'p.id_product = i.id_product AND IF(pai.id_image is null, i.cover = 1, i.id_image = pai.id_image)');

            $sql->leftOuterJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute');
            $sql->leftOuterJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute');
            $sql->leftOuterJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute');
            $sql->leftOuterJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group');
            $sql->leftOuterJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group');
            $sql->groupBy('p.id_product, IFNULL(pa.id_product_attribute, 0), i.id_image');

            $sql->select("IF(pa.reference = '' or pa.reference is null, CONCAT(p.id_product, '-', pa.id_product_attribute), pa.reference) as combination_reference");
            $sql->select('IFNULL(pa.id_product_attribute, 0) as id_product_attribute');
            $sql->select('GROUP_CONCAT(DISTINCT(al.name) SEPARATOR \' - \') as combination');                 
        } else {
            $sql->leftOuterJoin('image', 'i', 'p.id_product = i.id_product AND i.cover = 1');
        }

        $sql->where('p.reference <> \'\'');
        $sql->where('p.active = 1');
        $sql->where('cp.id_category IN (' . implode(',', Tools::unSerialize(Configuration::get('TP_CATEGORIES'))) . ')');

        $products = Db::getInstance()->executeS($sql);

        $export_file = fopen($export_file_path, 'w');
        fwrite($export_file, $txt);

        // 3. Costruisco le righe del prodotto e le inserisco nel file
        foreach($products as $product)
        {
            $product['description'] = str_replace(PHP_EOL, '', Tools::getDescriptionClean($product['description']));
            $product['image_url'] = $this->context->link->getImageLink($product['link_rewrite'], $product['id_image']);
            $product_price = Product::getPriceStatic($product['id_product'], true, (isset($product['id_product_attribute']) && $product['id_product_attribute'] != 0 ? $product['id_product_attribute'] : null));

            if($product['unit_price_ratio'] > 0 && Configuration::get('TP_FORCE_UNIT_PRICE')) {
                $product_price = round($product_price / $product['unit_price_ratio'], 2);
                $product['description'] = 'Prezzo unitario; ' . $product['description'] . '; Venduto in confezioni da ' . round($product['unit_price_ratio']);
            }

            $reference = Configuration::get('TP_PRODUCT_VARIANTS_ENABLED') && $product['combination_reference'] != null ? $product['combination_reference'] : $product['reference'];

            if($product['advanced_stock_management']) {
                $product['avaibility'] = $product['quantity'];
            }
            else {
                $product['avaibility'] = 2;
            };

            $currentCategory = new Category($product['id_category_default']);
            $parents = $currentCategory->getParentsCategories();
            $product['category_tree'] = implode(',', array_column(array_reverse($parents), 'name'));

            
            $row = $product['name'] . (isset($product['combination']) ? ' - ' . $product['combination'] : '') . $separator .
                $product['manufacturer'] . $separator .
                $product['description'] . $separator .
                $product_price . $separator .
                $reference . $separator .
                $this->context->link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute']) . $separator .
                $product['avaibility'] . $separator .
                $product['category_tree'] . $separator .
                $product['image_url'] . $separator .
                $product['shipping_cost'] . $separator .
                $product['supplier_reference'] . $separator .
                $product['ean13'] . $separator .
                $product['weight'] . $separator .
                '' . $separator .
                '' . $separator .
                $endrecord . $newline;

            fwrite($export_file, $row);
        }

        // 4. Popolo il file con i contenuti
        fclose($export_file);

        return true;
    }

    /**
     *  Hook eseguito al completamento dell'oridne
     *  nella pagina di conferma ordine
     */
    public function hookDisplayOrderConfirmation($params) {
        if(Configuration::get('TP_TRUSTED_PROGRAM_ENABLED')) {

            // Carico il javascript esterno per l'invio a trovaprezzi
            $this->context->controller->registerJavascript('remote-bootstrap', 'https://tracking.trovaprezzi.it/javascripts/tracking.min.js', ['server' => 'remote']);

            // Recupero i parametri per la creazione dello script di trovaprezzi
            $order = $params['order'];
            $products = $order->getProducts();

            $this->context->smarty->assign(array(
                'tp_merchant_key' => Configuration::get('TP_MERCHANT_KEY'),
                'tp_email' => $this->context->customer->email,
                'tp_orderid' => $order->id,
                'tp_products' => $products,
                'tp_total_paid' => $order->total_paid
            ));

            return $this->display(__FILE__, 'trusted_program.tpl');
        }

    }
}