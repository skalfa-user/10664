
var baseWatchConfig = require('@ionic/app-scripts/config/watch.config');
var configTasks = require('../scripts/configure');

baseWatchConfig.wwwDir = {
    paths: [
        '{{WWW}}/**/*.(html|css|js)'
    ],
    callback: function() {
        return configTasks();
    }
};

module.exports = baseWatchConfig;