<?php

namespace NickMous\MyOwnFramework\Controllers;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class BaseController
{
    public function getBeanById(string $typeOfBean, int|string $queryStringKey): ?OODBBean
    {
        $bean = R::findOne($typeOfBean, 'old_id = ?', [$queryStringKey]);

        if (!$bean) {
            $bean = R::findOne($typeOfBean, 'id = ?', [$queryStringKey]);
        }

        return $bean;
    }
}
