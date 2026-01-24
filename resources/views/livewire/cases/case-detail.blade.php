<div class="bg-gray-100 min-h-screen">
    <div class="mx-10 px-6 py-6">

        {{-- ================= HEADER ================= --}}
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <div class="flex justify-between items-start gap-6">

                {{-- LEFT --}}
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $case->title }}
                    </h1>
                    <p class="text-sm text-gray-500">
                        {{ $case->case_number }}
                    </p>

                    <div class="flex items-center gap-3 mt-3">

                        {{-- STATUS BADGE (Shows internal status for legal traceability) --}}
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($case->status_key === 'investigation') bg-yellow-100 text-yellow-800
                            @elseif($case->status_key === 'prosecution') bg-blue-100 text-blue-800
                            @elseif($case->status_key === 'trial') bg-purple-100 text-purple-800
                            @elseif($case->status_key === 'executed') bg-orange-100 text-orange-800
                            @elseif($case->status_key === 'closed') bg-gray-200 text-gray-700
                            @elseif($case->status_key === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $case->status_name }}
                        </span>

                        {{-- STATUS GROUP BADGE (Virtual grouping for UI) --}}
                        @if($statusGroup !== 'unknown')
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($statusGroup === 'working') bg-yellow-50 text-yellow-900 border border-yellow-300
                                @elseif($statusGroup === 'decision') bg-blue-50 text-blue-900 border border-blue-300
                                @elseif($statusGroup === 'final') bg-gray-50 text-gray-900 border border-gray-300
                                @elseif($statusGroup === 'review') bg-indigo-50 text-indigo-900 border border-indigo-300
                                @else bg-gray-50 text-gray-700 @endif">
                            {{ ucfirst($statusGroup) }}
                        </span>
                        @endif

                        {{-- PUBLISHED BADGE --}}
                        @if($case->is_public)
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm"
                            title="Kasus ini dapat dilihat publik">
                            Dipublikasikan
                        </span>
                        @endif
                    </div>
                </div>

                {{-- RIGHT ACTION --}}
                <div class="flex items-center gap-3 flex-wrap">
                    <div>
                        <button wire:click="publishCases"
                            class="px-4 py-2 {{ $case->is_public ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded text-sm">
                            {{ $case->is_public ? 'Batalkan Publikasi' : 'Publikasikan' }}
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="px-4 py-2 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600">
                            Ubah Status ▾
                        </button>

                        <div x-show="open" x-cloak @click.away="open = false"
                            class="absolute mt-2 w-48 bg-white border rounded shadow z-50">

                            @foreach($availableStatuses as $key => $label)
                            <button wire:click="changeStatus('{{ $key }}')" @click="open = false"
                                onclick="return confirm('Ubah status menjadi {{ $label }}?')"
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                                {{ $label }}
                            </button>
                            @endforeach

                        </div>
                    </div>


                </div>

            </div>
        </div>

        {{-- INFO / HELP (Tooltips for non-technical users) --}}
        <div class="bg-yellow-50 rounded p-3 mb-4 text-sm text-yellow-900">
            <strong>Petunjuk singkat:</strong>
            <ul class="list-disc ml-5 mt-1">
                <li><strong>Ubah Status</strong> — tombol ini melakukan perubahan legal status kasus (hanya untuk
                    tindakan resmi, butuh izin khusus).</li>
                <li><strong>Publikasikan</strong> — membuat kasus dapat dilihat publik; ini <em>tidak</em> mengubah
                    status hukum.</li>
                <li><strong>Tugas</strong> — item kerja internal untuk pengumpulan bukti & verifikasi; menyelesaikan
                    tugas tidak mengubah status kasus.</li>
            </ul>
        </div>

        <div class="flex gap-6 px-6 border-b">

            @foreach([
            'overview' => 'Ringkasan',
            'handling' => 'Penanganan',
            'timeline' => 'Linimasa',
            ] as $tab => $label)

            <button wire:click="setTab('{{ $tab }}')"
                class="relative py-4 text-sm font-medium
                        {{ $activeTab === $tab ? 'text-black border-b-2 border-black' : 'text-gray-500 hover:text-black' }}">
                {{ $label }}

                @if($tab === 'handling' && $tasks->count())
                <span class="ml-2 text-xs bg-gray-200 px-2 py-0.5 rounded-full">
                    {{ $tasks->count() }}
                </span>
                @endif
            </button>

            @endforeach

        </div>

        {{-- ================= CONTENT ================= --}}
        <div class="p-6">

            {{-- ===== OVERVIEW ===== --}}
            @if($activeTab === 'overview')
            <div class="grid grid-cols-3 gap-6">

                <div class="col-span-2 space-y-4">
                    <section>
                        <h2 class="font-semibold text-lg mb-1">Summary</h2>
                        <p class="text-gray-700">{{ $case->summary }}</p>
                    </section>

                    <section>
                        <h2 class="font-semibold text-lg mb-1">Description</h2>
                        <p class="whitespace-pre-line text-gray-700">
                            {{ $case->description }}
                        </p>
                    </section>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Event Date</p>
                        <p>{{ $case->event_date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Location</p>
                        <p>Lat {{ $case->latitude }}, Lng {{ $case->longitude }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Pelaku</p>

                        @if($actors->isEmpty())
                        <p class="text-sm text-gray-500">Belum ada pelaku</p>
                        @else
                        <ul class="text-sm mt-1 space-y-1">
                            @foreach($actors as $actor)
                            <li>{{ ucfirst($actor->type) }} — {{ $actor->name }}</li>
                            @endforeach
                        </ul>
                        @endif

                        <div class="mt-2">
                            <button class="px-3 py-1 bg-black text-white rounded text-sm"
                                x-on:click="$dispatch('open-actor-modal', { caseId: {{ $case->id }} })">Tambah
                                Pelaku</button>
                        </div>
                    </div>
                </div>

            </div>
            @endif

            {{-- ===== HANDLING: Tasks, Documents, Discussion ===== --}}
            @if($activeTab === 'handling')
            <div class="space-y-6">

                {{-- Tasks --}}
                <section>
                    <h2 class="font-semibold text-lg mb-2">Tugas</h2>

                    @if($tasks->isEmpty())
                    <div class="border border-dashed rounded-lg p-6 text-center text-gray-500">
                        <p class="font-medium">Belum ada tugas</p>
                        <p class="text-sm mt-1">Tugas akan muncul otomatis berdasarkan kategori kasus, atau dapat
                            ditambahkan oleh admin.</p>
                    </div>
                    @endif

                    <div class="space-y-4">
                        @foreach($tasks as $task)
                        <div class="p-4 border rounded-lg bg-white shadow-sm">
                            <div class="flex justify-between items-center">

                                <div>
                                    <h3 class="font-semibold">{{ $task->name }}</h3>
                                    <span class="text-xs px-2 py-1 rounded
                                            @if($task->status === 'approved') bg-green-100 text-green-700
                                            @elseif($task->status === 'submitted') bg-blue-100 text-blue-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="$dispatch('open-task-requirement-modal', { id: '{{ $task->id }}' })"
                                        class="px-3 py-1 border rounded text-sm"
                                        title="Lihat detail tugas dan requirement">Lihat</button>

                                    @if($task->status !== 'approved')
                                    @if(auth()->user()->can('case.task.approve') || (method_exists(auth()->user(),
                                    'isAdmin') && auth()->user()->isAdmin()))
                                    <button wire:click="approveTask({{ $task->id }})"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-sm"
                                        title="Hanya supervisor/admin yang dapat menyetujui tugas">Setujui</button>
                                    @else
                                    <button disabled
                                        class="px-3 py-1 bg-green-600 text-white rounded text-sm opacity-60 cursor-not-allowed"
                                        title="Anda tidak memiliki izin untuk menyetujui tugas">Setujui</button>
                                    @endif
                                    @endif
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                {{-- Documents & Discussion side-by-side --}}
                <section>
                    <h2 class="font-semibold text-lg mb-2">Sumber & Diskusi</h2>

                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <h3 class="font-medium mb-2">Dokumen</h3>
                            <div class="space-y-3">
                                @foreach($documents as $doc)
                                <div class="p-4 border rounded-lg bg-white shadow-sm">
                                    <p class="font-semibold">{{ $doc->title ?? 'Dokumen' }}</p>
                                    <p class="text-sm text-gray-500">{{ $doc->mime }}</p>

                                    <div class="flex gap-4 mt-2">
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank"
                                            class="text-blue-600 underline text-sm" title="Buka file">Buka File</a>
                                        <button class="text-sm text-gray-700 underline"
                                            wire:click="$dispatch('open-edit-document-modal', { docId: {{ $doc->id }} })"
                                            title="Ubah metadata dokumen">Sunting</button>
                                    </div>
                                </div>
                                @endforeach

                                <button class="px-4 py-2 bg-black text-white rounded mt-4"
                                    x-on:click="$dispatch('open-upload-document-modal', { caseId: {{ $case->id }} })">Unggah
                                    Dokumen</button>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-medium mb-2">Diskusi</h3>
                            @livewire('cases.case-discussion', ['caseId' => $case->id])
                        </div>

                    </div>
                </section>

            </div>
            @endif

            {{-- ===== TIMELINE ===== --}}
            @if($activeTab === 'timeline')
            <div class="space-y-4">
                @foreach($timelines as $log)
                <div class="p-4 border-l-4 border-black bg-gray-50 rounded">
                    <p>{{ $log->notes }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $log->created_at }}</p>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
    {{-- MODALS --}}
    @livewire('cases.task-requirement-case')
    @livewire('cases.upload-document-case')
    @livewire('cases.actor-cases')
</div>