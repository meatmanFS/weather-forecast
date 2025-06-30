import { defineConfig } from 'vite';

export default defineConfig({
    watch: {}, // Enables watch mode
    build: {
        outDir: 'includes/Core/templates/assets',
        emptyOutDir: true,
        rollupOptions: {
            input: "src/main.js", // Use only the JS file as the entry point
            output: {
                entryFileNames: 'script.js', // Static JS file
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return 'style.css'; // Static CSS file
                    }
                    return '[name][extname]'; // Keep original asset names
                }
            }
        }
    }
});
