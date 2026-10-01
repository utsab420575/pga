<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Applicant;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    const TABLES = ['applicants', 'user_attachments', 'basic_infos', 'education_infos', 'eligibility_degrees', 'job_experiences'];

    public function index(Request $request)
    {
        $filters = $request->only(['applicant', 'table', 'event', 'user', 'from', 'to']);

        // "applicant" accepts an applicant id or roll
        $applicantId = null;
        if (!empty($filters['applicant'])) {
            $applicantId = Applicant::where('id', $filters['applicant'])
                ->orWhere('roll', $filters['applicant'])
                ->value('id') ?? 0;
        }

        $items = ActivityLog::with('causer')
            ->when($applicantId !== null, fn ($q) => $q->where('applicant_id', $applicantId))
            ->when(!empty($filters['table']), fn ($q) => $q->where('log_name', $filters['table']))
            ->when(!empty($filters['event']), fn ($q) => $q->where('event', $filters['event']))
            ->when(!empty($filters['user']), fn ($q) => $q->where('causer_id', $filters['user']))
            ->when(!empty($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.settings.activity_logs.index', [
            'items'   => $items,
            'filters' => $filters,
            'tables'  => self::TABLES,
        ]);
    }
}
