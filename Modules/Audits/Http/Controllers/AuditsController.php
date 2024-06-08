<?php

namespace Modules\Audits\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Audits\Entities\Audits;
use Modules\Audits\Entities\AuthLogs;
use Modules\Audits\Exports\AuditsExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AuditsController extends Controller
{
    public function audits_export(Request $request): BinaryFileResponse
    {
        return (new AuditsExport)->from($request->start)->end($request->end)->download('audit-exports.xlsx');
    }

    public function auth_data(Request $request)
    {
        $data['users'] = User::admins()->get();

        return response()->json($data);
    }

    public function audits_data(Request $request)
    {
        $data['users'] = User::get();

        return response()->json($data);
    }

    public function audits(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $query = Audits::latest();
            $order = $query->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function auth_logs(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $query = AuthLogs::where('authenticatable_id', $request->user)->latest("id");
            if (! empty($request->client)) {
                $query = $query->where('login_at', $request->client);
            }
            if (! empty($request->value)) {
                $query = $query->where('id', $request->value);
            }

            $order = $query->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }


    public function getAuditsByAuditable(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $query = Audits::where('auditable_id', $request->auditable_id)->orderBy("id","desc");

            $order = $query->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
