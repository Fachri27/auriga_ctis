<div>
    <div class="mx-10 p-6 space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Cases</h1>
                <p class="text-sm text-gray-500">Manage and monitor reported cases</p>
            </div>

            <div class="flex gap-3">
                <input type="text" wire:model.live="search" placeholder="Search case number..."
                    class="border rounded-lg px-4 py-2 w-64 focus:ring focus:ring-black/20">

                <select wire:model.live="filter" class="rounded-lg border border-gray-200 px-4 py-2.5 bg-white shadow-sm
                        text-[15px] focus:ring-black focus:border-black transition">
                    <option value="">Filter</option>
                    <option value="investigation">Penyidikan</option>
                    <option value="published">Published</option>
                    <option value="closed">Closed</option>
                </select>

                <select wire:model.live="filterVerif" class="rounded-lg border border-gray-200 px-4 py-2.5 bg-white shadow-sm
                        text-[15px] focus:ring-black focus:border-black transition">
                    <option value="">Filter Verifikasi</option>
                    <option value="me">Assigned to me</option>
                    <option value="pending">Pending Review</option>
                    <option value="rejected">Rejected Case</option>
                </select>

                <button class="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800"
                    @click="$dispatch('open-case-modal')">
                    <span class="text-lg">＋</span>
                    New Case
                </button>
            </div>

        </div>

        {{-- TABLE CARD --}}
        <div class="bg-white rounded-xl shadow border overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-4 text-left font-medium">Case</th>
                        <th class="p-4 text-left font-medium">Event Date</th>
                        <th class="p-4 text-left font-medium">Visibility</th>
                        <th class="p-4 text-left font-medium">Verified by</th>
                        <th class="p-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($cases as $c)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- CASE --}}
                        <td class="p-4">
                            <div class="font-semibold">{{ $c->case_number }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $c->id }}</div>
                        </td>

                        {{-- DATE --}}
                        <td class="p-4 text-gray-700">
                            {{ \Carbon\Carbon::parse($c->event_date)->format('d M Y') }}
                        </td>

                        {{-- VISIBILITY --}}
                        <td class="p-4">
                            @if($c->is_public)
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                Public
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                Private
                            </span>
                            @endif
                        </td>

                        {{-- VERIFIED BY --}}
                        <td class="p-4 text-gray-700">
                            @if($c->verified_by)
                            {{ $c->verified_by_name }}
                            @else
                            <span class="text-gray-400 italic">Not verified</span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="p-4 text-right">
                            <div class="flex justify-end items-center gap-4 text-sm">

                                <a href="{{ route('case.detail', $c->id) }}" class="text-blue-600 hover:underline">
                                    View
                                </a>

                                <button class="text-gray-600 hover:underline"
                                    @click="$dispatch('open-edit-case-modal', { caseId: {{ $c->id }} })">
                                    Edit
                                </button>

                                <button wire:click="deleteCase({{ $c->id }})"
                                    onclick="confirm('Delete this case?') || event.stopImmediatePropagation()"
                                    class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            No cases found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div class="p-4 border-t bg-gray-50">
                {{ $cases->links() }}
            </div>
        </div>

        {{-- MODAL --}}
        <livewire:cases.case-modal />

    </div>
</div>