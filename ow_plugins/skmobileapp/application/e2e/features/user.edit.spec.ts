import { Utils } from '../utils';
import {} from 'jasmine';

describe('Edit', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('email validate should return error', async() => {
        await utils.reloadApp([
            'validator_useremail_failed'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // fill the username
        await utils.fillInputByPlaceholder('Email', 'test@test.com');

        // wait for the question error bubble
        expect(await utils.waitForElement('sk-question-error')).toBe(true);
    });

    it('successful edit profile', async() => {
        await utils.reloadApp([
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the 'next' button (check the form validation)
        await utils.click('sk-edit-questions-button');

        // should see the form's error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // fill the edit form
        await utils.fillInputByPlaceholder('Real name', 'test');
        await utils.fillDateBoxById('birthdate', 0, 0, 0);
        await utils.fillInputByPlaceholder('About me', 'test');
        await utils.fillGoogleLocationBoxById('googlemap_location', 'test');
        await utils.fillInputByPlaceholder('Email', 'test@test.com');

        // wait before the 'done' button is active 
        await utils.waitForElementAttributeHidden('sk-edit-questions-button', 'disabled');

        // save changes
        await utils.click('sk-edit-questions-button');

        // should see successful message
        expect(await utils.toaster()).toEqual('Profile has been updated');
    });
});
