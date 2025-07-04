<?php

namespace Nickmous\MyOwnFramework\Controllers;

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

    public function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public function checkUserLoggedIn(): void
    {
        if (!$this->isUserLoggedIn()) {
            redirect(path('user.login'));
        }
    }
}
