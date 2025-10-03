var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('web/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .addEntry('js/admin/app', [
        './src/AdminBundle/Resources/public/js/index.js'
    ])

    .addEntry('js/site/app', './src/SiteBundle/Resources/public/js/index.js')

    .addStyleEntry('css/site/app', './src/SiteBundle/Resources/public/sass/_index.scss')
    .addStyleEntry('css/site/user_profile', './src/SiteBundle/Resources/public/sass/_user-dashboard-index.scss')
    .addStyleEntry('css/site/pages/ad_view', './src/SiteBundle/Resources/public/sass/Pages/_ad_view.scss')
    .addStyleEntry('css/site/pages/single_ads_view', './src/SiteBundle/Resources/public/sass/Pages/_single_ads_view.scss')
    .addStyleEntry('css/admin/app', [
        './src/AdminBundle/Resources/public/scss/style.scss',
    ])

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    // .autoProvidejQuery()
    .autoProvideVariables({
        $: 'jquery',
        tjq: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
        'window.$': 'jquery',
    })
    .addLoader({
        test: /\.(htc)$/,
        use: [{
            loader: 'url-loader',
            options: {
                limit: 10000, // Convert images < 8kb to base64 strings
                name: '/[name].[hash].[ext]',
            }
        }]
    })
    .enableBuildNotifications(true, function (options) {
        options.alwaysNotify = true;
        options.title = 'DONE';
    })
    .enableSingleRuntimeChunk()
;

let config = Encore.getWebpackConfig();
config.resolve.alias = {
    'waypoints': __dirname + '/node_modules/jquery-waypoints/waypoints.js',
    'router'   : __dirname + '/assets/js/router.js'
};

module.exports = config;
