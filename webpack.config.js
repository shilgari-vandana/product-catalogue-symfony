var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .cleanupOutputBeforeBuild()
        .autoProvidejQuery()      
        .enableSassLoader()
        // when versioning is enabled, each filename will include a hash that changes
        // whenever the contents of that file change. This allows you to use aggressive
        // caching strategies. Use Encore.isProduction() to enable it only for production.
        .enableVersioning(false)
        .addEntry('app', './assets/js/app.js')
        .addEntry('login', './assets/js/login.js')
        .addEntry('admin', './assets/js/admin.js')
        .addEntry('search', './assets/js/search.js')
        .splitEntryChunks()
        .enableSingleRuntimeChunk()
        .enableIntegrityHashes(Encore.isProduction())
        .configureBabel(null, {
            useBuiltIns: 'usage',
            corejs: 3,
        })
        ;

module.exports = Encore.getWebpackConfig();
