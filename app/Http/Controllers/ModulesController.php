<?php

namespace App\Http\Controllers;

class ModulesController extends Controller
{
    // module list
    public function module_list()
    {
        $pageConfigs = ['pageHeader' => false];

        return view('/content/modules/modules/modules', ['pageConfigs' => $pageConfigs]);
    }

    // module list
    public function features_list()
    {
        $pageConfigs = ['pageHeader' => false];

        return view('/content/modules/features/features', ['pageConfigs' => $pageConfigs]);
    }

    // Ecommerce Checkout
    public function ecommerce_checkout()
    {
        $pageConfigs = [
            'pageClass' => 'ecommerce-application',
        ];

        $breadcrumbs = [
            ['link' => '/', 'name' => 'Home'], ['link' => 'javascript:void(0)', 'name' => 'eCommerce'], ['name' => 'Checkout'],
        ];

        return view('/content/apps/ecommerce/app-ecommerce-checkout', [
            'pageConfigs' => $pageConfigs,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
