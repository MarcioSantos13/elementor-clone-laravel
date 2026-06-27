<div class="pb-element pb-{{ $element->type }}" data-element-id="{{ $element->id }}" data-element-type="{{ $element->type }}">
    <div class="pb-element-toolbar">
        <span>{{ $element->name }}</span>
        <button onclick="editElement({{ $element->id }})">Edit</button>
        <button onclick="duplicateElement({{ $element->id }})" title="Duplicate">⧉</button>
        <button onclick="deleteElement({{ $element->id }})" title="Delete">✕</button>
    </div>

    @if($element->children->isNotEmpty())
        <div class="pb-element-children">
            @foreach($element->children as $child)
                @include('page-builder.partials.element', ['element' => $child])
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    function editElement(id) { alert('Edit element ' + id); }
    function duplicateElement(id) { alert('Duplicate element ' + id); }
    function deleteElement(id) { alert('Delete element ' + id); }
</script>
@endpush
