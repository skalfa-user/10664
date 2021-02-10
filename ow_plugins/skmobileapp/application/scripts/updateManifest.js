var utils = require('./utils');
var fs = require('fs');
const path = require('path');

module.exports = function() {
    // read the application config
    var configs = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../application.config.json'), 'utf8'));

    // read the manifest template file
    var manifest = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../src/manifest.tmpl.json'), 'utf8'));

    // generate a new manifest file
    // manifest['gcm_sender_id'] = configs['googleProjectNumber'];
    manifest['play_store_key'] = configs['playStoreKey'];
    manifest['name'] = configs['name'];
    manifest['short_name'] = configs['name'];
    manifest['background_color'] = configs['pwaBackgroundColor'];
    manifest['theme_color'] = configs['pwaThemeColor'];
    manifest['icons'] = [
        {
            src: configs['pwaIcon'],
            sizes: configs['pwaIconSize'],
            type: configs['pwaIconType']
        }
    ];

    fs.writeFileSync(path.resolve(__dirname, '../src/manifest.json'), JSON.stringify(manifest), 'utf8');

    // generate a new index.html file
    var indexFile = fs.readFileSync(path.resolve(__dirname, '../src/index.tmpl.html'), 'utf8');

    var indexFileContent = indexFile.replace(/\$\{\w+\}/g, function(match) {
        return utils.getConfigValue(match.substring(2, match.length - 1), configs);
    });

    fs.writeFileSync(path.resolve(__dirname, '../src/index.html'), indexFileContent, 'utf8');

    // generate a new ServiceWorker file
    var serviceWorkerFile = fs.readFileSync(path.resolve(__dirname, '../src/ServiceWorker.tmpl.js'), 'utf8');

    var serviceWorkerFileContent = serviceWorkerFile.replace(/\$\{\w+\}/g, function(match) {
        return utils.getConfigValue(match.substring(2, match.length - 1), configs);
    });

    fs.writeFileSync(path.resolve(__dirname, '../src/ServiceWorker.js'), serviceWorkerFileContent, 'utf8');
}
