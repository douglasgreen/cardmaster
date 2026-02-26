// modified: 2026-02-26

import js from '@eslint/js';
import pluginSecurity from 'eslint-plugin-security';
import pluginUnicorn from 'eslint-plugin-unicorn';
import pluginJsxA11y from 'eslint-plugin-jsx-a11y';
import globals from 'globals';
import eslintConfigPrettier from 'eslint-config-prettier';

// ------------------------------------------------------------------
// Global ignore patterns (replaces .eslintignore)
// ------------------------------------------------------------------
const ignorePatterns = [
    // Build / tooling directories
    'dist/**',
    'node_modules/**',
    'coverage/**',
    '*.config.*',
    'playwright-report/**',
    'test-results/**',
    'build/**',
    '.cache/**',
    '.next/**',
    // Composer / PHP
    'composer.lock',
    'vendor/**',
    // ESLint cache
    '.eslintcache',
    // Grunt
    '.grunt/**',
    // Husky
    '.husky/_/**',
    // Minified assets
    '*.min.*',
    // Node REPL history & npm stuff
    '.node_repl_history',
    '.npm/**',
    'npm-debug.log*',
    'package-lock.json',
    // PHPUnit
    '.phpunit.result.cache',
    // Python
    '*.pyc',
    '__pycache__/**',
    '*.pyo',
    // Symfony
    '.env.local.php',
    'parameters.yml',
    'var/**',
];

// ------------------------------------------------------------------
// Export the flat config
// ------------------------------------------------------------------
export default tseslint.config(
    // ----------------------------------------------------------------
    // Base JavaScript
    // ----------------------------------------------------------------
    js.configs.recommended,

    // ----------------------------------------------------------------
    // Global language options & parsers
    // ----------------------------------------------------------------
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.es2023,
            },
        },
    },

    // ----------------------------------------------------------------
    // Security baseline
    // ----------------------------------------------------------------
    {
        plugins: { security: pluginSecurity },
        rules: {
            ...pluginSecurity.configs.recommended.rules,
            'security/detect-object-injection': 'off', // often noisy
        },
    },

    // ----------------------------------------------------------------
    // Code‑quality (unicorn)
    // ----------------------------------------------------------------
    {
        plugins: { unicorn: pluginUnicorn },
        rules: {
            'unicorn/consistent-function-scoping': 'off',
            'unicorn/no-abusive-eslint-disable': 'error',
        },
    },

    // ----------------------------------------------------------------
    // Global ignore patterns (replaces .eslintignore)
    // ----------------------------------------------------------------
    {
        ignores: ignorePatterns,
    },

    // ----------------------------------------------------------------
    // Prettier – must be last to override conflicting rules
    // ----------------------------------------------------------------
    eslintConfigPrettier,
);
