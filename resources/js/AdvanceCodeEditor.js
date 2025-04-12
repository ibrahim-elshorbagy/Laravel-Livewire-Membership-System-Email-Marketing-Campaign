import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Heading,
    Font,
    Link,
    List,
    ListProperties,
    Table,
    TableToolbar,
    SourceEditing,
    GeneralHtmlSupport,
    HtmlComment
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

class AdvanceCodeEditor {
    constructor() {
        this.editor = null;
        this.initialized = false;
    }

    init() {
        // Check if editor already exists
        if (this.initialized || this.editor) {
            return;
        }

        const editorElement = document.querySelector('#editor');
        if (!editorElement || this.initialized) return;

        // Wait for Alpine.js to be available
        this.waitForAlpine().then(() => {
            ClassicEditor
                .create(editorElement, {
                    licenseKey: 'GPL',
                    plugins: [
                        Essentials,
                        Paragraph,
                        Bold,
                        Italic,
                        Underline,
                        Strikethrough,
                        Heading,
                        Font,
                        Link,
                        List,
                        ListProperties,
                        Table,
                        TableToolbar,
                        SourceEditing,
                        GeneralHtmlSupport,
                        HtmlComment
                    ],
                    toolbar: [
                        'undo', 'redo',
                        '|',
                        'heading',
                        '|',
                        'bold', 'italic', 'underline', 'strikethrough',
                        '|',
                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor',
                        '|',
                        'link', 'bulletedList', 'numberedList',
                        '|',
                        'insertTable',
                        '|',
                        'sourceEditing'
                    ],
                    fontSize: {
                        options: [8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36]
                    },
                    fontFamily: {
                        options: [
                            'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
                            'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'
                        ]
                    },
                    // Critical for allowing all HTML tags and attributes
                    htmlSupport: {
                        allow: [
                            {
                                name: /.*/,
                                attributes: true,
                                classes: true,
                                styles: true
                            }
                        ],
                        disallow: []
                    },
                    // Disable automatic HTML filtering
                    removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
                    // Start in source editing mode
                    startupMode: 'source'
                })
                .then(editor => {
                    this.editor = editor;
                    this.initialized = true;

                    // Get initial content from Alpine.js data
                    const form = document.getElementById('messageForm');
                    if (form && Alpine.$data) {
                        const initialContent = Alpine.$data(form).localMessageHtml;
                        if (initialContent) {
                            this.setContent(initialContent);
                            this.updatePreview(initialContent);
                        }
                    }

                    // Handle source editing mode changes
                    editor.plugins.get('SourceEditing').on('change:isSourceEditingMode', (evt, propertyName, isSourceEditingMode) => {
                        if (isSourceEditingMode) {
                            const sourceElement = document.querySelector('.ck-source-editing-area textarea');
                            if (sourceElement) {
                                // Add input event listener for live updates in source mode
                                sourceElement.addEventListener('input', () => {
                                    const content = sourceElement.value;
                                    this.updatePreview(content);

                                    // Update Alpine.js data
                                    const form = document.getElementById('messageForm');
                                    if (form && Alpine.$data) {
                                        Alpine.$data(form).localMessageHtml = content;
                                    }
                                });
                            }
                        }
                    });

                    // Handle regular editing mode changes
                    editor.model.document.on('change:data', () => {
                        const content = this.getContent();
                        this.updatePreview(content);

                        // Update Alpine.js data
                        const form = document.getElementById('messageForm');
                        if (form && Alpine.$data) {
                            Alpine.$data(form).localMessageHtml = content;
                        }
                    });

                })
                .catch(error => {
                    console.error('Editor initialization failed:', error);
                    this.initialized = false;
                });

        });
    }

    // Helper method to wait for Alpine.js to be available
    waitForAlpine() {
        return new Promise(resolve => {
            if (window.Alpine) {
                resolve();
            } else {
                document.addEventListener('alpine:init', () => resolve());
                // Fallback if alpine:init event doesn't fire
                setTimeout(resolve, 1000);
            }
        });
    }

    destroyEditor() {
        if (this.editor) {
            this.editor.destroy()
                .then(() => {
                    this.editor = null;
                    this.initialized = false;
                })
                .catch(error => console.error('Editor destruction failed:', error));
        }
    }

    getContent() {
        return this.editor ? this.editor.getData() : '';
    }

    setContent(content) {
        if (this.editor) {
            this.editor.setData(content);
        }
    }

    updatePreview(html) {
        const previewFrame = document.getElementById('preview-frame');
        if (previewFrame) {
            const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
        }
    }
}

const advanceCodeEditor = new AdvanceCodeEditor();

// Initialize the editor when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    advanceCodeEditor.init();
});

// Make it available globally for the blade template
window.advanceCodeEditor = advanceCodeEditor;

export default advanceCodeEditor;
