<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\TenancyServiceProvider;
use Modules\POS\app\Providers\POSServiceProvider;
use Modules\Inventory\app\Providers\InventoryServiceProvider;
use Modules\Finance\app\Providers\FinanceServiceProvider;
use Modules\Reporting\app\Providers\ReportingServiceProvider;
use Modules\Auth\app\Providers\AuthServiceProvider;
use Modules\Tenant\app\Providers\TenantModuleServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    TenancyServiceProvider::class,

    // Module service providers
    AuthServiceProvider::class,
    TenantModuleServiceProvider::class,
    POSServiceProvider::class,
    InventoryServiceProvider::class,
    FinanceServiceProvider::class,
    ReportingServiceProvider::class,
];
