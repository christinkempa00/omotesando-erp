{{--
    Filter Outlet + Cabang (BranchLocation) utk dipakai di dalam <x-filter-bar>.
    Dropdown Cabang SELALU tampil. Isinya mengikuti Outlet terpilih
    (mis. Ask for Patty -> Gandaria City / Event); outlet tanpa cabang ->
    "Tidak Ada Data"; "Semua Outlet" -> semua cabang ("Outlet — Cabang").

    - :branches       daftar outlet utk dropdown Outlet; null = dropdown Outlet
                      disembunyikan (portal Outlet, sudah ter-scope).
    - :fixed-branch-id outlet tetap (portal Outlet) supaya dropdown Cabang
                      tahu cabang milik outlet mana.
--}}
@props([
    'branches' => null,
    'branchLocations' => [],
    'selectedBranch' => null,
    'selectedBranchLocation' => null,
    'fixedBranchId' => null,
])

@php($selectClass = 'w-full rounded-md border-hairline text-sm shadow-sm focus:border-action focus:ring-action')

<div x-data="{
        branchId: @js((string) ($fixedBranchId ?? $selectedBranch ?? '')),
        locationId: @js((string) ($selectedBranchLocation ?? '')),
        all: @js($branchLocations),
        branchNames: @js(collect($branches ?? [])->mapWithKeys(fn ($b) => [(string) $b->id => $b->name])),
        get locations() {
            if (this.branchId) return this.all[this.branchId] || [];
            // Semua Outlet: gabungkan cabang dari outlet yang ada di dropdown.
            return Object.entries(this.all)
                .filter(([id]) => id in this.branchNames)
                .flatMap(([id, locs]) => locs.map(l => ({ id: l.id, name: this.branchNames[id] + ' — ' + l.name })));
        },
     }" class="space-y-4">

    @if ($branches)
        <div>
            <label class="block text-xs font-medium text-ink-muted mb-1">Outlet</label>
            <select name="branch_id" x-model="branchId" @change="locationId = ''" class="{{ $selectClass }}">
                <option value="">Semua Outlet</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) $selectedBranch === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div>
        <label class="block text-xs font-medium text-ink-muted mb-1">Cabang</label>
        <select name="branch_location_id" x-model="locationId" class="{{ $selectClass }}">
            <option value="">Semua Cabang</option>
            <template x-for="loc in locations" :key="loc.id">
                <option :value="String(loc.id)" x-text="loc.name" :selected="String(loc.id) === locationId"></option>
            </template>
            <template x-if="locations.length === 0">
                <option value="" disabled>Tidak Ada Data</option>
            </template>
        </select>
    </div>
</div>
