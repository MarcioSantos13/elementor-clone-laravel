@extends('page-builder.layouts.app')

@section('title', 'Tutorial')

@section('content')
    <div class="container tutorial">
        <div class="page-header">
            <h1>&#128214; Tutorial — Page Builder</h1>
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">&larr; Back to Pages</a>
        </div>

        <div class="toc">
            <strong>Contents</strong>
            <a href="#overview">Overview</a>
            <a href="#create-page">Creating a Page</a>
            <a href="#editor">The Editor</a>
            <a href="#drag-widgets">Adding Widgets</a>
            <a href="#select-settings">Editing Widget Settings</a>
            <a href="#page-settings">Page Settings</a>
            <a href="#responsive">Responsive Preview</a>
            <a href="#templates">Using Templates</a>
            <a href="#undo-redo">Undo &amp; Redo</a>
            <a href="#save-publish">Save &amp; Publish</a>
            <a href="#preview">Previewing a Page</a>
            <a href="#duplicate-delete">Duplicating &amp; Deleting</a>
        </div>

        {{-- OVERVIEW --}}
        <section id="overview" class="step">
            <h2>1. Overview</h2>
            <div class="step-body">
                <p>This page builder lets you create pages visually by dragging widgets onto a canvas, editing their content and style, and publishing the result — no coding required.</p>
                <p><strong>What you can do:</strong></p>
                <ul>
                    <li>Create pages with a title and status (draft / published)</li>
                    <li>Open a full-screen <strong>visual editor</strong></li>
                    <li>Drag <strong>widgets</strong> (Heading, Text, Image, Button, etc.) onto the page</li>
                    <li>Select any element and edit its <strong>settings</strong> in the right panel</li>
                    <li>Switch between <strong>desktop / tablet / mobile</strong> preview</li>
                    <li>Apply pre-built <strong>templates</strong> (Landing, About, Contact)</li>
                    <li>Undo / redo changes (Ctrl+Z / Ctrl+Shift+Z)</li>
                    <li>Auto-save every 60 seconds, or save / publish manually</li>
                    <li>Duplicate, export, import, or delete pages</li>
                </ul>
            </div>
        </section>

        {{-- CREATE PAGE --}}
        <section id="create-page" class="step">
            <h2>2. Creating a Page</h2>
            <div class="step-body">
                <p>From the pages list, click <strong>"New Page"</strong> in the top navigation bar.</p>
                <div class="illustration">
                    <div class="ill-preview" style="max-width:400px">
                        <div style="background:#f8f9fa;padding:1rem;border-radius:6px;border:1px solid #ddd;text-align:center">
                            <div style="font-size:.8rem;color:#888;margin-bottom:.5rem">pages list header</div>
                            <div style="display:flex;gap:.5rem;justify-content:center">
                                <span style="display:inline-block;padding:.3rem .8rem;background:#007bff;color:#fff;border-radius:4px;font-size:.85rem">+ New Page</span>
                            </div>
                        </div>
                    </div>
                </div>
                <ol>
                    <li>Enter a <strong>Title</strong> (e.g. "My Landing Page")</li>
                    <li>Choose a <strong>Status</strong> — <em>Draft</em> keeps it hidden, <em>Published</em> makes it visible</li>
                    <li>Optionally pick a <strong>Template</strong> (see <a href="#templates">step 8</a>)</li>
                    <li>Click <strong>"Create &amp; Open Editor"</strong> to jump straight into the visual editor</li>
                </ol>
            </div>
        </section>

        {{-- THE EDITOR --}}
        <section id="editor" class="step">
            <h2>3. The Editor</h2>
            <div class="step-body">
                <p>The editor is a full-screen dark-themed interface with three panels:</p>
                <div class="panel-layout-ill">
                    <div class="panel-ill left"><strong>Widgets</strong><br><span style="font-size:.75rem">drag items to canvas</span></div>
                    <div class="panel-ill center"><strong>Canvas</strong><br><span style="font-size:.75rem">your page preview</span></div>
                    <div class="panel-ill right"><strong>Settings</strong><br><span style="font-size:.75rem">selected element options</span></div>
                </div>
                <ul>
                    <li><strong>Left panel</strong> — list of available widgets. Drag one onto the canvas.</li>
                    <li><strong>Center (canvas)</strong> — shows a live preview of your page. Click any element to select it.</li>
                    <li><strong>Right panel</strong> — shows settings for the selected element (or page).</li>
                    <li><strong>Top toolbar</strong> — responsive buttons (desktop/tablet/mobile), save, publish, undo/redo.</li>
                </ul>
            </div>
        </section>

        {{-- ADDING WIDGETS --}}
        <section id="drag-widgets" class="step">
            <h2>4. Adding Widgets (Drag &amp; Drop)</h2>
            <div class="step-body">
                <p>Each widget adds a different type of content. Here's what each one does:</p>
                <table class="widget-table">
                    <tr><th>Widget</th><th>What it creates</th></tr>
                    <tr><td><strong>Heading</strong></td><td>A large title (&lt;h1&gt;–&lt;h6&gt;) with configurable tag, text, alignment, and color</td></tr>
                    <tr><td><strong>Text</strong></td><td>A paragraph or block of text with configurable content and color</td></tr>
                    <tr><td><strong>Image</strong></td><td>An image with configurable source URL, alt text, and width</td></tr>
                    <tr><td><strong>Button</strong></td><td>A clickable button with configurable text, URL, alignment, and color</td></tr>
                    <tr><td><strong>Section</strong></td><td>A structural container (full-width row). You put Columns or other widgets inside it</td></tr>
                    <tr><td><strong>Column</strong></td><td>A vertical column inside a Section. Controls width, alignment, padding, background</td></tr>
                </table>
                <p><strong>How to add:</strong></p>
                <ol>
                    <li>In the <strong>left panel</strong>, find the widget you want</li>
                    <li><strong>Drag</strong> it (click and hold) and <strong>drop</strong> it onto the canvas</li>
                    <li>The widget appears instantly on the canvas</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Tip:</strong> Start by dragging a <strong>Section</strong> onto the canvas, then drag a <strong>Column</strong> into the section, then drag content widgets (Heading, Text, etc.) into the column.
                </div>
            </div>
        </section>

        {{-- EDITING WIDGET SETTINGS --}}
        <section id="select-settings" class="step">
            <h2>5. Selecting &amp; Editing Widget Settings</h2>
            <div class="step-body">
                <ol>
                    <li><strong>Click</strong> any element on the canvas — it gets a blue outline to show it's selected</li>
                    <li>The <strong>right panel</strong> updates to show all editable settings for that widget</li>
                    <li>Each widget exposes its own controls (e.g. Heading has <em>Level</em>, <em>Text</em>, <em>Alignment</em>, <em>Color</em>)</li>
                    <li>Change any value — the canvas <strong>updates in real time</strong></li>
                </ol>
                <div class="illustration">
                    <div class="ill-flow">
                        <span>Click canvas element</span>
                        <span>&rarr;</span>
                        <span>Right panel shows controls</span>
                        <span>&rarr;</span>
                        <span>Change value</span>
                        <span>&rarr;</span>
                        <span>Canvas updates live</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- PAGE SETTINGS --}}
        <section id="page-settings" class="step">
            <h2>6. Page Settings</h2>
            <div class="step-body">
                <p>You can also change settings that apply to the entire page:</p>
                <ol>
                    <li>In the editor toolbar, click <strong>"Page Settings"</strong></li>
                    <li>The right panel switches to page-level controls</li>
                    <li>Configure:
                        <ul>
                            <li><strong>Container Width</strong> — max width of the page content (e.g. 1140px, full-width)</li>
                            <li><strong>Page Background</strong> — background color or image for the whole page</li>
                            <li><strong>Content Padding</strong> — padding around the page content</li>
                            <li><strong>Custom CSS</strong> — raw CSS for advanced customization</li>
                        </ul>
                    </li>
                    <li>Changes apply instantly to the canvas preview</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Tip:</strong> Click "Page Settings" again to deselect it and return to widget editing.
                </div>
            </div>
        </section>

        {{-- RESPONSIVE --}}
        <section id="responsive" class="step">
            <h2>7. Responsive Preview</h2>
            <div class="step-body">
                <p>See how your page looks on different screen sizes:</p>
                <ol>
                    <li>In the editor toolbar, click one of the responsive icons:
                        <ul>
                            <li>&#128187; <strong>Desktop</strong> — full width</li>
                            <li>&#128241; <strong>Tablet</strong> — 768px wide</li>
                            <li>&#128241; <strong>Mobile</strong> — 375px wide</li>
                        </ul>
                    </li>
                    <li>The canvas width adjusts instantly</li>
                    <li>You can edit settings and add widgets at any breakpoint</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Tip:</strong> Use mobile view to make sure your layout stacks nicely on small screens.
                </div>
            </div>
        </section>

        {{-- TEMPLATES --}}
        <section id="templates" class="step">
            <h2>8. Using Templates</h2>
            <div class="step-body">
                <p>Templates give you a head start by pre-populating the page with sections, columns, and widgets.</p>
                <p><strong>When creating a new page:</strong></p>
                <ol>
                    <li>On the <strong>Create Page</strong> form, look for the "Template" section</li>
                    <li>Choose from:
                        <ul>
                            <li><strong>Blank Page</strong> — start empty (default)</li>
                            <li><strong>Landing Page</strong> — hero section + features grid with headings, text, and buttons</li>
                            <li><strong>About Page</strong> — company intro with mission/team sections</li>
                            <li><strong>Contact Page</strong> — contact form layout with info and a form area</li>
                        </ul>
                    </li>
                    <li>Click <strong>"Create &amp; Open Editor"</strong> to see the template loaded in the editor</li>
                    <li>You can then modify any element or add more widgets</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Tip:</strong> You can also apply a template to an existing page from the pages list using the "Apply Template" button in the actions dropdown.
                </div>
            </div>
        </section>

        {{-- UNDO / REDO --}}
        <section id="undo-redo" class="step">
            <h2>9. Undo &amp; Redo</h2>
            <div class="step-body">
                <p>Every change you make in the editor is tracked, so you can step backward or forward:</p>
                <ul>
                    <li><strong>Undo</strong> — press <kbd>Ctrl</kbd> + <kbd>Z</kbd> or click the &#8630; button in the toolbar</li>
                    <li><strong>Redo</strong> — press <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> or click the &#8631; button in the toolbar</li>
                </ul>
                <p>This works across all actions: adding, moving, deleting elements, changing settings, etc.</p>
            </div>
        </section>

        {{-- SAVE & PUBLISH --}}
        <section id="save-publish" class="step">
            <h2>10. Save &amp; Publish</h2>
            <div class="step-body">
                <p>Two buttons in the editor toolbar let you persist your work:</p>
                <table class="widget-table">
                    <tr><th>Button</th><th>What it does</th></tr>
                    <tr><td><strong>Save Draft</strong></td><td>Saves the current state without changing the published version. Your page remains as "draft" or keeps its previous published state.</td></tr>
                    <tr><td><strong>Publish</strong></td><td>Saves and immediately publishes the page — it becomes visible to visitors.</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Tip:</strong> The editor also <strong>auto-saves</strong> every 60 seconds, so you won't lose work if you forget to save manually.
                </div>
            </div>
        </section>

        {{-- PREVIEW --}}
        <section id="preview" class="step">
            <h2>11. Previewing a Page</h2>
            <div class="step-body">
                <p>From the pages list, click <strong>"View"</strong> next to any page.</p>
                <p>This opens a clean, front-end preview of the page with all your widgets rendered and styled as they would appear on the live site.</p>
                <p>If the page is still a <strong>draft</strong>, only you (logged-in users) can see it.</p>
            </div>
        </section>

        {{-- DUPLICATE / DELETE --}}
        <section id="duplicate-delete" class="step">
            <h2>12. Duplicating &amp; Deleting</h2>
            <div class="step-body">
                <p>From the pages list, each page has action buttons:</p>
                <table class="widget-table">
                    <tr><th>Action</th><th>How</th></tr>
                    <tr><td><strong>Duplicate</strong></td><td>Creates an exact copy of the page (including all its elements) with "(copy)" appended to the title.</td></tr>
                    <tr><td><strong>Export</strong></td><td>Downloads the page as a <code>.json</code> file — you can share it or back it up.</td></tr>
                    <tr><td><strong>Import</strong></td><td>Upload a previously exported <code>.json</code> file to recreate the page.</td></tr>
                    <tr><td><strong>Delete</strong></td><td>Permanently removes the page and all its elements. A confirmation message appears after deletion.</td></tr>
                </table>
            </div>
        </section>

        <div class="tutorial-footer">
            <p>That's it! You're ready to build pages with the visual page builder.</p>
            <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">Create Your First Page &rarr;</a>
        </div>
    </div>

    <style>
        .tutorial { max-width: 920px; }
        .tutorial h1 { font-size: 1.8rem; }
        .toc { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.2rem 1.5rem; margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: .5rem 1.2rem; align-items: center; }
        .toc strong { font-size: .85rem; }
        .toc a { font-size: .85rem; color: #007bff; text-decoration: none; }
        .toc a:hover { text-decoration: underline; }
        .step { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
        .step h2 { font-size: 1.25rem; margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 2px solid #007bff; display: inline-block; }
        .step-body { font-size: .95rem; line-height: 1.6; }
        .step-body p { margin-bottom: .75rem; }
        .step-body ul, .step-body ol { margin-bottom: .75rem; padding-left: 1.5rem; }
        .step-body li { margin-bottom: .3rem; }
        .step-body table { margin: .75rem 0; }
        .widget-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .widget-table th, .widget-table td { padding: .5rem .75rem; border: 1px solid #eee; text-align: left; vertical-align: top; }
        .widget-table th { background: #f8f9fa; font-weight: 600; }
        .tip { background: #e7f3ff; border-left: 4px solid #007bff; padding: .75rem 1rem; border-radius: 4px; font-size: .88rem; margin: .75rem 0; }
        .panel-layout-ill { display: flex; gap: .5rem; margin: .75rem 0; }
        .panel-ill { flex: 1; text-align: center; padding: 1rem .5rem; border-radius: 6px; font-size: .8rem; }
        .panel-ill.left { background: #1e1e2d; color: #fff; }
        .panel-ill.center { background: #fff; border: 2px solid #333; color: #333; }
        .panel-ill.right { background: #f0f0f0; color: #333; }
        .ill-flow { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; background: #f8f9fa; padding: .75rem 1rem; border-radius: 6px; font-size: .85rem; }
        .ill-flow span { white-space: nowrap; }
        .ill-flow span:nth-child(even) { font-size: 1.2rem; color: #007bff; }
        .ill-preview { margin: .75rem 0; }
        kbd { background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; padding: .1rem .4rem; font-size: .8rem; font-family: inherit; }
        .tutorial-footer { text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .tutorial-footer p { margin-bottom: 1rem; font-size: 1.05rem; }
    </style>
@endsection
