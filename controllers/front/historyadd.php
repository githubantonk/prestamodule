<?php

class mymodulehistoryaddModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        Db::getInstance()->execute('
            INSERT INTO `'._DB_PREFIX_.'converter_history` (`datetime_`,`name`) VALUES (\'' . date('Y-m-d h:i:s') . '\',\'' . Tools::getValue('num') .' ' . Tools::getValue('from_') . ' to ' . Tools::getValue('to_') . '\')
            ');

        $res = '';
        $arr = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'converter_history` ORDER BY datetime_ DESC LIMIT 10');
        foreach ($arr as $val) {
            $res .= '<p>' . $val['datetime_'] . '<span>' . $val['name'] . '</span></p>';
        }
        if (!$res) $res = 'Not available';
        echo $res;        
    }
}	