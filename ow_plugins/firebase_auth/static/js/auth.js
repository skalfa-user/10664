function FireBaseAuth(options)
{
    /**
     * Auth options
     *
     * @var {object}
     */
    var authOptions =
    {
        authWrapperSelector: '',
        basicButtonWrapSelector: '',
        isLongProviderList: false,
        authenticateUrl: '',
        backUri: '',
        inviteCode: '',
        twitterProvider: '',
        facebookProvider: '',
        googleProvider: '',
        linkedInProvider: '',
        instagramProvider: '',
        authDomain: '',
        displayCustomTokenUrl: '',
        messages: {
            errorOccurred: '',
            errorAccountExists: ''
        }
    };

    // extend options
    authOptions = $.extend({}, authOptions, options);

    var customAuthProviderTimer = '';

    /**
     * Init
     */
    var init = function() {
        // show / hide extra auth buttons 
        if (authOptions.isLongProviderList) {
            $(authOptions.authWrapperSelector).hover(function() {
                $(this).find(authOptions.basicButtonWrapSelector).fadeIn();
            }, function() {
                $(this).find(authOptions.basicButtonWrapSelector).fadeOut();
            });
        }

        // show a provider login popup
        $(authOptions.connectButtonSelector).click(function() {
            var providerInstance = null;
            var providerId = $(this).data('provider');

            switch(providerId) {
                case authOptions.twitterProvider :
                    providerInstance = new firebase.auth.TwitterAuthProvider();
                    break;

                case authOptions.facebookProvider :
                    providerInstance = new firebase.auth.FacebookAuthProvider();
                    break;

                case authOptions.googleProvider :
                    providerInstance = new firebase.auth.GoogleAuthProvider();
                    providerInstance.addScope('profile');
                    providerInstance.addScope('email');
                    break;

                case authOptions.linkedInProvider :
                case authOptions.instagramProvider :
                    return showCustomAuthProvider(providerId);

                default:
                    throw new TypeError('Unsupported provider ' + providerId);
            }

            // login user on the site
            firebase.auth().signInWithPopup(providerInstance).then(function(response) {
                login(response, providerId);
            }).catch(function(error) {
                switch(error.code) {
                    // process account exists with different credential error
                    case 'auth/account-exists-with-different-credential' :
                        firebase.auth().fetchSignInMethodsForEmail(error.email).then(signInMethods => {
                            if (signInMethods && signInMethods.length) {
                                OW.error(authOptions.messages.errorAccountExists + signInMethods[0]);
                            }
                        });
                        break;

                    // ignore some kind of errors
                    case 'auth/popup-closed-by-user' :
                    case 'auth/cancelled-popup-request' : 
                        break;

                    default :
                        OW.error(authOptions.messages.errorOccurred);
                }
            });
        });
    }
 
    /**
     * Show custom provider
     */
    var showCustomAuthProvider = function(provider) {
        if (customAuthProviderTimer) {
            clearInterval(customAuthProviderTimer);
        }

        var firebaseProjectId = authOptions.authDomain.split('.');
        var providerName = provider.split('.');
        var authLink = 'https://us-central1-' + firebaseProjectId[0] + '.cloudfunctions.net/' + providerName[0] + 'Redirect?backUrl=' + authOptions.displayCustomTokenUrl;

        var providerWindow = window.open(authLink, 'firebaseAuthCustom', 'width=515,height=680,top=260,left=702,location=1,resizable=1,statusbar=1,toolbar=0');

        // try to find a custom token
        customAuthProviderTimer = setInterval(function() {
            // stop the ping
            if (providerWindow.closed) {
                clearInterval(customAuthProviderTimer);

                return;
            }

            try {
                var token = providerWindow.document.getElementById("firebaseToken").innerText;

                if (token) {
                    clearInterval(customAuthProviderTimer);
                    providerWindow.close();

                    // login user
                    if (token != '__empty__') {
                        firebase.auth().signInWithCustomToken(token).then(function(response){
                            login(response, provider);
                        }).catch(function() {
                            OW.error(authOptions.messages.errorOccurred);
                        });
                    }
                }
            }
            catch(error) {}
        }, 1000);
    }

    /**
     * Login
     */
    var login = function(response, providerId) {
        // redirect page
        OW.postRequest(authOptions.authenticateUrl, {
            backUri: authOptions.backUri,
            inviteCode: authOptions.inviteCode,
            displayName: response.user.displayName,
            email: response.user.email ? response.user.email : (response.user.providerData && 
                response.user.providerData.length && response.user.providerData[0].email ? response.user.providerData[0].email : ''),
            phoneNumber: response.user.phoneNumber,
            photoURL: response.user.photoURL,
            providerId: providerId,
            uid: response.user.uid
        });
    }

    // init auth
    init();
}
