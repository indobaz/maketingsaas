<?php

namespace App\Http\Controllers;

use App\Models\ContentPillar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContentPillarController extends Controller
{
    /** @var list<array{name: string, color: string, target_percentage: int}> */
    private const DEFAULT_PILLARS = [
        ['name' => 'Product Showcase', 'color' => '#E91E63', 'target_percentage' => 30],
        ['name' => 'Educational', 'color' => '#2196F3', 'target_percentage' => 25],
        ['name' => 'Behind the Scenes', 'color' => '#FF9800', 'target_percentage' => 20],
        ['name' => 'Promotional', 'color' => '#9C27B0', 'target_percentage' => 15],
        ['name' => 'User Generated Content', 'color' => '#4CAF50', 'target_percentage' => 10],
    ];

    public function index(): View
    {
        $companyId = Auth::user()->company_id;

        $pillars = ContentPillar::query()
            ->where('company_id', $companyId)
            ->withCount('posts')
            ->orderByDesc('created_at')
            ->get();

        $totalPercentage = (int) $pillars->sum('target_percentage');

        return view('pillars.index', [
            'pillars' => $pillars,
            'totalPercentage' => $totalPercentage,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->assertOwnerOrAdmin();

        $companyId = Auth::user()->company_id;
        $validated = $this->validatedPillar($request);

        $currentSum = (int) ContentPillar::where('company_id', $companyId)->sum('target_percentage');
        if ($currentSum + $validated['target_percentage'] > 100) {
            return back()
                ->withInput()
                ->withErrors([
                    'target_percentage' => "Total target percentage cannot exceed 100%. Currently used: {$currentSum}%",
                ]);
        }

        ContentPillar::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'target_percentage' => $validated['target_percentage'],
        ]);

        return back()->with('success', 'Pillar added');
    }

    public function update(Request $request, ContentPillar $contentPillar): RedirectResponse
    {
        $this->assertOwnerOrAdmin();
        $this->assertPillarInCompany($contentPillar);

        $companyId = Auth::user()->company_id;
        $validated = $this->validatedPillar($request);

        $otherSum = (int) ContentPillar::where('company_id', $companyId)
            ->where('id', '!=', $contentPillar->id)
            ->sum('target_percentage');

        if ($otherSum + $validated['target_percentage'] > 100) {
            return back()
                ->withInput()
                ->withErrors([
                    'target_percentage' => "Total target percentage cannot exceed 100%. Currently used: {$otherSum}%",
                ]);
        }

        $contentPillar->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'target_percentage' => $validated['target_percentage'],
        ]);

        return back()->with('success', 'Pillar updated');
    }

    public function destroy(ContentPillar $contentPillar): RedirectResponse
    {
        $this->assertOwnerOrAdmin();
        $this->assertPillarInCompany($contentPillar);

        $contentPillar->delete();

        return back()->with('success', 'Pillar deleted');
    }

    public function loadDefaults(Request $request): RedirectResponse
    {
        $this->assertOwnerOrAdmin();

        $companyId = Auth::user()->company_id;

        if (ContentPillar::where('company_id', $companyId)->exists()) {
            return back()->with('error', 'Default pillars can only be loaded when you have no pillars.');
        }

        foreach (self::DEFAULT_PILLARS as $row) {
            ContentPillar::create([
                'company_id' => $companyId,
                'name' => $row['name'],
                'color' => $row['color'],
                'target_percentage' => $row['target_percentage'],
            ]);
        }

        return back()->with('success', 'Default pillars loaded');
    }

    private function assertOwnerOrAdmin(): void
    {
        $role = Auth::user()->role ?? '';
        if (! in_array($role, ['owner', 'admin'], true)) {
            abort(403);
        }
    }

    private function assertPillarInCompany(ContentPillar $contentPillar): void
    {
        abort_unless($contentPillar->company_id === Auth::user()->company_id, 403);
    }

    /**
     * @return array{name: string, color: string, target_percentage: int}
     */
    private function validatedPillar(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'target_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
    }
}
