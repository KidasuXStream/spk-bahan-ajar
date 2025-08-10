<?php

namespace App\Http\Controllers;

use App\Exports\RankingPerProdiExport;
use App\Exports\SummaryPerProdiExport;
use App\Exports\ProcurementListExport;
use App\Exports\AHPResultsExport;
use App\Exports\RankingAdvancedExport; // Added this import
use App\Exports\PengajuanPerProdiExport;
use App\Exports\RankingPerProdiAdvancedExport;
use App\Exports\RekapDataExport;
use App\Models\PengajuanBahanAjar;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class ExportController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Export ranking per prodi
     */
    public function exportRankingPerProdi(Request $request, $prodiId = null)
    {
        // Validate prodi access
        if ($prodiId && !$this->canAccessProdi($prodiId)) {
            abort(403, 'Anda tidak memiliki akses ke data prodi ini');
        }

        // Get session ID from request if provided
        $sessionId = $request->get('sessionId');

        // Validate AHP session access if sessionId is provided
        if ($sessionId && !$this->canAccessAHPSession($sessionId)) {
            abort(403, 'Anda tidak memiliki akses ke data AHP ini');
        }

        $filename = 'ranking-pengajuan-' . ($prodiId ?: 'semua-prodi');
        if ($sessionId) {
            $session = \App\Models\AhpSession::find($sessionId);
            $filename .= '-session-' . ($session ? $session->tahun_ajaran . '-' . $session->semester : $sessionId);
        }
        $filename .= '-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new RankingPerProdiExport($prodiId, $sessionId), $filename);
    }

    /**
     * Export summary per prodi
     */
    public function exportSummaryPerProdi(Request $request, $sessionId)
    {
        // Validate AHP session access
        if (!$this->canAccessAHPSession($sessionId)) {
            abort(403, 'Anda tidak memiliki akses ke data AHP ini');
        }

        $filename = 'summary-pengajuan-session-' . $sessionId . '-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new SummaryPerProdiExport($sessionId), $filename);
    }

    /**
     * Show export form
     */
    public function showExportForm(Request $request, $sessionId = null)
    {
        // Validate AHP session access if sessionId is provided
        if ($sessionId && !$this->canAccessAHPSession($sessionId)) {
            abort(403, 'Anda tidak memiliki akses ke data AHP ini');
        }

        // Get available sessions for export (prioritize active sessions)
        $sessions = \App\Models\AhpSession::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available prodi for filtering
        $prodis = User::whereHas('roles', function ($query) {
            $query->where('name', 'Kaprodi');
        })
            ->whereNotNull('prodi')
            ->distinct()
            ->pluck('prodi')
            ->map(function ($prodi) {
                return [
                    'id' => $prodi,
                    'nama' => strtoupper($prodi),
                    'full_name' => $this->getProdiFullName($prodi)
                ];
            })
            ->values();

        return view('exports.form', compact('sessions', 'prodis', 'sessionId'));
    }

    /**
     * Get list of available prodi for export
     */
    public function getProdiList()
    {
        $prodis = User::whereHas('roles', function ($query) {
            $query->where('name', 'Kaprodi');
        })
            ->whereNotNull('prodi')
            ->distinct()
            ->pluck('prodi')
            ->map(function ($prodi) {
                return [
                    'id' => $prodi,
                    'nama' => strtoupper($prodi),
                    'full_name' => $this->getProdiFullName($prodi)
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $prodis
        ]);
    }

    /**
     * Export procurement list (shopping list format)
     */
    public function exportProcurementList(Request $request, $prodiId = null)
    {
        // Validate prodi access
        if ($prodiId && !$this->canAccessProdi($prodiId)) {
            abort(403, 'Anda tidak memiliki akses ke data prodi ini');
        }

        $filename = 'daftar-pengadaan-' . ($prodiId ?: 'semua-prodi') . '-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new ProcurementListExport($prodiId), $filename);
    }

    /**
     * Export AHP matrix and results
     */
    public function exportAHPResults(Request $request, $sessionId)
    {
        // Validate AHP session access
        if (!$this->canAccessAHPSession($sessionId)) {
            abort(403, 'Anda tidak memiliki akses ke data AHP ini');
        }

        $filename = 'hasil-ahp-session-' . $sessionId . '-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new AHPResultsExport($sessionId), $filename);
    }

    /**
     * Export ranking with advanced filters
     */
    public function exportRankingAdvanced(Request $request)
    {
        $prodiId = $request->get('prodiId');
        $sessionId = $request->get('sessionId');
        $statusFilter = $request->get('status');
        $urgensiFilter = $request->get('urgensi');

        // Validate access
        if ($prodiId && !$this->canAccessProdi($prodiId)) {
            abort(403, 'Anda tidak memiliki akses ke data prodi ini');
        }

        if ($sessionId && !$this->canAccessAHPSession($sessionId)) {
            abort(403, 'Anda tidak memiliki akses ke data AHP ini');
        }

        $filename = 'ranking-advanced-' . ($prodiId ?: 'semua-prodi');
        if ($sessionId) {
            $session = \App\Models\AhpSession::find($sessionId);
            $filename .= '-session-' . ($session ? $session->tahun_ajaran . '-' . $session->semester : $sessionId);
        }
        $filename .= '-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new RankingAdvancedExport($prodiId, $sessionId, $statusFilter, $urgensiFilter), $filename);
    }

    /**
     * Export pengajuan per prodi (untuk tim pengadaan)
     */
    public function exportPengajuanPerProdi(Request $request)
    {
        // Only Tim Pengadaan and Super Admin can access this
        $user = Auth::user();
        if (!$this->userHasRole($user, 'Tim Pengadaan') && !$this->userHasRole($user, 'super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini');
        }

        $filename = 'pengajuan-per-prodi-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new PengajuanPerProdiExport(), $filename);
    }

    /**
     * Export ranking per prodi dengan AHP (untuk tim pengadaan)
     */
    public function exportRankingPerProdiAdvanced(Request $request)
    {
        // Only Tim Pengadaan and Super Admin can access this
        $user = Auth::user();
        if (!$this->userHasRole($user, 'Tim Pengadaan') && !$this->userHasRole($user, 'super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini');
        }

        $filename = 'ranking-ahp-per-prodi-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new RankingPerProdiAdvancedExport(), $filename);
    }

    /**
     * Export rekap data komprehensif (untuk tim pengadaan)
     */
    public function exportRekapData(Request $request)
    {
        // Only Tim Pengadaan and Super Admin can access this
        $user = Auth::user();
        if (!$this->userHasRole($user, 'Tim Pengadaan') && !$this->userHasRole($user, 'super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini');
        }

        $filename = 'rekap-data-pengadaan-' . date('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new RekapDataExport(), $filename);
    }

    /**
     * Check if user can access specific prodi data
     */
    protected function canAccessProdi($prodiId)
    {
        $user = Auth::user();

        // Super admin can access all
        if ($this->userHasRole($user, 'super_admin')) {
            return true;
        }

        // Tim Pengadaan can access all prodi
        if ($this->userHasRole($user, 'Tim Pengadaan')) {
            return true;
        }

        // Kaprodi can only access their own prodi
        if ($this->userHasRole($user, 'Kaprodi')) {
            return $user->prodi === $prodiId;
        }

        return false;
    }

    /**
     * Check if user can access AHP session
     */
    protected function canAccessAHPSession($sessionId)
    {
        $user = Auth::user();

        // Super admin can access all
        if ($this->userHasRole($user, 'super_admin')) {
            return true;
        }

        // Tim Pengadaan can access all AHP sessions
        if ($this->userHasRole($user, 'Tim Pengadaan')) {
            return true;
        }

        // Kaprodi can only access AHP sessions with their prodi data
        if ($this->userHasRole($user, 'Kaprodi')) {
            return PengajuanBahanAjar::where('ahp_session_id', $sessionId)
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('prodi', $user->prodi);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Simple role checking method
     */
    protected function userHasRole($user, $roleName)
    {
        try {
            // Check if user has roles relationship
            if (method_exists($user, 'roles')) {
                return $user->roles()->where('name', $roleName)->exists();
            }

            // Fallback: check if user has role attribute
            if (property_exists($user, 'role') || method_exists($user, 'getRole')) {
                $userRole = $user->role ?? $user->getRole();
                return $userRole === $roleName;
            }

            // Fallback: check if user has role_name attribute
            if (property_exists($user, 'role_name')) {
                return $user->role_name === $roleName;
            }

            // For now, allow access if user is authenticated
            // TODO: Implement proper role checking
            return true;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::warning('Role checking failed for user: ' . $user->id, [
                'error' => $e->getMessage(),
                'role_requested' => $roleName
            ]);

            // For now, allow access if user is authenticated
            // TODO: Implement proper role checking
            return true;
        }
    }

    /**
     * Get full prodi name
     */
    protected function getProdiFullName($prodi)
    {
        $prodiNames = [
            'trpl' => 'Teknologi Rekayasa Perangkat Lunak',
            'mesin' => 'Teknik Mesin',
            'elektro' => 'Teknik Elektro',
            'sipil' => 'Teknik Sipil',
            'kimia' => 'Teknik Kimia'
        ];

        return $prodiNames[$prodi] ?? strtoupper($prodi);
    }

    /**
     * Get export statistics
     */
    public function getExportStats()
    {
        $user = Auth::user();

        if (!$this->userHasRole($user, 'Tim Pengadaan') && !$this->userHasRole($user, 'super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke statistik export');
        }

        $stats = [
            'total_pengajuan' => PengajuanBahanAjar::count(),
            'pengajuan_per_prodi' => [],
            'status_distribution' => [],
            'urgensi_distribution' => []
        ];

        // Get pengajuan per prodi
        $prodis = User::whereHas('roles', function ($query) {
            $query->where('name', 'Kaprodi');
        })
            ->whereNotNull('prodi')
            ->pluck('prodi');

        foreach ($prodis as $prodi) {
            $count = PengajuanBahanAjar::whereHas('user', function ($query) use ($prodi) {
                $query->where('prodi', $prodi);
            })->count();

            $stats['pengajuan_per_prodi'][$prodi] = $count;
        }

        // Get status distribution
        $stats['status_distribution'] = PengajuanBahanAjar::selectRaw('status_pengajuan, COUNT(*) as count')
            ->groupBy('status_pengajuan')
            ->pluck('count', 'status_pengajuan')
            ->toArray();

        // Get urgensi distribution
        $stats['urgensi_distribution'] = [
            'prodi' => PengajuanBahanAjar::selectRaw('urgensi_prodi, COUNT(*) as count')
                ->groupBy('urgensi_prodi')
                ->pluck('count', 'urgensi_prodi')
                ->toArray(),
            'tim_pengadaan' => PengajuanBahanAjar::selectRaw('urgensi_tim_pengadaan, COUNT(*) as count')
                ->groupBy('urgensi_tim_pengadaan')
                ->pluck('count', 'urgensi_tim_pengadaan')
                ->toArray(),
            'institusi' => PengajuanBahanAjar::selectRaw('urgensi_institusi, COUNT(*) as count')
                ->groupBy('urgensi_institusi')
                ->pluck('count', 'urgensi_institusi')
                ->toArray(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
