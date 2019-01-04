<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class MyModule extends Module
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Kostenko Anton';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cryptocurrency Converter');
        $this->description = $this->l('Cryptocurrency Converter Calculator');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_API_KEY')) {
            $this->warning = $this->l('No name provided');
        }
    }

	public function install()
	{
	    if (Shop::isFeatureActive()) {
	        Shop::setContext(Shop::CONTEXT_ALL);
	    }

	    if (!parent::install() ||
	        !$this->registerHook('header') ||
	        !$this->registerHook('displayHome') ||
	        !Configuration::updateValue('MYMODULE_API_KEY', 'X-CMC_PRO_API_KEY') ||
	        !Configuration::updateValue('MYMODULE_UPDATE_TIME', 1545835103) ||	        
			!Db::getInstance()->execute(
				'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'converter_val` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`symbol` varchar(5) NOT NULL,
					`price` double NOT NULL,
					UNIQUE KEY (`id`)
					) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;') ||
			!Db::getInstance()->execute('
				CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'converter_history` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`datetime_` datetime NOT NULL,
					`name` varchar(255) NOT NULL,
					PRIMARY KEY (`id`),
					INDEX (`datetime_`)
					) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;')
	    ) {
	        return false;
	    }

	    return true;
	}    

	public function uninstall()
	{
	    if (!parent::uninstall() ||
	        !Configuration::deleteByName('MYMODULE_API_KEY') ||
	        !Configuration::deleteByName('MYMODULE_UPDATE_TIME') ||	        
	        !Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'converter_history`') ||
	        !Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'converter_val`')
	    ) {
	        return false;
	    }

	    return true;
	}

	// front
	public function hookDisplayHeader()
	{
	    $this->context->controller->addCSS($this->_path.'css/mymodule.css', 'all');
	    $this->context->controller->addJS($this->_path.'js/script.js', 'all');
	}


	public function hookDisplayHome($params)
	{
		
		function UpdateCryptocurrencyValFromAPI(){
			$status = '';
			$ch = curl_init('');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
			curl_setopt($ch, CURLOPT_URL, 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-CMC_PRO_API_KEY: ' . Configuration::get('MYMODULE_API_KEY')));

			$ret = json_decode(curl_exec($ch), true);
			curl_close($ch);
			if (!$ret['status']['error_code']) {
				$upd = '';
				foreach ($ret['data'] as $key => $value) {
					if ($upd) $upd .= ',';
					$upd .= "('".$value['id']."', '".$value['name']."', '".$value['symbol']."', '".$value['quote']['USD']['price']."')";
				}
				if ($upd) {
					Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'converter_val`');
					Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'converter_val` (`id`, `name`, `symbol`, `price`) VALUES ' . $upd);
					Configuration::updateValue('MYMODULE_UPDATE_TIME', time());
				}
			} 
			else $status = 'Error: '.$ret['status']['error_message'];
			return $status;
		}

		$error_in = 'tt';
		$list_all = array();

		if ((time() - Configuration::get('MYMODULE_UPDATE_TIME')) / 60 > 5) $error_in = UpdateCryptocurrencyValFromAPI();
		$list_all = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'converter_val`');

	    $this->context->smarty->assign([
	        'list_all'	=> $list_all,
	        'time_upd'	=> date('Y:m:d H:i', Configuration::get('MYMODULE_UPDATE_TIME')),
	        'error' => $error_in
	      ]);

	      return $this->display(__FILE__, 'home.tpl');
	}


	// Admin back 
	public function getContent()
	{
	    $output = null;

	    if (Tools::isSubmit('submit'.$this->name)) {
	        $myModuleName = strval(Tools::getValue('MYMODULE_API_KEY'));

	        if (
	            !$myModuleName ||
	            empty($myModuleName) ||
	            !Validate::isGenericName($myModuleName)
	        ) {
	            $output .= $this->displayError($this->l('Invalid X-CMC_PRO_API_KEY'));
	        } else {
	            Configuration::updateValue('MYMODULE_API_KEY', $myModuleName);
	            $output .= $this->displayConfirmation($this->l('Settings updated'));
	        }
	    }

	    return $output.$this->displayForm();
	}

	public function displayForm()
	{
	    // Get default language
	    $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

	    // Init Fields form array
	    $fieldsForm[0]['form'] = [
	        'legend' => [
	            'title' => $this->l('Settings (last time used api: '. date('Y-m-d h:i', Configuration::get('MYMODULE_UPDATE_TIME')).')'),
	        ],
	        'input' => [
	            [
	                'type' => 'text',
	                'label' => $this->l('X-CMC_PRO_API_KEY'),
	                'name' => 'MYMODULE_API_KEY',
	                'size' => 20,
	                'required' => true
	            ]
	        ],
	        'submit' => [
	            'title' => $this->l('Save'),
	            'class' => 'btn btn-default pull-right'
	        ]
	    ];

	    $helper = new HelperForm();

	    // Module, token and currentIndex
	    $helper->module = $this;
	    $helper->name_controller = $this->name;
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

	    // Language
	    $helper->default_form_language = $defaultLang;
	    $helper->allow_employee_form_lang = $defaultLang;

	    // Title and toolbar
	    $helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = [
	        'save' => [
	            'desc' => $this->l('Save'),
	            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	            '&token='.Tools::getAdminTokenLite('AdminModules'),
	        ],
	        'back' => [
	            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	            'desc' => $this->l('Back to list')
	        ]
	    ];

	    // Load current value
	    $helper->fields_value['MYMODULE_API_KEY'] = Configuration::get('MYMODULE_API_KEY');

	    return $helper->generateForm($fieldsForm);
	}		


}
