import { Utils } from '../utils';
import { by, browser } from 'protractor';
import {} from 'jasmine';

describe('Join', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('username validate should return error', async() => {
        await utils.reloadApp([
            'validator_username_failed'
        ]);

        // click the join button
        await utils.click('sk-signup');

        // fill the username
        await utils.fillInputByPlaceholder('Type your username', 'test');

        // wait for the question error bubble
        expect(await utils.waitForElement('sk-question-error')).toBe(true);
    });

    it('email validate should return error', async() => {
        await utils.reloadApp([
            'validator_useremail_failed'
        ]);

        // click the join button
        await utils.click('sk-signup');

        // fill the username
        await utils.fillInputByPlaceholder('Type your email', 'test@test.com');

        // wait for the question error bubble
        expect(await utils.waitForElement('sk-question-error')).toBe(true);
    });

    it('password validate should return error', async() => {
        await utils.reloadApp([
        ]);

        // click the join button
        await utils.click('sk-signup');

        // fill the form
        await utils.fillInputByPlaceholder('Type the password', 'test');
        await utils.fillInputByPlaceholder('Repeat your password', 'test2');

        // wait for the question error bubble
        expect(await utils.waitForElement('sk-question-error')).toBe(true);
    });

    it('successful join', async() => {
        await utils.reloadApp([
            'configs_require_avatar'
        ]);

        // click the join button
        await utils.click('sk-signup');

        // click the 'next' button (check the form validation)
        await utils.click('sk-join-initial-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // fill the join initial form
        await utils.fillInputByPlaceholder('Type your username', 'test');
        await utils.fillInputByPlaceholder('Type your email', 'test@test.com');
        await utils.fillInputByPlaceholder('Type the password', 'test');
        await utils.fillInputByPlaceholder('Repeat your password', 'test');

        // select the gender
        await utils.fillSelectBoxById('sex', [
            0
        ]);

        // select the looking for
        await utils.fillSelectBoxById('lookingFor', [
            0,
            1
        ]);

        // click the 'next' button (check the form validation - we have to select avatar)
        await utils.click('sk-join-initial-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please choose an avatar');

        // upload avatar
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-avatar-uploader');

        // wait until image will be uploaded
        expect(await utils.waitForElementHidden('sk-add-avatar-icon')).toBe(true);

        // go to the next page
        await utils.click('sk-join-initial-button');

        // check the page's validation
        await utils.click('sk-join-questions-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // fill the join questions form
        await utils.fillInputByPlaceholder('Real name', 'test');
        await utils.fillDateBoxById('birthdate', 0, 0, 0);
        await utils.fillInputByPlaceholder('About me', 'test');
        await utils.fillGoogleLocationBoxById('googlemap_location', 'test');

        // wait before the 'done' button is active 
        await utils.waitForElementAttributeHidden('sk-join-questions-button', 'disabled');

        // create a profile
        await utils.click('sk-join-questions-button');

        // check the created profile
        expect(await utils.waitForElement('sk-name')).toBe(true);
        expect(browser.findElement(by.className('sk-name')).getText()).toEqual('Tester');
    });
});
