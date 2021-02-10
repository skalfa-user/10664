let fs = require('fs');
let fixtures = []; // list of needed fixtures

let appRouter = function (app) {
    // middleware (check the trailing slash)
    app.use(function(req, res, next) {
        if (req.path.substr(-1) != '/' && req.path.length > 1) {
            res.sendStatus(404)

            return;
        }

        next();
    });

    // middleware (set content type and parse fixtures)
    app.use(function (req, res, next) {
        res.header('Content-Type','application/json');
        fixtures = req.get('fixtures') // get fixtures from headers
            ? req.get('fixtures').split(',')
            : (req.query['fixtures'] ? req.query['fixtures'].split(',') : []); // get fixtures from get params

        next();
    });

    // configs
    app.get('/skmobileapp/api/configs/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('configs.json');

        // enable the maintenance mode
        if (fixtures.includes('configs_maintenance')) {
            fixture = Object.assign({}, fixture, {
                maintenanceMode: true
            });
        }

        // disable the tinder search mode
        if (fixtures.includes('configs_tinder_disabled')) {
            fixture = Object.assign({}, fixture, {
                searchMode: 'browse'
            });
        }

        // disable the search mode
        if (fixtures.includes('configs_search_disabled')) {
            fixture = Object.assign({}, fixture, {
                searchMode: 'tinder'
            });
        }

        // disable the user search by username
        if (fixtures.includes('configs_search_by_username_disabled')) {
            fixture = Object.assign({}, fixture, {
                isSearchByUserNameActive: false
            });
        }

        // disable the gdpr plugin
        if (fixtures.includes('configs_gdpr_disable')) {
            fixture = Object.assign({}, fixture, {
                activePlugins: fixture.activePlugins.filter(plugin => plugin != 'gdpr')
            });
        }

        // disable the hotlist plugin
        if (fixtures.includes('configs_hotlist_disable')) {
            fixture = Object.assign({}, fixture, {
                activePlugins: fixture.activePlugins.filter(plugin => plugin != 'hotlist')
            });
        }

        // disable the bookmars plugin
        if (fixtures.includes('configs_bookmarks_disable')) {
            fixture = Object.assign({}, fixture, {
                activePlugins: fixture.activePlugins.filter(plugin => plugin != 'bookmarks')
            });
        }

        // disable the who viewed me plugin
        if (fixtures.includes('configs_ocsguests_disable')) {
            fixture = Object.assign({}, fixture, {
                activePlugins: fixture.activePlugins.filter(plugin => plugin != 'ocsguests')
            });
        }

        // require the avatar uploading mode
        if (fixtures.includes('configs_require_avatar')) {
            fixture = Object.assign({}, fixture, {
                isAvatarRequired: true
            });
        }

        res.send(fixture);
    });

    // langs
    app.get('/skmobileapp/api/i18n/:id/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('i18n.json'));
    });

    // server events
    app.get('/skmobileapp/api/server-events/', function(req, res) {
        res.header('Content-Type','text/event-stream');
        res.send([]);
    });

    // server events for logged users
    app.get('/skmobileapp/api/server-events/user/:id/', function(req, res) {
        res.set({
            'Content-Type': 'text/event-stream',
            'Cache-Control': 'no-cache',
            'Connection': 'keep-alive',
            'Access-Control-Allow-Origin': '*'
        });

        const channels = [
            'guests',
            'matchedUsers',
            'conversations',
            'compatibleUsers',
            'hotList',
            'videoIm'
        ];

        let id = 1;

        channels.forEach(channel => {
            let data = {
                channel,
                data: []
            };

            // add user inside the guest list
            if (channel == 'guests' && fixtures.includes('se_guests')) {
                data['data'] = readFixtureJsonFile('guests.json');
            }

            // add user inside the hot list
            if (channel == 'hotList' && fixtures.includes('se_hot_list')) {
                data['data'] = readFixtureJsonFile('hot.list.json');
            }

            // add conversations
            if (channel == 'conversations' && fixtures.includes('se_conversations')) {
                data['data'] = readFixtureJsonFile('conversations.json');
            }

            res.write(`event: message\nid: ${id}\ndata:${JSON.stringify(data)}`);
            res.write('\n\n');
            id++;
        });

        res.end();
    });

    // login
    app.post('/skmobileapp/api/login/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('login.json');

        // failed login
        if (fixtures.includes('login_failed')) {
            fixture = Object.assign({}, fixture, {
                success: false,
                error: 'Error',
                token: ''
            });
        }

        res.send(fixture);
    });

    // update message
    app.put('/skmobileapp/api/mailbox/messages/', function(req, res) {
        res.status(204).send();
    });

    // create a text message
    app.post('/skmobileapp/api/mailbox/messages/', function(req, res) {
        // failed create a message
        if (fixtures.includes('message_create_failed')) {
            res.status(404).send({
                messagesError: 'Test'
            });

            return;
        }

        // read a basic fixture
        let fixture = readFixtureJsonFile('message.json');

        // change some message's params
        fixture = Object.assign({}, fixture, {
            id: fixture.id + 1,
            text: req.body.text,
            tempId: req.body.id,
            isRecipientRead: true
        });

        res.send(fixture);
    });

    // create a photo message
    app.post('/skmobileapp/api/mailbox/photo-messages/', function(req, res) {
        // failed create a photo message
        if (fixtures.includes('photo_message_create_failed')) {
            res.status(404).send({
                messagesError: 'Test'
            });

            return;
        }

        // read a basic fixture
        let fixture = readFixtureJsonFile('message.json');

        // change some message's params
        fixture = Object.assign({}, fixture, {
            attachments: [
                readFixtureJsonFile('message.attachment.json')
            ],
            id: fixture.id + 1,
            text: 'Attachment',
            tempId: req.query.id,
            isRecipientRead: true
        });

        res.send(fixture);
    });

    // get chat history messages
    app.get('/skmobileapp/api/mailbox/messages/history/user/:id/', function(req, res) {
        let messages = [];

        // get a predefined history message
        if (fixtures.includes('message_history')) {
            let fixture = readFixtureJsonFile('message.json');

            fixture = Object.assign({}, fixture, {
                id: 10000,
                text: 'history',
                isAuthor: false
            });

            messages.push(fixture);
        }

        res.send(messages);
    });

    // get chat messages
    app.get('/skmobileapp/api/mailbox/messages/user/:id/', function(req, res) {
        let messages = [];

        // get a predefined message
        if (fixtures.includes('message')) {
            messages.push(readFixtureJsonFile('message.json'));
        }

        // get a list of messages
        if (fixtures.includes('message_list')) {
            for (let i = 0; i < 20; i++) {
                let fixture = readFixtureJsonFile('message.json');

                fixture = Object.assign({}, fixture, {
                    id: fixture.id + i,
                    text: fixture.text + '_' + i,
                    timeStamp: + new Date(),
                    updateStamp: + new Date()
                });

                messages.push(fixture);
            }
        }

        // get a predefined message protected by credits
        if (fixtures.includes('message_protected') || fixtures.includes('message_photo_protected')) {
            let fixture = readFixtureJsonFile('message.json');

            fixture = Object.assign({}, fixture, {
                isAuthor: false,
                isAuthorized: false
            });

            messages.push(fixture);
        }

        res.send(messages);
    });

    // get a chat message
    app.get('/skmobileapp/api/mailbox/messages/:id/', function(req, res) {
        let fixture = readFixtureJsonFile('message.json');

        // get a predefined text message protected by credits
        if (fixtures.includes('message_protected')) {
            fixture = readFixtureJsonFile('message.json');

            fixture = Object.assign({}, fixture, {
                isAuthor: false,
                isAuthorized: true
            });
        }

        // get a predefined photo message protected by credits
        if (fixtures.includes('message_photo_protected')) {
            fixture = readFixtureJsonFile('message.json');

            fixture = Object.assign({}, fixture, {
                isAuthor: false,
                isAuthorized: true,
                attachments: [
                    readFixtureJsonFile('message.attachment.json')
                ]
            });
        }

        res.send(fixture);
    });

    // like / dislike
    app.post('/skmobileapp/api/math-actions/user/', function(req, res) {
        res.send(readFixtureJsonFile('match.action.json'));
    });

    // update conversations
    app.put('/skmobileapp/api/mailbox/conversations/:id/', function(req, res) {
        res.status(204).send();
    });

    // delete conversations
    app.delete('/skmobileapp/api/mailbox/conversations/:id/', function(req, res) {
        res.status(204).send();
    });

    // tinder search users
    app.get('/skmobileapp/api/tinder-users/', function(req, res) {
        let users = [];

        // get a predefined tinder user
        if (fixtures.includes('tinder_user')) {
            users.push(readFixtureJsonFile('tinder.search.json'));
        }

        // get a list of tinder users
        if (fixtures.includes('tinder_user_list')) {
            for (let i = 0; i < 2; i++) {
                let fixture = readFixtureJsonFile('tinder.search.json');

                fixture = Object.assign({}, fixture, {
                    id: fixture.id + i,
                    userName: fixture.userName + ' ' + (fixture.id + i)
                });

                users.push(fixture);
            }
        }

        res.send(users);
    });

    // search users
    app.post('/skmobileapp/api/users/searches/', function(req, res) {
        let users = [];

        // show a second user
        if (fixtures.includes('user_search_second_user')) {
            let fixture = readFixtureJsonFile('user.json');

            fixture = Object.assign({}, fixture, {
                id: 2,
                email: 'tester2@test.com',
                userName: 'Tester2',
                permissions: [],
                token: null
            });

            users.push(fixture);
        }

        // get a list of users
        if (fixtures.includes('user_search_list')) {
            for (let i = 2; i < 10; i++) {
                let fixture = readFixtureJsonFile('user.json');

                fixture = Object.assign({}, fixture, {
                    id: i,
                    userName: fixture.userName + ' ' + i,
                    permissions: [],
                    token: null
                });

                users.push(fixture);
            }
        }

        res.send(users);
    });

    // block users
    app.post('/skmobileapp/api/users/blocks/:id/', function(req, res) {
        res.status(204).send();
    });

    // unblock users
    app.delete('/skmobileapp/api/users/blocks/:id/', function(req, res) {
        res.status(204).send();
    });

    // users
    app.get('/skmobileapp/api/users/:id/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('user.json');

        // user not found
        if (fixtures.includes('user_not_found')) {
            res.status(404).send();

            return;
        }

        // user email confirmation
        if (fixtures.includes('user_email_confirmation')) {
            res.status(403).send({
                type: 'emailNotVerified',
                shortDescription: 'You have to verify your email'
            });

            return;
        }

        // user disapproved
        if (fixtures.includes('user_disapproved')) {
            res.status(403).send({
                type: 'disapproved',
                shortDescription: 'Your profile is disapproved'
            });

            return;
        }

        // user suspended
        if (fixtures.includes('user_suspended')) {
            res.status(403).send({
                type: 'suspended',
                shortDescription: 'test',
                description: 'test'
            });

            return;
        }

        // complete profile
        if (fixtures.includes('user_complete_profile')) {
            res.status(403).send({
                type: 'profileNotCompleted',
                shortDescription: 'You have to complete your profile'
            });

            return;
        }

        // complete account type
        if (fixtures.includes('user_complete_account_type')) {
            res.status(403).send({
                type: 'accountTypeNotCompleted',
                shortDescription: 'You have to complete your account type'
            });

            return;
        }

        // user without admin role
        if (fixtures.includes('user_not_admin')) {
            fixture = Object.assign({}, fixture, {
                isAdmin: false
            });
        }


        // show a second user
        if (fixtures.includes('user_second')) {
            fixture = Object.assign({}, fixture, {
                id: 2,
                email: 'tester2@test.com',
                userName: 'Tester2',
                permissions: [],
                token: null
            });
        }

        // add an avatar inside the fixture
        if (fixtures.includes('user_with_avatar')) {
            fixture = Object.assign({}, fixture, {
                avatar: readFixtureJsonFile('avatar.json')
            });
        }

        const processedPermissions = [];

        // process the fixture's permissions
        fixture.permissions.forEach(permission => {
            switch(true) {
                // allow to read messages by credits
                case permission.permission == 'mailbox_read_chat_message' && fixtures.includes('user_read_messages_by_credits') :
                    processedPermissions.push(Object.assign({}, permission, {
                        isAuthorized: false,
                        isAllowed: false,
                        isAllowedAfterTracking: true
                    }));

                    break;

                // any promoted actions
                case permission.permission == 'hotlist_add_to_list' && fixtures.includes('user_promoted_hot_list') :
                case permission.permission == 'photo_upload' && fixtures.includes('user_promoted_upload_photos') :
                case permission.permission == 'mailbox_read_chat_message' && fixtures.includes('user_promoted_read_messages') :
                case permission.permission == 'mailbox_reply_to_chat_message' && fixtures.includes('user_promoted_send_messages') :
                    processedPermissions.push(Object.assign({}, permission, {
                        isPromoted: true,
                        isAllowed: false
                    }));

                    break;

                // any blocked actions
                case permission.permission == 'base_search_users' && fixtures.includes('user_search_blocked_search') :
                case permission.permission == 'base_search_users' && fixtures.includes('tinder_blocked_search') :
                case permission.permission == 'hotlist_add_to_list' && fixtures.includes('user_blocked_hot_list') :
                case permission.permission == 'photo_upload' && fixtures.includes('user_block_upload_photos') :
                case permission.permission == 'mailbox_read_chat_message' && fixtures.includes('user_blocked_read_messages') :
                case permission.permission == 'mailbox_reply_to_chat_message' && fixtures.includes('user_blocked_send_messages') :
                case permission.permission == 'mailbox_send_chat_message' && fixtures.includes('user_blocked_send_messages') :
                    processedPermissions.push(Object.assign({}, permission, {
                        isPromoted: false,
                        isAllowed: false
                    }));

                    break;

                default :
                    processedPermissions.push(permission);
            }
        });

        fixture = Object.assign({}, fixture, {
            permissions: processedPermissions
        });

        res.send(fixture);
    });

    // users location
    app.put('/skmobileapp/api/user-locations/me/', function(req, res) {
        // read a basic fixture
        res.send({});
    });

    // users devices
    app.post('/skmobileapp/api/devices/', function(req, res) {
        // read a basic fixture
        res.send({});
    });

    // forgot password (email)
    app.post('/skmobileapp/api/forgot-password/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('forgot.password.json');

        // failed login
        if (fixtures.includes('forgot_email_failed')) {
            fixture = Object.assign({}, fixture, {
                success: false,
                message: 'There is no user with this email address'
            });
        }

        res.send(fixture);
    });

    // forgot password (new passwords)
    app.put('/skmobileapp/api/forgot-password/:code/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('forgot.password.json');

        // a wrong password
        if (fixtures.includes('forgot_password_failed')) {
            fixture = Object.assign({}, fixture, {
                success: false,
                message: 'Password is incorrect'
            });

            return;
        }

        res.send(fixture);
    });

    // verify email
    app.post('/skmobileapp/api/verify-email/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('verify.email.json');

        // a wrong email
        if (fixtures.includes('verify_email_failed')) {
            fixture = Object.assign({}, fixture, {
                success: false,
                message: 'test'
            });
        }

        res.send(fixture);
    });

    // verify email code
    app.post('/skmobileapp/api/validators/verify-email-code/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('validator.json');

        // a wrong code
        if (fixtures.includes('verify_email_code_failed')) {
            fixture = Object.assign({}, fixture, {
                valid: false
            });
        }

        res.send(fixture);
    });

    // forgot password (validate code)
    app.post('/skmobileapp/api/validators/forgot-password-code/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('validator.json');

        // a wrong code
        if (fixtures.includes('forgot_code_failed')) {
            fixture = Object.assign({}, fixture, {
                valid: false
            });
        }

        res.send(fixture);
    });

    // gender list
    app.get('/skmobileapp/api/user-genders/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('genders.json'));
    });

    // validate user name
    app.post('/skmobileapp/api/validators/user-name/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('validator.json');

        // wrong user name
        if (fixtures.includes('validator_username_failed')) {
            fixture = Object.assign({}, fixture, {
                valid: false
            });
        }

        res.send(fixture);
    });

    // validate email
    app.post('/skmobileapp/api/validators/user-email/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('validator.json');

        // wrong email
        if (fixtures.includes('validator_useremail_failed')) {
            fixture = Object.assign({}, fixture, {
                valid: false
            });
        }

        res.send(fixture);
    });

    // upload avatars
    app.post('/skmobileapp/api/avatars/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('join_avatar.json'));
    });

    // update avatar
    app.post('/skmobileapp/api/avatars/me/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('avatar.json');

        // return a pending avatar
        if (fixtures.includes('avatar_upload_pending')) {
            fixture = Object.assign({}, fixture, {
                active: false
            });
        }

        res.send(fixture);
    });

    // delete avatar
    app.delete('/skmobileapp/api/avatars/:id/', function(req, res) {
        res.status(204).send();
    });

    // complete profile questions
    app.get('/skmobileapp/api/complete-profile-questions/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('complete.profile.questions.json'));
    });

    // search questions
    app.get('/skmobileapp/api/search-questions/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('search.questions.json'));
    });

    // join questions
    app.get('/skmobileapp/api/join-questions/:id/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('join.questions.json'));
    });

    // autocomplete
    app.get('/skmobileapp/api/location-autocomplete/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('location.autocomplete.json'));
    });

    // create user
    app.post('/skmobileapp/api/users/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('user.json'));
    });

    // update user
    app.put('/skmobileapp/api/users/:id/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('user.json'));
    });

    // create questions data
    app.post('/skmobileapp/api/questions-data/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('questions.data.json'));
    });

    // update questions data
    app.put('/skmobileapp/api/questions-data/me/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('questions.data.json'));
    });

    // edit questions
    app.get('/skmobileapp/api/edit-questions/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('edit.questions.json'));
    });

    // upload photos
    app.post('/skmobileapp/api/photos/', function(req, res) {
        // read a basic fixture
        let fixture = readFixtureJsonFile('photo.json');

        // return a pending photo
        if (fixtures.includes('photo_upload_pending')) {
            fixture = Object.assign({}, fixture, {
                approved: false
            });
        }

        res.send(fixture);
    });

    // add to the hot list
    app.post('/skmobileapp/api/hotlist-users/me/', function(req, res) {
        // read a basic fixture
        res.send(readFixtureJsonFile('hot.list.json'));
    });

    // remove from the hot list
    app.delete('/skmobileapp/api/hotlist-users/me/', function(req, res) {
        res.status(204).send();
    });

    // app settings email notification questions
    app.get('/skmobileapp/api/email-notifications/questions/', function(req, res) {
      // read a basic fixture
      res.send(readFixtureJsonFile('email.notifications.questions.json'));
    });

    // app settings email notification questions save
    app.put('/skmobileapp/api/email-notifications/me/', function(req, res) {
      res.status(204).send();
    });

    //app settings push notification questions
    app.get('/skmobileapp/api/preferences/questions/skmobileapp_pushes/', function(req, res) {
      // read a basic fixture
      res.send(readFixtureJsonFile('push.notifications.questions.json'));
    });

    // app settings push notification questions save
    app.put('/skmobileapp/api/preferences/me/', function(req, res) {
      res.status(204).send();
    });

    // gdpr request personal data download
    app.post('/skmobileapp/api/gdpr/downloads/', function(req, res) {
      res.status(204).send();
    });

    // gdpr request personal data deletion
    app.post('/skmobileapp/api/gdpr/deletions/', function(req, res) {
      res.status(204).send();
    });

    // gdpr send message to admin
    app.post('/skmobileapp/api/gdpr/messages/', function(req, res) {
      res.status(204).send();
    });

    // account delete
    app.delete('/skmobileapp/api/users/:id/', function(req, res) {
      res.status(204).send();
    });

    // bookmark list
    app.get('/skmobileapp/api/bookmarks/', function(req, res) {
        let users = [];

        // get a predefined bookmark user
        if (fixtures.includes('bookmark_user')) {
            users.push(readFixtureJsonFile('bookmark.json'));
        }

        res.send(users);
    });

    // bookmark delete user
    app.delete('/skmobileapp/api/bookmarks/users/:id/', function(req, res) {
      res.status(204).send();
    });

    // app guests list read
    app.put('/skmobileapp/api/guests/me/mark-all-as-read/', function(req, res) {
      res.status(204).send();
    });

    // guests remove user
    app.delete('/skmobileapp/api/guests/:id/', function(req, res) {
      res.status(204).send();
    });
}

module.exports = appRouter;

/**
 * Read fixture json file
 */
function readFixtureJsonFile(fileName, encoding){
    if (typeof (encoding) == 'undefined'){
        encoding = 'utf8';
    }

    let file = fs.readFileSync(__dirname + '/fixtures/' + fileName, encoding);

    return JSON.parse(file);
}
