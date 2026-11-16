<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const FILTERS = ['open', 'reviewed', 'closed', 'all'];

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = is_string($status) && in_array($status, self::FILTERS, true) ? $status : 'open';

        $reports = Report::query()
            ->with(['book:id,title,status', 'user:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = Report::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reports.index', compact('reports', 'status', 'counts'));
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:reviewed,closed'],
        ]);

        $report->status = $data['status'];
        $report->save();

        return back()->with('status', 'Status laporan diperbarui.');
    }
}
