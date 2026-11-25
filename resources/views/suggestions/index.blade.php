@extends('layouts.app')

@section('page_title', 'Suggestions / Issues')

@section('styles')
<!-- Quill Rich Text Editor Stylesheet -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    .suggestion-container {
        max-width: 860px;
        margin: 0 auto;
    }
    .ql-toolbar.ql-snow {
        border-top-left-radius: var(--border-radius);
        border-top-right-radius: var(--border-radius);
        border-color: var(--border-color);
        background-color: #f8fafc;
        padding: 10px 12px;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: var(--border-radius);
        border-bottom-right-radius: var(--border-radius);
        border-color: var(--border-color);
        font-family: inherit;
        font-size: 0.95rem;
    }
    .ql-editor {
        min-height: 260px;
        line-height: 1.6;
        color: var(--text-color);
    }
    .ql-editor.ql-blank::before {
        color: #94a3b8;
        font-style: normal;
    }
    .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        margin: 8px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .editor-wrapper {
        border-radius: var(--border-radius);
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .editor-wrapper.focused {
        box-shadow: 0 0 0 3px rgba(1, 55, 86, 0.12);
    }
    .editor-helper-text {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
        font-size: 0.76rem;
        color: var(--text-muted);
    }
</style>
@endsection

@section('content')
<div class="suggestion-container">
    <div class="card">
        <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-envelope-paper" style="font-size: 1.25rem; color: var(--primary-color);"></i>
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--primary-dark);">Submit a Suggestion or Report an Issue</h3>
            </div>
        </div>
        <div class="card-body" style="padding: 24px;">
            <form id="suggestionForm" action="{{ route('support.store') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="subject" class="form-label" style="font-weight: 600;">Subject <span style="color: var(--priority-most-urgent);">*</span></label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        class="form-control @error('subject') is-invalid @enderror"
                        placeholder="e.g. Issue with file forwarding, Suggestion for dashboard..."
                        value="{{ old('subject') }}"
                        maxlength="150"
                        required
                    >
                    @error('subject')
                        <div style="color: var(--priority-most-urgent); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 600;">Message / Details <span style="color: var(--priority-most-urgent);">*</span></label>
                    
                    <div class="editor-wrapper" id="editorWrapper">
                        <div id="quillEditor"></div>
                    </div>

                    <!-- Hidden textarea to bind Quill HTML for form submission -->
                    <textarea name="message" id="message" style="display: none;" required>{{ old('message') }}</textarea>

                    <div class="editor-helper-text">
                        <span><i class="bi bi-info-circle"></i> Use the toolbar to format text or attach/paste screenshots and images directly.</span>
                    </div>

                    @error('message')
                        <div style="color: var(--priority-most-urgent); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; align-items: center; gap: 12px; margin-top: 10px;">
                    <button type="submit" id="submitBtn" class="btn btn-primary" style="padding: 9px 22px; font-weight: 600;">
                        <i class="bi bi-send"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Quill Rich Text Editor Script -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean']
        ];

        const quill = new Quill('#quillEditor', {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Write your suggestion or report details here. You can bold text, insert lists, and attach or paste images...'
        });

        const editorWrapper = document.getElementById('editorWrapper');
        quill.on('selection-change', function(range) {
            if (range) {
                editorWrapper.classList.add('focused');
            } else {
                editorWrapper.classList.remove('focused');
            }
        });

        // Custom Image Handler to auto-compress large images before embedding
        const toolbar = quill.getModule('toolbar');
        toolbar.addHandler('image', function() {
            const fileInput = document.createElement('input');
            fileInput.setAttribute('type', 'file');
            fileInput.setAttribute('accept', 'image/*');
            fileInput.click();

            fileInput.onchange = function() {
                const file = fileInput.files[0];
                if (file) {
                    processAndInsertImage(file, quill);
                }
            };
        });

        // Also handle clipboard paste for screenshots & images
        quill.root.addEventListener('paste', function(e) {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (clipboardData && clipboardData.items) {
                for (let i = 0; i < clipboardData.items.length; i++) {
                    const item = clipboardData.items[i];
                    if (item.type.indexOf('image') !== -1) {
                        const file = item.getAsFile();
                        if (file) {
                            e.preventDefault();
                            processAndInsertImage(file, quill);
                            return;
                        }
                    }
                }
            }
        });

        // Function to optimize & insert image as base64
        function processAndInsertImage(file, quillInstance) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const maxWidth = 1200;
                    const maxHeight = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        if (width > height) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                    const range = quillInstance.getSelection(true);
                    quillInstance.insertEmbed(range.index, 'image', compressedBase64);
                    quillInstance.setSelection(range.index + 1);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // Restore old message if validation failed previously
        const initialContent = @json(old('message', ''));
        if (initialContent && initialContent.trim() !== '') {
            quill.root.innerHTML = initialContent;
        }

        // Form submit listener to transfer content into the hidden textarea
        const form = document.getElementById('suggestionForm');
        const hiddenMessageInput = document.getElementById('message');

        form.addEventListener('submit', function (e) {
            const htmlContent = quill.root.innerHTML;
            const textContent = quill.getText().trim();
            const hasImages = quill.root.querySelector('img') !== null;

            if (!textContent && !hasImages) {
                e.preventDefault();
                alert('Please provide some details for your suggestion or issue.');
                quill.focus();
                return false;
            }

            hiddenMessageInput.value = htmlContent;
        });
    });
</script>
@endsection
