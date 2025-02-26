// Theme handler for persistent dark mode across page navigations
const initThemeHandler = () => {
    return {
        darkMode: localStorage.getItem('dark') === 'true',
        init() {
            // Apply theme on initial load
            this.applyTheme(this.darkMode);
            
            // Watch for changes and update theme
            this.$watch('darkMode', (value) => {
                localStorage.setItem('dark', value);
                this.applyTheme(value);
            });

            // Listen for page navigation events
            document.addEventListener('livewire:navigated', () => {
                this.applyTheme(this.darkMode);
            });

            // Listen for storage changes from other tabs/windows
            window.addEventListener('storage', (e) => {
                if (e.key === 'dark') {
                    this.darkMode = e.newValue === 'true';
                    this.applyTheme(this.darkMode);
                }
            });
        },
        applyTheme(isDark) {
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    };
};

// Export the theme handler
export default initThemeHandler;