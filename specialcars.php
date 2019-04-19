<?php

use PrestaShopBundle\Form\Admin\Type\TranslateType;
use PrestaShopBundle\Form\Admin\Type\FormattedTextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SpecialCars extends Module
{
    public function __construct()
    {
        $this->name = 'specialcars';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Pierre Etheve';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Special Car Product Page');
        $this->description = $this->l('Module aimed at streamlining product entry for car-related purposes.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }

        $this->context->controller->addJS($this->_path.'script.js');
    }

    public function install()
	{
        if (!parent::install() OR       
            !$this->registerHook('displayAdminProductsExtra') OR
            !$this->registerHook('displayAdminProductsMainStepLeftColumnMiddle') OR
            !$this->registerHook('actionAdminProductsControllerSaveBefore'))
            return false;
        else
            return true;
	}

	public function uninstall()
	{
	    return parent::uninstall();
	}

    private function logObject($label, $object) {
        $array_txt = var_export($object, TRUE);
        error_log($label." : ".$array_txt);
    }

    public function generateData($product_id) {
        try{
            error_log("Generating data");
            $query = "SELECT * FROM ps_product WHERE `id_product`=".$product_id;
            $data = Db::getInstance()->executeS($query);
            $this->logObject("SQL data", $data);

            $query2 = "SELECT * FROM ps_model_hierarchy WHERE `id`=1";
            $data2 = Db::getInstance()->executeS($query2);

            $model_hierarchy = $data2[0]['full_hierarchy'];
            error_log('Model Hierarchy : '.$model_hierarchy);

            $car_model = $data[0]['car_model'];
            $car_type = $data[0]['car_type'];
            $car_version = $data[0]['car_version'];
            $car_number = $data[0]['car_number'];
            $car_color_code = $data[0]['car_color_code'];
            $car_option_code = $data[0]['car_option_code'];
            $product_id = $_REQUEST['form']['id_product'];
            error_log($car_model);
            $content = "<script>var custom_field_data={'car_model':'".$car_model."', 'car_type':'".$car_type."', 'car_version':'".$car_version."', 'car_number':'".$car_number."', 'car_color_code':'".$car_color_code."', 'car_option_code':'".$car_option_code."', 'model_hierarchy' : '".$model_hierarchy."'};</script>";
            return $content;
        } 
        catch (Exception $e){
            error_log("Error caught in 'generateData'");
            error_log($e->getMessage());
        }

    }

    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params){
        try{
            error_log("Hook 'displayAdminProductsMainStepLeftColumnMiddle' running");
            $content = $this->display(__FILE__, 'newfieldstut.tpl');
            $id_product = $params['id_product'];
            $content = $content.$this->generateData($id_product);
            // $content = "";
            return $content;
        } catch (Exception $e) {
            error_log("Error caught in 'hookDisplayAdminProductsMainStepLeftColumnMiddle'");
            error_log($e->getMessage());
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        try{
            error_log("'hookDisplayAdminProductsExtra' running !");
            $productAdapter = $this->get('prestashop.adapter.data_provider.product');
            $product = $productAdapter->getProduct($params['id_product']);
            $formData = [
               'car_model' => $product->car_model,
               'car_type' => $product->car_type,
            ];
            $formFactory = $this->get('form.factory');
            $form = $formFactory->createBuilder(FormType::class, $formData)
                ->add('car_model', TranslateType::class, array(
                    'label' => 'Car model',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))
                ->add('car_type', TranslateType::class, array(
                    'label' => 'Car type',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))
                ->add('car_version', TranslateType::class, array(
                    'label' => 'Car version',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))
                ->add('car_number', TranslateType::class, array(
                    'label' => 'Serial number',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))
                ->add('car_option_code', TranslateType::class, array(
                    'label' => 'Option code',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))
                ->add('car_color_code', TranslateType::class, array(
                    'label' => 'Color code',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))
                ->add('model_hierarchy', TranslateType::class, array(
                    'label' => 'Hierarchy',
                    'locales' => Language::getLanguages(),
                    'hideTabs' => true,
                    'required' => false
                ))

            ->getForm()
            ;
            return $this->get('twig')->render(_PS_MODULE_DIR_.'specialcars/views/display-admin-products-extra.html.twig', [
                'form' => $form->createView()
            ]) ;
        }   

        catch(Exception $e){
            error_log("Error caught in 'hookDisplayAdminProductsExtra'");
            error_log($e->getMessage());
        }

    }

    public function hookActionAdminProductsControllerSaveBefore($params)
    {
        try{
            $productAdapter = $this->get('prestashop.adapter.data_provider.product');
            $product = $productAdapter->getProduct($_REQUEST['form']['id_product']);

            $car_model = $_REQUEST['form']['car_model'][1];
            $car_type = $_REQUEST['form']['car_type'][1];
            $car_version = $_REQUEST['form']['car_version'][1];
            $car_number = $_REQUEST['form']['car_number'][1];
            $car_color_code = $_REQUEST['form']['car_color_code'][1];
            $car_option_code = $_REQUEST['form']['car_option_code'][1];
            $product_id = $_REQUEST['form']['id_product'];
            $model_hierarchy = $_REQUEST['form']['model_hierarchy'][1];

            error_log("Model Hierarchy : ".$model_hierarchy);

            $sql_query = "UPDATE `ps_product` SET `car_model`='".$car_model."',`car_type`='".$car_type."',`car_version`='".$car_version."',`car_number`='".$car_number."',`car_option_code`='".$car_option_code."',`car_color_code`='".$car_color_code."' WHERE  `id_product`=".$product_id;
            error_log("QUERY : ".$sql_query);
            Db::getInstance()->execute($sql_query);

            $sql_query_2 = "UPDATE `ps_model_hierarchy` SET `full_hierarchy`='".$model_hierarchy."' WHERE  `id`= 1";
            error_log("QUERY : ".$sql_query_2);
            Db::getInstance()->execute($sql_query_2);

        } 
        catch (Exception $e){
            error_log("Error caught in 'hookActionAdminProductsControllerSaveBefore'");
            error_log($e->getMessage());
        }

    } 
}