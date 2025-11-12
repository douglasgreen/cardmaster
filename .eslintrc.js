module.exports = {
    parser: "espree", // Use the default JavaScript parser
    extends: ["eslint:recommended"],
    env: {
        browser: true,
        es6: true,
        jquery: true,
        node: true,
    },
    globals: {
        handleError: "readonly"
    },
    ignorePatterns: ["node_modules/", "vendor/"],
    parserOptions: {
        ecmaVersion: 2021,
        sourceType: "script",
    },
    rules: {
        indent: ["error", 4],
        "prefer-const": "error",
        semi: ["error", "always"],
    },
};
