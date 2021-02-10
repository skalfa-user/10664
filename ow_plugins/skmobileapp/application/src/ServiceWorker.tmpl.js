/**
 * Check out https://googlechrome.github.io/sw-toolbox/ for
 * more info on how to use sw-toolbox to custom configure your service worker.
 */

//-- working with caches --//
'use strict';
importScripts('./build/sw-toolbox.js');

importScripts('./firebase-app-5.7.3.js');
importScripts('./firebase-messaging-5.7.3.js');
firebase.initializeApp({messagingSenderId: '${firebaseMessagingSenderId}'});

self.toolbox.options.cache = {
    name: 'skmobile-cache'
};

// pre-cache our key assets
self.toolbox.precache(
    [
        './build/main.js',
        './build/vendor.js',
        './build/main.css',
        './build/polyfills.js',
        'index.html',
        'manifest.json'
    ]
);

// media files should not be cached
toolbox.router.get(/.*(?<!.mp4\/)$/, function(request, values, options) {
    return self.toolbox.networkFirst(request, values, options);
});

//-- working with push notices --//

var channels = [];
var startupPushParams = null;
var pushApiUrl = '${serverUrl}/skmobileapp/api/web-pushes/';

/**
 * New push message handler
 */
self.addEventListener('push', function(event) {
    event.waitUntil(self.registration.pushManager.getSubscription().then(function(subscription) {
        return firebase.messaging().getToken()
            .then(function(token) {
                // get the push payload data
                return fetch(pushApiUrl + encodeURIComponent(token) + '/');
            })
            .catch(function() {
                // extract a token from url
                var token = subscription.endpoint.split('/').pop();
                return fetch(pushApiUrl + encodeURIComponent(token) + '/');
            });
    })
    .then(function(response) {
        return Promise.all([
            response.json(),
            clients.matchAll({
                type: 'window',
                includeUncontrolled: true
            })
        ]);
    })
    .then(function(promises) {
        var response = promises[0];
        var pages = promises[1];

        if (response.id) {
            // find active pages with the running app
            pages = pages.filter(function(page) {
                return page.focused === true && page.visibilityState === 'visible';
            });

            // show the push notifications only if app is not running or not on the focus
            if (!pages.length) {
                return self.registration.showNotification(response.title, {
                    body: response.message,
                    icon: response.icon,
                    tag: response.id,
                    data: {
                        url : response.url,
                        params: response.params
                    }
                });
            }
        }
    }));
});

/**
 * Notification click handler
 */
self.addEventListener('notificationclick', function(event) {
    var notification = event.notification;

    event.waitUntil(clients.matchAll().then(function(pages) {
        // close the notification window
        notification.close();

        // find hidden (not focused) pages with the running app
        pages = pages.filter(function(page) {
            return page.focused === false && page.visibilityState === 'hidden';
        });

        // set focus on the app
        if (pages.length) {
            pages[0].focus();

            // send push params to all active clients
            if (notification.data.params) {
                sendMessageToClients(notification.data.params);
            }

            return false;
        }

        return true;
    })
    .then(function(isNewWindowNeeded) {
        if (isNewWindowNeeded) {
            return clients.openWindow(notification.data.url).then(function() {
                if (notification.data.params) {
                    startupPushParams = notification.data.params;
                }
            });
        }
    }));
});

/**
 * Main script messages handler
 */
self.addEventListener('message', function(event) {
    // register a new channel
    if (event.ports.length) {
        channels.push(event.ports[0]);

        // send startup push params
        if (startupPushParams) {
            sendMessageToClients(startupPushParams);

            startupPushParams = null;
        }
    }
});

/**
 * Send message to clients
 */
function sendMessageToClients(params) {
    channels.forEach(function(channel) {
        var pushParams = Object.assign({}, {
            foreground: false
        }, params);

        channel.postMessage({
            additionalData: pushParams
        });
    });
}
