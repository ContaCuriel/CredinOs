<?php

namespace App\TenantFinders;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SessionTenantFinder extends TenantFinder
{
    /**
     * Tries to find a tenant based on the 'tenant_id' in the session.
     */
    public function findForRequest(Request $request): ?Tenant
    {
        $tenantId = $request->session()->get('tenant_id');

        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        return null;
    }
}
