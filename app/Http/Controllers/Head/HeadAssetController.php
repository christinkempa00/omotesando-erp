<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\GA\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Monitoring aset untuk Head — read-only (reuse model Asset & AssetController's
 * query filters), tidak ada aksi tambah/edit/hapus/QR seperti di sisi GA.
 */
class HeadAssetController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->rememberFilters($request, 'head.assets.index', ['status', 'search', 'branch_id', 'branch_location_id', 'page', 'per_page'])) {
            return $redirect;
        }

        $query = Asset::with(['branch'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($branchId = $request->query('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($branchLocationId = $request->query('branch_location_id')) {
            $query->where('branch_location_id', $branchLocationId);
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $assets = $query->paginate($perPage)->withQueryString();

        return view('head.assets.index', [
            'assets' => $assets,
            'statusLabels' => Asset::statusLabels(),
            'branches' => Branch::orderedOutlets(),
            'selectedStatus' => $status,
            'selectedBranch' => $branchId,
            'branchLocations' => BranchLocation::groupedByBranch(),
            'selectedBranchLocation' => $branchLocationId,
            'search' => $search,
        ]);
    }
}
