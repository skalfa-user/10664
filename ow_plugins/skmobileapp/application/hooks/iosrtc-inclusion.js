#!/usr/bin/env node

'use strict';

const fs = require('fs');
const filename = 'Bridging-Header.h';
const importStatement = '\n' +
    '// cordova-plugin-iosrtc\n' +
    '#import "cordova-plugin-iosrtc-Bridging-Header.h"';

function getProjectName(protoPath) {
    var path = require('path'),
        cordovaConfigPath = path.join(protoPath, 'config.xml'),
        content = fs.readFileSync(cordovaConfigPath, 'utf-8');

    return /<name>([\s\S]*)<\/name>/mi.exec(content)[1].trim();
}


module.exports = function (context) {

    var projectRoot = context.opts.projectRoot,
        projectName = getProjectName(projectRoot);

    fs.appendFile('platforms/ios/' + projectName + '/' + filename, importStatement);
};