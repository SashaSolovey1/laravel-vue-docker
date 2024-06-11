module.exports = {
    apps: [
        {
            name: 'vite',
            script: 'npm',
            args: 'run dev',
            cwd: './laravel-app',
            interpreter: 'none',
            watch: true,
        },
        {
            name: 'queue',
            script: 'php',
            args: 'artisan queue:work --queue=high,default',
            interpreter: 'none',
            watch: true,
        }
    ],
};
