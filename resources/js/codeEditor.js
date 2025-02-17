import { EditorView, basicSetup } from "codemirror";
import { EditorState } from "@codemirror/state";
import { html } from "@codemirror/lang-html";
import { oneDark } from "@codemirror/theme-one-dark";

// Global updatePreview function to update the preview iframe
window.updatePreview = function(content) {
    const frame = document.getElementById('preview-frame');
    if (frame) {
        const previewContent = `${content}`;
        frame.contentWindow.document.open();
        frame.contentWindow.document.write(previewContent);
        frame.contentWindow.document.close();
    }
};

let editor = null;

function initializeEditor() {
    const editorElement = document.getElementById('editor');
    if (!editorElement) return;

    // Get initial content from the hidden textarea
    const initialContent = editorElement.value;

    // Custom theme extension to force the editor to fill its container
    const customTheme = EditorView.theme({
        "&": {
            height: "100%",
            maxHeight: "100%"
        },
        ".cm-scroller": {
            overflow: "auto",
            maxHeight: "100%"
        },
        "&.cm-focused": {
            outline: "none"
        }
    });

    // Create the editor state
    const state = EditorState.create({
        doc: initialContent,
        extensions: [
            basicSetup,
            html(),
            oneDark,
            customTheme,
            EditorView.updateListener.of(update => {
                if (update.docChanged) {
                    // Update Livewire model
                    const content = update.state.doc.toString();
                    editorElement.value = content;
                    editorElement.dispatchEvent(new Event('input'));
                    // Update preview
                    updatePreview(content);
                }
            })
        ]
    });

    // Create the editor view inside the container
    const container = document.querySelector('#editor-container');
    editor = new EditorView({
        state,
        parent: container
    });

    // Use a ResizeObserver to update CodeMirror when container size changes
    if (window.ResizeObserver) {
        const resizeObserver = new ResizeObserver(() => {
            editor.requestMeasure();
        });
        resizeObserver.observe(container);
    }

    // Initial preview update
    updatePreview(initialContent);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEditor();
});

// Re-initialize when Livewire updates the DOM
document.addEventListener('livewire:navigated', initializeEditor);
