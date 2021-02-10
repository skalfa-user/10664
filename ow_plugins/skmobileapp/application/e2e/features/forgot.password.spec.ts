import { Utils } from '../utils';
import {} from 'jasmine';

describe('Forgot password', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('fail check email', async() => {
        await utils.reloadApp([
            'forgot_email_failed'
        ]);

        // click the forgot password button
        await utils.click('sk-fpass');

        // fill the email
        await utils.fillInputByPlaceholder('Type your email', 'test@test.com');

        // click the 'next' button
        await utils.click('sk-forgot-email-button');

        // we should see the error message
        expect(await utils.alert()).toEqual('There is no user with this email address');
    });

    it('fail check code', async() => {
        await utils.reloadApp([
            'forgot_code_failed'
        ]);

        // click the forgot password button
        await utils.click('sk-fpass');

        // fill the email
        await utils.fillInputByPlaceholder('Type your email', 'test@test.com');

        // click the 'next' button
        await utils.click('sk-forgot-email-button');

        // fill the code
        await utils.fillInputByPlaceholder('Type the reset code', 'test');

        // click the 'next' button
        await utils.click('sk-forgot-code-button');

        // we should see the error message
        expect(await utils.alert()).toEqual('Please enter the valid reset code');
    });

    it('successful restore password', async() => {
        await utils.reloadApp();

        // click the forgot password button
        await utils.click('sk-fpass');

        // click the 'next' button (check the form validation)
        await utils.click('sk-forgot-email-button');

        // should see the error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // fill the email
        await utils.fillInputByPlaceholder('Type your email', 'test@test.com');

        // click the 'next' button
        await utils.click('sk-forgot-email-button');

        // click the 'next' button (check the form validation)
        await utils.click('sk-forgot-code-button');

        // should see the error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');
                
        // fill the code
        await utils.fillInputByPlaceholder('Type the reset code', 'test');

        // click the 'next' button
        await utils.click('sk-forgot-code-button');

        // click the 'next' button (check the form validation)
        await utils.click('sk-forgot-new-password-button');

        // should see the error message
        expect(await utils.toaster()).toEqual('Please fill the fields correctly to continue');

        // fill new passwords
        await utils.fillInputByPlaceholder('Type in new password', 'test');
        await utils.fillInputByPlaceholder('Repeat your password', 'test');

        // click the 'next' button
        await utils.click('sk-forgot-new-password-button');

        // wait for the successful message
        expect(await utils.toaster()).toEqual('Your password successfully updated');
    });
});
