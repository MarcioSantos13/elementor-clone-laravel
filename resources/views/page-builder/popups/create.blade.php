@extends('page-builder.layouts.app')

@section('title', isset($popup) ? 'Edit Popup' : 'New Popup')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>{{ isset($popup) ? 'Edit Popup' : 'New Popup' }}</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">Configure the popup type, triggers, and associated content</p>
        </div>
        <a href="{{ route('page-builder.popups.index') }}" class="btn btn-secondary">
            &larr; Back
        </a>
    </div>

    <div class="card">
        <form action="{{ isset($popup) ? route('page-builder.popups.update', $popup) : route('page-builder.popups.store') }}" method="POST">
            @csrf
            @if(isset($popup)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="{{ old('title', $popup->title ?? '') }}"
                           placeholder="Ex: Newsletter Signup, Special Offer">
                </div>

                <div class="form-group" style="flex:1">
                    <label for="type">Type <span class="required">*</span></label>
                    <select id="type" name="type" class="form-control form-select" required>
                        <option value="">Select type...</option>
                        @foreach($types as $key)
                            <option value="{{ $key }}" {{ (old('type', $popup->type ?? request('type', '')) === $key) ? 'selected' : '' }}>
                                {{ ucfirst($key) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!isset($popup))
            <div class="form-group">
                <label>Triggers</label>
                <div id="triggers-list" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.75rem"></div>
                <div style="display:flex;gap:.5rem">
                    <select id="trigger-type" class="form-control form-select" style="flex:1">
                        <option value="">Select trigger...</option>
                        @foreach($triggerTypes as $key => $config)
                            <option value="{{ $key }}" data-has-value="{{ $config['has_value'] ? 'true' : 'false' }}" data-placeholder="{{ $config['placeholder'] ?? '' }}">
                                {{ $config['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="trigger-value-wrap" style="display:none;flex:1">
                        <input type="text" id="trigger-value" class="form-control" placeholder="Value">
                    </div>
                    <button type="button" id="add-trigger" class="btn btn-secondary" disabled>+ Add</button>
                </div>
            </div>
            @endif

            @if(!isset($popup))
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control form-select">
                    <option value="draft" selected>Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            @endif

            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0">
                <button type="submit" class="btn btn-primary">
                    {{ isset($popup) ? 'Update Popup' : 'Create Popup' }}
                </button>
                @if(!isset($popup))
                    <button type="submit" name="next" value="editor" class="btn btn-primary" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
                        Create & Open Editor
                    </button>
                @endif
                <a href="{{ route('page-builder.popups.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@if(!isset($popup))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggersList = document.getElementById('triggers-list');
    const triggerType = document.getElementById('trigger-type');
    const triggerValueWrap = document.getElementById('trigger-value-wrap');
    const triggerValue = document.getElementById('trigger-value');
    const addBtn = document.getElementById('add-trigger');
    const form = document.querySelector('form');
    let triggers = [];

    triggerType.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const hasValue = opt.dataset.hasValue === 'true';
        triggerValueWrap.style.display = hasValue ? 'block' : 'none';
        if (hasValue) {
            triggerValue.placeholder = opt.dataset.placeholder || 'Value';
        }
        addBtn.disabled = !this.value;
    });

    addBtn.addEventListener('click', function() {
        const type = triggerType.value;
        if (!type) return;
        const opt = triggerType.options[triggerType.selectedIndex];
        const hasValue = opt.dataset.hasValue === 'true';
        const value = hasValue ? triggerValue.value : null;
        triggers.push({ type, value });
        renderTriggers();
        triggerType.value = '';
        triggerValueWrap.style.display = 'none';
        triggerValue.value = '';
        addBtn.disabled = true;
    });

    function renderTriggers() {
        triggersList.innerHTML = '';
        if (triggers.length === 0) {
            triggersList.innerHTML = '<div style="padding:.5rem;text-align:center;color:#94a3b8;font-size:.82rem;background:#f8fafc;border-radius:6px">No triggers configured</div>';
            return;
        }
        triggers.forEach((t, idx) => {
            const opt = triggerType.querySelector(`option[value="${t.type}"]`);
            const label = opt ? opt.textContent : t.type;
            const displayLabel = label + (t.value ? ': ' + t.value : '');
            const card = document.createElement('div');
            card.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px';
            card.innerHTML = `
                <span>⚡</span>
                <span style="flex:1;font-size:.82rem;font-weight:500;color:#1e293b">${displayLabel}</span>
                <button type="button" class="remove-trigger" style="background:none;border:none;font-size:1.1rem;color:#ef4444;cursor:pointer;padding:0 .25rem">&times;</button>
            `;
            card.querySelector('.remove-trigger').onclick = () => {
                triggers.splice(idx, 1);
                renderTriggers();
            };
            triggersList.appendChild(card);
        });
    }

    form.addEventListener('submit', function() {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'triggers';
        input.value = JSON.stringify(triggers);
        form.appendChild(input);
    });

    renderTriggers();
});
</script>
@endpush
@endif
@endsection
