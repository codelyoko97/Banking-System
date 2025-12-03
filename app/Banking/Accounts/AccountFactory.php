<?php

namespace App\Banking\Accounts;

use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;

class AccountFactory
{
    public static function buildTree(Account $model, AccountRepositoryInterface $repo): AccountComponent
    {
        if ($model->children->count() === 0) {
            return new AccountLeaf($model, $repo);
        }

        $composite = new AccountComposite($model->id);

        foreach ($model->children as $childModel) {
            $childComponent = self::buildTree($childModel, $repo);
            $composite->addChild($childComponent);
        }

        return $composite;
    }
}
