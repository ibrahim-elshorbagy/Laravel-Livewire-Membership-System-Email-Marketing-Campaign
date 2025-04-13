
class AdvanceTinyMCE {
    constructor() {
        this.editor = null;
        this.initialized = false;
    }

    init() {
        // Check if editor already exists
        if (this.initialized || this.editor) {
            return;
        }

        const editorElement = document.querySelector('#text-editor');
        if (!editorElement || this.initialized) return;

        // Wait for Alpine.js to be available
        this.waitForAlpine().then(() => {
            // Create a script element to load TinyMCE from CDN
            // This ensures all resources (including skin and models) load correctly
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.9.1/tinymce.min.js'; //old v allow full page code
            script.referrerPolicy = 'origin';

            script.onload = () => {
                // Initialize TinyMCE once the script is loaded
                window.tinymce.init({
                    selector: '#text-editor',
                    height: 750,
                    menubar: true,
                    plugins: [
                        'fullpage',
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'preview', 'help', 'wordcount',
                        'codesample', 'directionality', 'emoticons', 'nonbreaking',
                        'pagebreak', 'quickbars', 'save'
                    ],
                    // This blocks script tags and other potentially harmful elements
                    toolbar: "fullscreen code codesample accordion addcomment aidialog aishortcuts aligncenter alignjustify alignleft alignnone alignright | anchor | blockquote blocks | backcolor | bold | casechange checklist copy cut | fontfamily fontsize forecolor  | italic | language | lineheight | newdocument | outdent | paste pastetext | print exportpdf exportword importword | redo | remove removeformat | selectall | strikethrough | styles | subscript superscript underline | undo | visualaid | a11ycheck advtablerownumbering revisionhistory typopgraphy anchor restoredraft casechange charmap checklist  addcomment showcomments ltr rtl editimage fliph flipv imageoptions rotateleft rotateright emoticons export footnotes footnotesupdate formatpainter  help image insertdatetime link openlink unlink bullist numlist media mergetags mergetags_list nonbreaking pagebreak pageembed permanentpen preview quickimage quicklink quicktable cancel save searchreplace showcomments spellcheckdialog spellchecker |  template typography | insertfile inserttemplate addtemplate | visualblocks visualchars | wordcount",
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',

                    // Essential for full HTML editing
                    code_dialog_height: 600,
                    code_dialog_width: 800,

                    // Start in code view for full HTML editing
                    toolbar_sticky: false,
                    setup: (editor) => {
                        
                        this.editor = editor;
                            editor.on('BeforeSetContent', function(e) {
                                // Remove script tags
                                e.content = e.content.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');

                                // Remove inline JavaScript events (onclick, onload, etc.)
                                e.content = e.content.replace(/\son\w+\s*=\s*(['"]).*?\1/gi, '');
                            });

                        // Get initial content from Alpine.js data
                        const form = document.getElementById('messageForm');
                        if (form && window.Alpine && Alpine.$data) {
                            const initialContent = Alpine.$data(form).localMessageHtml;
                            if (initialContent) {
                                editor.on('init', () => {
                                    this.setContent(initialContent);
                                    this.updatePreview(initialContent);
                                });
                            }
                        }

                        // Real-time preview updates on content change
                        editor.on('input change', () => {
                            const content = this.getContent();
                            this.updatePreview(content);

                            // Update Alpine.js data
                            const form = document.getElementById('messageForm');
                            if (form && window.Alpine && Alpine.$data) {
                                Alpine.$data(form).localMessageHtml = content;
                            }
                        });

                        // Also handle keyup events for more responsive updates
                        editor.on('keyup', () => {
                            const content = this.getContent();
                            this.updatePreview(content);

                            // Update Alpine.js data
                            const form = document.getElementById('messageForm');
                            if (form && window.Alpine && Alpine.$data) {
                                Alpine.$data(form).localMessageHtml = content;
                            }
                        });
                    },
                    init_instance_callback: (editor) => {
                        this.initialized = true;
                        console.log('Editor initialized:', editor);
                    }
                });
            };

            // Add the script to the document
            document.head.appendChild(script);
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
            if (window.tinymce) {
                window.tinymce.remove('#editor');
            }
            this.editor = null;
            this.initialized = false;
        }
    }

    getContent() {
        return this.editor ? this.editor.getContent() : '';
    }

    setContent(content) {
        if (this.editor) {
            this.editor.setContent(content);
        }
    }

    updatePreview(html) {
        const previewFrame = document.getElementById('preview-frame');
        if (previewFrame) {
            try {
                const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();
            } catch (e) {
                console.error('Error updating preview:', e);
            }
        }
    }
}

const advanceTinyMCE = new AdvanceTinyMCE();

// Initialize the editor when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    advanceTinyMCE.init();
});

// Make it available globally for the blade template
window.advanceCodeEditor = advanceTinyMCE; // Keep the same name for compatibility

export default advanceTinyMCE;
