// Protractor configuration file, see link for more information
// https://github.com/angular/protractor/blob/master/lib/config.ts

const SpecReporter = require('jasmine-spec-reporter').SpecReporter;

exports.config = { 
    allScriptsTimeout: 11000,
    specs: [
        '../e2e/**/*.spec.ts'
    ],
    capabilities: {
        'browserName': 'chrome',
        'loggingPrefs': {
            'driver': 'WARNING',
            'server': 'WARNING',
            'browser': 'SEVERE'
        },
        'chromeOptions': {
            'args': [
                'incognito',
                'disable-shared-workers', 
                'disable-extensions'
            ]
        }
    },
    directConnect: true,
    baseUrl: 'http://0.0.0.0:8100',
    framework: 'jasmine',
    jasmineNodeOpts: {
        showColors: true,
        defaultTimeoutInterval: 30000,
        print: function() {}
    },
    onPrepare() {
        require('ts-node').register({
            project: 'e2e/tsconfig.json'
        });

        jasmine.getEnv().addReporter(new SpecReporter({spec: {
            displayStacktrace: true
        }}));

        jasmine.getEnv().addReporter(new function() {
            this.specDone = function(result) {
                if (result.failedExpectations.length > 0) {
                    // show all browser logs
                    browser.manage().logs().get('browser').then(function(browserLog) {
                        console.log('log: ' + require('util').inspect(browserLog)); 
                    });
                }
            };
        });
    }
};
