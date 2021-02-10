var webpack = require('webpack');
const chalk = require("chalk");
const fs = require('fs');
const path = require('path');
const useDefaultConfig = require('@ionic/app-scripts/config/webpack.config.js');

const env = process.env.IONIC_ENV;

if (env === 'prod' || env === 'dev') {
    useDefaultConfig[env].plugins.push(new webpack.DefinePlugin({
        'process.env': {
            'NODE_ENV': JSON.stringify((env == 'prod' ? 'production' : 'development'))
        }
    }));

    useDefaultConfig[env].resolve.alias = {
        "app": path.resolve('./src/app/'),
        "services": path.resolve('./src/services/'),
        "store": path.resolve('./src/store/'),
        "pages": path.resolve('./src/pages/'),
        "shared": path.resolve('./src/shared/')
    };

} else {
    // Default to dev config
    useDefaultConfig[env] = useDefaultConfig.dev;
    useDefaultConfig[env].resolve.alias = {
        "app": path.resolve('./src/app/'),
        "services": path.resolve('./src/services/'),
        "store": path.resolve('./src/store/'),
        "pages": path.resolve('./src/pages/'),
        "shared": path.resolve('./src/shared/')
    };
}

module.exports = function () {
    return useDefaultConfig;
};
