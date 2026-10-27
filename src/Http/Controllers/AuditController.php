<?php

namespace Devpartners\AuditableLog\Http\Controllers;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Config;

class AuditController
{
    public function show(Request $request, $resourceName, $resourceId)
    {
        $resource = $this->loadResource($resourceName, $resourceId);
        $record = $resource->resource;
        $user = $request->user(config('nova.guard'));

        abort_unless($resource->authorizedToView($request) && $user->can('audit', $record), 403, 'Unable to retrieve audits');

        $audits = $record->audits()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json(['status' => 'OK', 'audits' => $audits, 'restore' => $this->canRestore($request, $resource)]);
    }

    public function restore(Request $request, $resourceName, $resourceId, $auditId)
    {
        $resource = $this->loadResource($resourceName, $resourceId);
        $record = $resource->resource;

        abort_unless($this->canRestore($request, $resource), 403, 'Unable to restore audits');

        /**
         * @var Audit $auditor
         * @var Audit $audit
         */
        $auditableClass = Config::get('audit.implementation', Audit::class);
        $auditor = new $auditableClass();

        $audit = $record->audits()->where($auditor->getTable() . '.' . $auditor->getKeyName(), $auditId)->firstOrFail();

        $record->forceFill(Arr::only($audit->old_values, $request->input('restore', [])));
        $record->save();

        return response()->json(['status' => 'OK', 'record' => $record]);
    }

    protected function canRestore(Request $request, Resource $resource): bool
    {
        return $resource->authorizedToUpdate($request)
            && $request->user(config('nova.guard'))->can('audit_restore', $resource->resource);
    }

    /**
     * @param $resourceName
     * @param $resourceId
     *
     * @return Resource
     */
    protected function loadResource($resourceName, $resourceId)
    {
        $resourceClass = Nova::resourceForKey($resourceName);
        abort_if($resourceClass === null, 404);

        $model = $resourceClass::newModel();
        $record = method_exists($model, "trashed")
            ? $model::withTrashed()->findOrFail($resourceId)
            : $model->findOrFail($resourceId);

        return new $resourceClass($record);
    }
}
