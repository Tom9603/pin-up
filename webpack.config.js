const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/app.js')

    .enableSassLoader()

    .enableSingleRuntimeChunk()

;

module.exports = Encore.getWebpackConfig();
