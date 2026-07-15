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
        <div class="cms-panel w-full max-w-3xl">

            <!-- HEADER -->
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">TASK</div>
                    <h2 class="cms-panel-title">
                        {{ $task->name ?? 'Task' }}
                    </h2>
                    <p class="cms-panel-sub">
                        Upload evidentiary document & complete requirements
                    </p>
                </div>

                <button
                    class="cms-btn cms-btn-ghost"
                    @click="open = false"
                >
                    Close
                </button>
            </div>

            <!-- BODY -->
            <div class="cms-panel-body space-y-5 max-h-[70vh] overflow-y-auto" style="padding:20px">

                @foreach($requirements as $i => $req)
                    <div class="space-y-2">

                        <!-- LABEL -->
                        <label class="block text-sm font-medium text-[color:var(--ink)]">
                            {{ $req['name'] }}
                            @if($req['is_required'])
                                <span class="text-[color:var(--danger)]">*</span>
                            @endif
                        </label>

                        <!-- FILE UPLOAD -->
                        @if($req['field_type'] === 'file')

                        {{-- EXISTING FILE --}}
                        @if(!empty($req['value']))
                            <div class="flex items-center justify-between p-3 border border-[color:var(--hairline)] rounded-[10px] bg-[color:var(--paper)] mb-2">
                                <div class="flex items-center gap-2 text-sm text-[color:var(--ink)] truncate">
                                    <svg class="w-4 h-4 text-[color:var(--muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                    <span class="truncate font-mono-c text-[color:var(--ink)]">{{ basename($req['value']) }}</span>
                                </div>

                                <div class="flex gap-3 text-sm">
                                    <a
                                        href="{{ asset('storage/'.$req['value']) }}"
                                        target="_blank"
                                        class="text-[color:var(--leaf-deep)] hover:underline"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ asset('storage/'.$req['value']) }}"
                                        download
                                        class="text-[color:var(--muted)] hover:underline"
                                    >
                                        Download
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- UPLOAD NEW FILE --}}
                        <div class="border-2 border-dashed border-[color:var(--hairline-2)] rounded-[10px] p-4 text-center bg-[color:var(--paper-2)]">
                            <input
                                type="file"
                                wire:model="taskFiles.{{ $req['requirement_id'] }}"
                                class="hidden"
                                id="file-{{ $req['requirement_id'] }}"
                            >

                            <label for="file-{{ $req['requirement_id'] }}"
                                class="cursor-pointer text-sm text-[color:var(--muted)]">
                                Click to upload new file (replace)
                            </label>

                            @if(isset($taskFiles[$req['requirement_id']]))
                                <div class="mt-2 text-xs text-[color:var(--leaf-deep)] flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    {{ $taskFiles[$req['requirement_id']]->getClientOriginalName() }}
                                </div>
                            @endif
                            </div>
                        @endif


                        <!-- TEXT -->
                        @if($req['field_type'] === 'text')
                            <input
                                type="text"
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="cms-input w-full"
                                placeholder="Enter value..."
                            >
                        @endif

                        <!-- TEXTAREA -->
                        @if($req['field_type'] === 'textarea')
                            <textarea
                                wire:model.defer="requirements.{{ $i }}.value"
                                rows="4"
                                class="cms-input w-full"
                                placeholder="Write notes / explanation..."
                            ></textarea>
                        @endif

                        <!-- NUMBER -->
                        @if($req['field_type'] === 'number')
                            <input
                                type="number"
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="cms-input w-full"
                            >
                        @endif

                        <!-- DATE -->
                        @if($req['field_type'] === 'date')
                            <input
                                type="date"
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="cms-input w-full"
                            >
                        @endif

                        <!-- SELECT -->
                        @if($req['field_type'] === 'select')
                            <select
                                wire:model.defer="requirements.{{ $i }}.value"
                                class="cms-input w-full"
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
                                    <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                                        <input
                                            type="checkbox"
                                            wire:model.defer="requirements.{{ $i }}.value"
                                            value="{{ $opt }}"
                                            class="rounded border-[color:var(--hairline-2)]"
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
                                    <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                                        <input
                                            type="radio"
                                            wire:model.defer="requirements.{{ $i }}.value"
                                            value="{{ $opt }}"
                                            class="border-[color:var(--hairline-2)]"
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
            <div class="px-6 py-4 border-t border-[color:var(--hairline)] flex justify-end gap-2 bg-[color:var(--paper)]">
                <button
                    class="cms-btn cms-btn-ghost"
                    @click="open = false"
                >
                    Cancel
                </button>

                <button
                    wire:click="save"
                    class="cms-btn cms-btn-leaf"
                >
                    Submit Task
                </button>
            </div>

        </div>
    </div>
</div>