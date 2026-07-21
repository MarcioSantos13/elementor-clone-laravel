@vite('resources/js/editor/index.js')
<script>
document.addEventListener('DOMContentLoaded', () => editor.init({{ $page->id }}, '{{ csrf_token() }}'));
document.addEventListener('click', (e) => {
    if (!e.target.closest('.pb-el') && !e.target.closest('.pb-structure-item') && !e.target.closest('.pb-settings') && !e.target.closest('.pb-toolbar') && !e.target.closest('.pb-nav-context') && !e.target.closest('.pb-panel')) {
        document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        editor.selectedId = null;
        document.getElementById('settings-empty').style.display = '';
        document.getElementById('settings-form').classList.remove('active');
    }
});
</script>