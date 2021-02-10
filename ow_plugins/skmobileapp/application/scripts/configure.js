
var task = require('shell-task');

const path = require('path');

var wwwDir = path.resolve(__dirname, '../www');
var cacheStaticDir = path.resolve(__dirname, '../../../../ow_static/plugins/skmobileapp');
var browserPlatformDir = path.resolve(__dirname, '../platforms/browser');

module.exports = function() {
    var promise = new Promise(function (resolve, reject) {
        new task('cordova-hcp build') // hot code push - calculating the hashes of the files
        .then('cp ' + browserPlatformDir + '/config.xml ' + wwwDir + '/') // copy all necessary files from the browser platform 
        .then('cp -R ' + browserPlatformDir + '/platform_www/cordova-js-src ' + wwwDir + '/')
        .then('cp -R ' + browserPlatformDir + '/platform_www/plugins ' + wwwDir + '/')
        .then('cp ' + browserPlatformDir + '/platform_www/cordova_plugins.js ' + wwwDir + '/')
        .then('cp ' + browserPlatformDir + '/platform_www/cordova.js ' + wwwDir + '/')
        .then('find ' +  wwwDir + '/ -type f \( -name "*.html" -o -name "*.xml" -o -name "*.js" -o -name "*.css" -o -name "*.svg" -o -name "*.map" \) -exec sh -c "gzip < {} > {}.gzip" \;') // gzip files
        .then('rm -rf ' + cacheStaticDir) // clear cache static dir
        .run(function (err) {
            if (!err) {
                resolve();
            }

            reject(err);
        });
    });

    return promise;
}
