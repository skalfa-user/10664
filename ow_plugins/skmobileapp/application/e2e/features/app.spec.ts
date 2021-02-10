import { element, by, browser } from 'protractor';
import { Utils } from '../utils';
import {} from 'jasmine';

describe('App', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('the maintenance mode page should be activated', async() => {
        await utils.reloadApp([
            'configs_maintenance'
        ]);

        const pageDesc = await element(by.css('h1')).getText();

        expect(pageDesc).toEqual('Under maintenance.');
    });

    it('logout', async() => {
        await utils.reloadApp([
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click the logout button
        await utils.click('sk-logout-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });

    it('the privacy and terms of use pages', async() => {
        await utils.reloadApp([
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click the privacy button
        await utils.click('sk-privacy-button');

        // waiting for modal window with privacy
        expect(await utils.waitForElement('show-page')).toBe(true);

        // search for some text in privacy policy
        expect(utils.findElementByText('.modal-wrapper', 'Thank you for visiting our website').count()).toBe(1);

        // close the modal window
        await utils.click('sk-custompage-close');

        // click the terms of use button
        await utils.click('sk-termsofuse-button');

        // waiting for modal window with privacy
        expect(await utils.waitForElement('show-page')).toBe(true);

        // search for some text in terms of use
        expect(utils.findElementByText('.modal-wrapper', 'Welcome to our website').count()).toBe(1);
    });

    it('save email settings', async() => {
        await utils.reloadApp([
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click the email button
        await utils.click('sk-email-button');

        // search for some text in email questions setings
        expect(utils.findElementByText('.sk-email-notifications-description', 'Control the emails you want to get').count()).toBe(1);

        // click the done button
        await utils.click('sk-email-notification-done-button');

        // should see the success message
        expect(await utils.toaster()).toEqual('Email settings saved');
    });

    it('save push settings', async() => {
        await utils.reloadApp([
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click the push button
        await utils.click('sk-push-button');

        // search for some text in push questions setings
        expect(utils.findElementByText('.sk-checkbox-question-presentation', 'New Messages').count()).toBe(1);

        // click the done button
        await utils.click('sk-push-notification-done-button');

        // should see the success message
        expect(await utils.toaster()).toEqual('Preferences saved');
    });

    it('gdpr settings yourdata', async() => {
        await utils.reloadApp([
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click the yourdata button
        await utils.click('sk-yourdata-button');

        // search for some text in gdpr questions setings
        expect(utils.findElementByText('.sk-user-data-note', 'You have entrusted us with the following personal data').count()).toBe(1);

       // click the data profile edit button
        await utils.click('sk-gdpr-profile-edit-btn');

        // profile edit should be opened
        expect(await utils.waitForElement('sk-avatar-mask')).toBe(true);

        await utils.waitForElement('back-button');

        // click the back button
        await utils.click('back-button',2);

       // click the data download button
        await utils.click('sk-user-data-download-btn');

        // should see the success message
        expect(await utils.toaster()).toEqual('You have successfully sent a request');

        // click the data deletion button
        await utils.click('sk-user-data-deletion-btn');

        // should see the success message
        expect(await utils.toaster()).toEqual('You have successfully sent a request.');
    });

    it('gdpr settings 3rd party services', async() => {
        await utils.reloadApp([
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click the 3rd party services button
        await utils.click('sk-3rdpartyservice-button');

        // search for some text in gdpr questions setings
        expect(utils.findElementByText('.sk-user-data-note', 'For business, analytical and administrative purposes').count()).toBe(1);

        // click the request manual deletion button
        await utils.click('sk-third-data-deletion-btn');

        // search for some text in 3rd party services page
        expect(utils.findElementByText('.sk-textarea-question-presentation', 'Send message to admin').count()).toBe(1);

        // click send message button
        await utils.click('sk-gdpr-send-message');

        // should see the message about empty field
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // write message
        await utils.fillInputByCssClass('text-input', 'test message');

        // click send message button
        await utils.click('sk-gdpr-send-message');

        // should see the success message
        expect(await utils.toaster()).toEqual('You have successfully sent a request.');
    });

    it('gdpr settings should be absent if the plugin uninstalled', async() => {
        await utils.reloadApp([
          'configs_gdpr_disable'
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

       // must not show gdpr text
        expect(utils.findElementByText('.scroll-content', 'General data protection regulation').count()).toBe(0);
    });

    it('delete the user profile', async() => {
        await utils.reloadApp([
          'user_not_admin'
        ], true);

        // click the settings button
        await utils.click('sk-settings-button');

        // click delete profile button
        await utils.click('sk-delete-button');

        // should see the message about delete account
        expect(utils.findElementByText('.action-sheet-title', 'If you delete your account').count()).toBe(1);

        // click delete account button
        await utils.click('action-sheet-button',0);

        // sign-in form should be opened
        expect(utils.waitForElement('sk-buttons-inline')).toBe(true);
    });

    it('successful complete a profile', async() => {
        await utils.reloadApp([
          'user_complete_profile'
        ], true);

        // the complete profile page should be activated
        expect(utils.findElementByText('.toolbar-title', 'Complete your profile').count()).toBe(1);

        // click the 'done' button (check the form validation)
        await utils.click('sk-complete-profile-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        utils.reloadAppFixtures([
            'disable_user_complete_profile'
        ]);

        // fill the form
        await utils.fillInputByPlaceholder('Test', 'test');

        // save the changes
        await utils.click('sk-complete-profile-button');

        // the user's dashboard should be activated after filling the form
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });

    it('leave the complete profile page', async() => {
        await utils.reloadApp([
          'user_complete_profile'
        ], true);

        // the complete profile page should be activated
        expect(utils.findElementByText('.toolbar-title', 'Complete your profile').count()).toBe(1);

        // leave the page
        await utils.click('sk-leave-complete-profile-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });

    it('successful complete an account type', async() => {
        await utils.reloadApp([
          'user_complete_account_type'
        ], true);

        // the complete account type page should be activated
        expect(utils.findElementByText('.toolbar-title', 'Complete your profile').count()).toBe(1);

        // click the 'done' button (check the form validation)
        await utils.click('sk-complete-account-type-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        utils.reloadAppFixtures([
            'disable_user_complete_account_type'
        ]);

        // select a new account type
        await utils.fillSelectBoxById('accountType', [
            1
        ]);

        // save the changes
        await utils.click('sk-complete-account-type-button');

        // the user's dashboard should be activated after filling the form
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });

    it('leave the complete account type page', async() => {
        await utils.reloadApp([
          'user_complete_account_type'
        ], true);

        // the complete profile page should be activated
        expect(utils.findElementByText('.toolbar-title', 'Complete your profile').count()).toBe(1);

        // leave the page
        await utils.click('sk-leave-complete-account-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });

    it('show the account disapproved page', async() => {
        await utils.reloadApp([
          'user_disapproved'
        ], true);

        // the account disapproved page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Your account is pending approval').count()).toBe(1);

        // now we need the user will be activated
        utils.reloadAppFixtures([
            'disable_user_disapproved'
        ]);

        // try to refresh the page
        await utils.moveElement('sk-blank-state-cont', {
            x: 0,
            y: 1000
        });

        // the user's dashboard should be activated after the page refreshing
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });

    it('leave the account disapproved page', async() => {
        await utils.reloadApp([
          'user_disapproved'
        ], true);

        // the account disapproved page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Your account is pending approval').count()).toBe(1);

        // leave the page
        await utils.click('sk-leave-user-disapproved-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });

    it('successful verify user email code', async() => {
        await utils.reloadApp([
          'user_email_confirmation'
        ], true);

        // the verify user email page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'A verification code was sent to you').count()).toBe(1);

        // now we need the user will be activated
        utils.reloadAppFixtures([
            'disable_user_disapproved'
        ]);

        // fill the form
        await utils.fillInputByPlaceholder('Type the code', 'test');

        // click the 'done' button (check the form validation)
        await utils.click('sk-verify-email-code-button');

        // the user's dashboard should be activated
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });

    it('error verify user email code', async() => {
        await utils.reloadApp([
          'user_email_confirmation',
          'verify_email_code_failed'
        ], true);

        // the verify user email code page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'A verification code was sent to you').count()).toBe(1);

        // fill the form
        await utils.fillInputByPlaceholder('Type the code', 'test');

        // click the 'done' button (check the form validation)
        await utils.click('sk-verify-email-code-button');

        // we should see the error message
        expect(await utils.alert()).toEqual('Please enter the valid verification code');
    });

    it('leave the verify user email code page', async() => {
        await utils.reloadApp([
          'user_email_confirmation'
        ], true);

        // the verify user email page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'A verification code was sent to you').count()).toBe(1);

        // leave the page
        await utils.click('sk-leave-verify-email-code-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });

    it('successful verify user email', async() => {
        await utils.reloadApp([
          'user_email_confirmation'
        ], true);

        // navigate to the email verification code page
        await utils.click('sk-verify-email-page-button');

        // the verify user email page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Please enter your email below').count()).toBe(1);

        // wait before the entered email to be checked
        await utils.waitForElementHidden('sk-question-validation');

        // fill the form
        await utils.fillInputByPlaceholder('Type your email address', '', true);

        // wait before the 'resend' button is active 
        await utils.waitForElementAttributeHidden('sk-verify-email-button', 'disabled');

        // click the 'resend' button (check the form validation)
        await utils.click('sk-verify-email-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // fill the form
        await utils.fillInputByPlaceholder('Type your email address', 'test2@test.com');

        // wait before the 'resend' button is active 
        await utils.waitForElementAttributeHidden('sk-verify-email-button', 'disabled');

        // save a new email
        await utils.click('sk-verify-email-button');

        // should see the form's successful message
        expect(await utils.toaster()).toEqual('Verification mail has been sent to test2@test.com');

        // the verify user email code page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'A verification code was sent to you').count()).toBe(1);
    });

    it('error verify user email', async() => {
        await utils.reloadApp([
          'user_email_confirmation',
          'verify_email_failed'
        ], true);

        // navigate to the email verification code page
        await utils.click('sk-verify-email-page-button');

        // the verify user email page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Please enter your email below').count()).toBe(1);

        // wait before the 'resend' button is active 
        await utils.waitForElementAttributeHidden('sk-verify-email-button', 'disabled');

        // click the 'resend' button (check the form validation)
        await utils.click('sk-verify-email-button');

        // we should see the error message
        expect(await utils.alert()).toEqual('test');
    });

    it('leave the verify user email page', async() => {
        await utils.reloadApp([
          'user_email_confirmation'
        ], true);

        // navigate to the email verification  page
        await utils.click('sk-verify-email-page-button');

        // the verify user email page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Please enter your email below').count()).toBe(1);

        // leave the page
        await utils.click('sk-leave-verify-email-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });

    it('common error page', async() => {
        await utils.reloadApp([
          'user_not_found'
        ], true);

        // should see the error message
        expect(utils.findElementByText('.sk-blank-state-cont', 'Oops. Something went wrong.').count()).toBe(1);

        // now we need any issues
        utils.reloadAppFixtures([
            'disable_user_not_found'
        ]);

        // close the error page
        await utils.click('sk-app-error-ok-button');

        // the user's dashboard should be activated after the page closing
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });

    it('leave the common error page', async() => {
        await utils.reloadApp([
          'user_not_found'
        ], true);

        // leave the page
        await utils.click('sk-leave-app-error-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });
 
    it('show the account suspended page', async() => {
        await utils.reloadApp([
          'user_suspended'
        ], true);

        // the suspended page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Sorry your account is suspended').count()).toBe(1);
        expect(utils.findElementByText('.sk-blank-state-cont', 'Reason: test').count()).toBe(1);

        // now we need the user will be activated
        utils.reloadAppFixtures([
            'disable_user_suspended'
        ]);

        // try to refresh the page
        await utils.moveElement('sk-blank-state-cont', {
            x: 0,
            y: 10
        });

        // the user's dashboard should be activated after the page refreshing
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });

    it('leave the suspended page', async() => {
        await utils.reloadApp([
          'user_suspended'
        ], true);

        // the suspended page should be activated
        expect(utils.findElementByText('.sk-blank-state-cont', 'Sorry your account is suspended').count()).toBe(1);
        expect(utils.findElementByText('.sk-blank-state-cont', 'Reason: test').count()).toBe(1);

        // leave the page
        await utils.click('sk-leave-user-disapproved-button');

        // the login page should be activated
        expect(await utils.waitForElement('sk-fpass')).toBe(true);
    });
});
