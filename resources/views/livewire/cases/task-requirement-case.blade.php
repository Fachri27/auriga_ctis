<div
    x-data="{ open: @entangle('open') }"
    x-on:open-task-requirement-modal.window="open = true"
    x-on:close-task-requirement-modal.window="open = false"
    x-show="open"
    x-cloak
>

    <!-- BACKDROP -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"></div>

    <!-- MODAL -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl overflow-hidden">

            <!-- HEADER -->
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $task->name ?? 'Task' }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        Upload evidentiary document & complete requirements
                    </p>
                </div>

                <button
                    class="text-gray-400 hover:text-gray-600"
                    @click="open = false"
                >
                    ✕
                </button>
            </div>

            <!-- BODY -->
            <div class="px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">

                @foreach($requirements as $i => $req)
                    <div class="space-y-2">

                        <!-- LABEL -->
                        <label class="text-sm font-medium text-gray-800">
                            {{ $req['name'] }}
                            @if($req['is_required'])
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <!-- FILE UPLOAD -->
                        @if($req['field_type'] === 'file')

                        {{-- EXISTING FILE --}}
                        @if(!empty($req['value']))
                            <div class="flex items-center justify-between p-3 bg-gray-100 rounded-lg mb-2">
                                <div class="text-sm text-gray-700 truncate">
                                    📄 {{ basename($req['value']) }}
                                </div>

                                <div class="flex gap-3 text-sm">
                                    <a
                                        href="{{ asset('storage/'.$req['value']) }}"
                                        target="_blank"
                                        class="text-blue-600 hover:underline"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ asset('storage/'.$req['value']) }}"
                                        download
                                        class="text-gray-600 hover:underline"
                                    >
                                        Download
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- UPLOAD NEW FILE --}}
                        <div class="border-2 border-dashed rounded-lg p-4 text-center">
                            <input
                                type="file"
                                wire:model="taskFiles.{{ $req['requirement_id'] }}"
                                class="hidden"
                                id="file-{{ $req['requirement_id'] }}"
                            >

                            <label for="file-{{ $req['requirement_id'] }}"
                                class="cursor-pointer text-sm text-gray-600">
                                Click to upload new file (replace)
                            </label>

                            @if(isset($taskFiles[$req['requirement_id']]))
                                <div class="mt-2 text-xs text-green-600">
                                    ✔ {{ $taskFiles[$req['requirement_id']]->getClientOriginalName() }}
                                </div>
                            @endif
                            </div>
                        @endif


                        <!-- TEXT -->
                        @if($req['field_type'] === 'text')
                            <input
                                type="text"
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                                placeholder="Enter value..."
                            >
                        @endif

                        <!-- TEXTAREA -->
                        @if($req['field_type'] === 'textarea')
                            <textarea
                                wire:model.defer="requirements.{{ $i }}.value"
                                rows="4"
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                                placeholder="Write notes / explanation..."
                            ></textarea>
                        @endif

                        <!-- NUMBER -->
                        @if($req['field_type'] === 'number')
                            <input
                                type="number"
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                            >
                        @endif

                        <!-- DATE -->
                        @if($req['field_type'] === 'date')
                            <input
                                type="date"
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                            >
                        @endif

                        <!-- SELECT -->
                        @if($req['field_type'] === 'select')
                            <select
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="w-full rounded-lg border-gray-300 focus:ring-black focus:border-black"
                            >
                                <option value="">Select option</option>
                                @foreach($req['options'] as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @endif

                        <!-- CHECKBOX -->
                        @if($req['field_type'] === 'checkbox')
                            <div class="space-y-2">
                                @foreach($req['options'] as $opt)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            wire:model.defer="requirements.{{ $i }}.value"
                                            value="{{ $opt }}"
                                            class="rounded border-gray-300"
                                        >
                                        {{ $opt }}
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <!-- RADIO -->
                        @if($req['field_type'] === 'radio')
                            <div class="space-y-2">
                                @foreach($req['options'] as $opt)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="radio"
                                            wire:model.defer="requirements.{{ $i }}.value"
                                            value="{{ $opt }}"
                                            class="border-gray-300"
                                        >
                                        {{ $opt }}
                                    </label>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach

            </div>

            <!-- FOOTER -->
            <div class="px-6 py-4 border-t flex justify-end gap-3 bg-gray-50">
                <button
                    class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-100"
                    @click="open = false"
                >
                    Cancel
                </button>

                <button
                    wire:click="save"
                    class="px-5 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-900"
                >
                    Submit Task
                </button>
            </div>

        </div>
    </div>
</div>
