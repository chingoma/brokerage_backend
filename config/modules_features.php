<?php

return [

    'modules' => [
        'Securities' => [
            'description' => 'Securities',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'Securities' => [
                    'description' => 'Securities',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Securities',
                        ],
                        'read' => [
                            'description' => 'ability to view Securities list and Security details',
                        ],
                        'update' => [
                            'description' => 'ability to update Securities',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Securities',
                        ],
                    ],
                ],
            ],
        ],
        'Bonds' => [
            'description' => 'Bonds',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'Bonds' => [
                    'description' => 'Bonds',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Bonds',
                        ],
                        'read' => [
                            'description' => 'ability to view Bonds list and Bond details',
                        ],
                        'update' => [
                            'description' => 'ability to update Bonds',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Bonds',
                        ],
                    ],
                ],
            ],
        ],
        'Human Resource' => [
            'description' => 'Human Resource',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'employees' => [
                    'description' => 'employees',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create employees',
                        ],
                        'read' => [
                            'description' => 'ability to view employees list and employee details',
                        ],
                        'update' => [
                            'description' => 'ability to update employees',
                        ],
                        'delete' => [
                            'description' => 'ability to delete employees',
                        ],
                        'approve' => [
                            'description' => 'ability to approve employees',
                        ],
                        'suspend' => [
                            'description' => 'ability to suspend employees',
                        ],
                    ],
                ],
                'departments' => [
                    'description' => 'departments',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create departments',
                        ],
                        'read' => [
                            'description' => 'ability to view departments list and department details',
                        ],
                        'update' => [
                            'description' => 'ability to update departments',
                        ],
                        'delete' => [
                            'description' => 'ability to delete departments',
                        ],
                    ],
                ],
            ],
        ],
        'Orders' => [
            'description' => 'Orders',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'orders' => [
                    'description' => 'orders',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create orders',
                        ],
                        'read' => [
                            'description' => 'ability to view orders list and order details',
                        ],
                        'update' => [
                            'description' => 'ability to update orders',
                        ],
                        'delete' => [
                            'description' => 'ability to delete orders',
                        ],
                        'approve' => [
                            'description' => 'ability to approve orders. Note, When order is approved becomes dealing sheet',
                        ],
                    ],
                ],
            ],
        ],
        'Dealing Sheets' => [
            'description' => 'Dealing Sheets',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'Dealing Sheets' => [
                    'description' => 'Dealing Sheets',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Dealing Sheets',
                        ],
                        'read' => [
                            'description' => 'ability to view Dealing Sheets list and Dealing Sheet details',
                        ],
                        'update' => [
                            'description' => 'ability to update Dealing Sheets',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Dealing Sheets',
                        ],
                        'approve' => [
                            'description' => 'ability to approve orders. Note, When Dealing Sheet is approved transactions are sent to General Ledger',
                        ],
                    ],
                ],
            ],
        ],
        'CRM' => [
            'description' => 'customer relationship manager',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'customers' => [
                    'description' => 'customers',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create customer',
                        ],
                        'read' => [
                            'description' => 'ability to view customers list and customer profile',
                        ],
                        'update' => [
                            'description' => 'ability to update customer',
                        ],
                        'delete' => [
                            'description' => 'ability to delete customer',
                        ],
                        'approve' => [
                            'description' => 'ability to approve customer',
                        ],
                        'suspend' => [
                            'description' => 'ability to suspend customer',
                        ],
                    ],
                ],
                'customer categories' => [
                    'description' => 'customer categories',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create customer categories',
                        ],
                        'read' => [
                            'description' => 'ability to view customer categories',
                        ],
                        'update' => [
                            'description' => 'ability to update customer categories',
                        ],
                        'delete' => [
                            'description' => 'ability to delete customer categories',
                        ],
                    ],
                ],
                'news letter' => [
                    'description' => 'news letter',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create news letter',
                        ],
                        'read' => [
                            'description' => 'ability to view news letter',
                        ],
                        'update' => [
                            'description' => 'ability to update news letter',
                        ],
                        'delete' => [
                            'description' => 'ability to delete news letter',
                        ],
                    ],
                ],
                'news letter categories' => [
                    'description' => 'news letter categories',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create news letter categories',
                        ],
                        'read' => [
                            'description' => 'ability to view news letter categories',
                        ],
                        'update' => [
                            'description' => 'ability to update news letter categories',
                        ],
                        'delete' => [
                            'description' => 'ability to delete news letter categories',
                        ],
                    ],
                ],
            ],
        ],
        'Accounting' => [
            'description' => 'Accounting',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'General Ledger' => [
                    'description' => 'General Ledger',
                    'permissions' => [
                        'read' => [
                            'description' => 'ability to view General Ledger',
                        ],
                    ],
                ],
                'Trial Balance' => [
                    'description' => 'Trial Balance',
                    'permissions' => [
                        'read' => [
                            'description' => 'ability to view Trial Balance',
                        ],
                    ],
                ],
                'Balance Sheet' => [
                    'description' => 'Balance Sheet',
                    'permissions' => [
                        'read' => [
                            'description' => 'ability to view Balance Sheet',
                        ],
                    ],
                ],
                'Income Statement' => [
                    'description' => 'Income Statement',
                    'permissions' => [
                        'read' => [
                            'description' => 'ability to view Income Statement',
                        ],
                    ],
                ],
                'Account Settings' => [
                    'description' => 'Account Settings',
                    'permissions' => [
                        'read' => [
                            'description' => 'ability to alter account settings',
                        ],
                    ],
                ],
                'Chat of Accounts' => [
                    'description' => 'Chat of Accounts',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Chat of Accounts',
                        ],
                        'read' => [
                            'description' => 'ability to view Account list and account details',
                        ],
                        'update' => [
                            'description' => 'ability to update Accounts',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Accounts',
                        ],
                    ],
                ],
                'Receipts' => [
                    'description' => 'Receipts',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Receipt',
                        ],
                        'read' => [
                            'description' => 'ability to view Receipt list and Receipt details',
                        ],
                        'update' => [
                            'description' => 'ability to update Receipt',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Receipt',
                        ],
                        'approve' => [
                            'description' => 'ability to approve Receipt',
                        ],
                    ],
                ],
                'Vouchers' => [
                    'description' => 'Vouchers',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Vouchers',
                        ],
                        'read' => [
                            'description' => 'ability to view Vouchers list and Voucher details',
                        ],
                        'update' => [
                            'description' => 'ability to update Vouchers',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Vouchers',
                        ],
                        'approve' => [
                            'description' => 'ability to approve Vouchers',
                        ],
                    ],
                ],
                'Journal' => [
                    'description' => 'Journal',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Journal',
                        ],
                        'read' => [
                            'description' => 'ability to view Journal list and Journal details',
                        ],
                        'update' => [
                            'description' => 'ability to update Journal',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Journal',
                        ],
                        'approve' => [
                            'description' => 'ability to approve Journal',
                        ],
                    ],
                ],
                'Payment' => [
                    'description' => 'Payment',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Payment',
                        ],
                        'read' => [
                            'description' => 'ability to view Payment list and Payment details',
                        ],
                        'update' => [
                            'description' => 'ability to update Payment',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Payment',
                        ],
                        'approve' => [
                            'description' => 'ability to approve Payment',
                        ],
                    ],
                ],
                'Reconciliation' => [
                    'description' => 'Reconciliation',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create Reconciliation',
                        ],
                        'read' => [
                            'description' => 'ability to view Reconciliation',
                        ],
                        'update' => [
                            'description' => 'ability to update Reconciliation',
                        ],
                        'delete' => [
                            'description' => 'ability to delete Reconciliation',
                        ],
                        'approve' => [
                            'description' => 'ability to approve Reconciliation',
                        ],
                    ],
                ],
            ],
        ],
        'Business Setup' => [
            'description' => 'Business setup',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'business' => [
                    'description' => 'business',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create business',
                        ],
                        'read' => [
                            'description' => 'ability to view business',
                        ],
                        'update' => [
                            'description' => 'ability to update business',
                        ],
                        'delete' => [
                            'description' => 'ability to delete business',
                        ],
                        'approve' => [
                            'description' => 'ability to approve business',
                        ],
                        'process' => [
                            'description' => 'ability to process business',
                        ],
                        'setup' => [
                            'description' => 'ability to setup business',
                        ],
                    ],
                ],
            ],
        ],
        'Roles Setup' => [
            'description' => 'Roles setup',
            'price_monthly' => '10000',
            'price_annually' => '100000',
            'features' => [
                'roles' => [
                    'description' => 'Roles',
                    'permissions' => [
                        'create' => [
                            'description' => 'ability to create role',
                        ],
                        'read' => [
                            'description' => 'ability to view role',
                        ],
                        'update' => [
                            'description' => 'ability to update role',
                        ],
                        'delete' => [
                            'description' => 'ability to delete role',
                        ],
                        'approve' => [
                            'description' => 'ability to approve role',
                        ],
                        'process' => [
                            'description' => 'ability to process role',
                        ],
                        'setup' => [
                            'description' => 'ability to setup role',
                        ],
                    ],
                ],
            ],
        ],
    ],

];
