<div>
    <div class="mx-10 px-4 py-10 space-y-8">

        {{-- ================= HEADER ================= --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">
                    Laporan #{{ $report->report_code }}
                </h1>
                <p class="text-sm text-gray-500">
                    Dibuat {{ $report->created_at->format('d M Y, H:i') }}
                </p>
            </div>

            {{-- STATUS BADGE --}}
            <span class="px-4 py-2 rounded-full text-sm font-medium w-fit
                @if($report->status?->key === 'open') bg-yellow-100 text-yellow-800
                @elseif($report->status?->key === 'verified') bg-blue-100 text-blue-800
                @elseif($report->status?->key === 'rejected') bg-red-100 text-red-800
                @endif
            ">
                {{ $report->status?->name ?? '-' }}
            </span>
        </div>

        {{-- ================= GRID LAYOUT ================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ================= LEFT CONTENT ================= --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- REPORT DESCRIPTION --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="font-semibold text-lg mb-3">Deskripsi Laporan</h2>

                    <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                        {!!
                        $report->translations->where('locale','id')->first()->description
                        ?? 'Tidak ada deskripsi.'
                        !!}
                    </p>
                </div>

                {{-- EVIDENCE --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="font-semibold text-lg mb-4">Bukti / Evidence</h2>

                    @if ($report->evidence && count($report->evidence))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($report->evidence as $ev)
                        @php
                        $path = 'storage/' . $ev;
                        $ext = strtolower(pathinfo($ev, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        @endphp

                        @if ($isImage)
                        <a href="{{ asset($path) }}" target="_blank"
                            class="group relative border rounded-lg overflow-hidden">
                            <img src="{{ asset($path) }}"
                                class="h-40 w-full object-cover group-hover:scale-105 transition">
                        </a>

                        @elseif ($ext === 'pdf')
                        <a href="{{ asset($path) }}" target="_blank"
                            class="flex flex-col items-center justify-center h-40 border rounded-lg bg-gray-50 hover:bg-gray-100">
                            <span class="text-4xl">📄</span>
                            <span class="text-sm mt-2">View PDF</span>
                        </a>

                        @else
                        <a href="{{ asset($path) }}" download
                            class="flex flex-col items-center justify-center h-40 border rounded-lg bg-gray-50 hover:bg-gray-100">
                            <span class="text-4xl">📎</span>
                            <span class="text-sm mt-2">{{ strtoupper($ext) }} File</span>
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500">
                        Tidak ada bukti yang dilampirkan.
                    </p>
                    @endif
                </div>

            </div>

            {{-- ================= RIGHT SIDEBAR ================= --}}
            <div class="space-y-6">

                {{-- REPORTER INFO --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="font-semibold text-lg mb-4">Informasi Pelapor</h2>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500">Nama</p>
                            <p class="font-medium">{{ $report->nama_lengkap }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">NIK</p>
                            <p class="font-medium">{{ $report->nik }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium">{{ $report->jenis_kelamin }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">No HP</p>
                            <p class="font-medium">{{ $report->no_hp }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Email</p>
                            <p class="font-medium break-all">{{ $report->email }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Pekerjaan</p>
                            <p class="font-medium">{{ $report->pekerjaan }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Alamat</p>
                            <p class="font-medium">{{ $report->alamat }}</p>
                        </div>
                    </div>
                </div>

                {{-- ACTION PANEL --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="font-semibold text-lg mb-4">Aksi</h2>

                    <div class="space-y-3">
                        @if ($report->status?->key === 'open')

                        @can('verify', $report)
                        <button wire:click="verify"
                            title="Periksa bukti singkat dan klik untuk memberi tanda laporan terverifikasi"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Verifikasi Laporan
                        </button>
                        @else
                        <button disabled title="Anda tidak memiliki izin untuk memverifikasi laporan"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg opacity-60 cursor-not-allowed">
                            Verifikasi Laporan
                        </button>
                        @endcan

                        @can('reject', $report)
                        <button wire:click="rejected" title="Tandai laporan sebagai ditolak (beri alasan di timeline)"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Tolak Laporan
                        </button>
                        @else
                        <button disabled title="Anda tidak memiliki izin untuk menolak laporan"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg opacity-60 cursor-not-allowed">
                            Tolak Laporan
                        </button>
                        @endcan

                        @endif

                        @if ($report->status?->key === 'verified')
                        <button wire:click="convertToCase"
                            title="Buat case dari laporan ini dan generate tugas sesuai kategori"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Buat Case
                        </button>

                        <p class="text-xs text-gray-500 mt-2">Jika dikonfirmasi: sistem akan membuat case dan
                            men-generate tugas otomatis berdasarkan template kategori.</p>
                        @endif
                    </div>

                    @if (session('success'))
                    <p class="mt-4 text-green-600 text-sm font-medium">
                        {{ session('success') }}
                    </p>
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>