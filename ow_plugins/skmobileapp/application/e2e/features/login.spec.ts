import { browser, by } from 'protractor';
import { Utils } from '../utils';
import {} from 'jasmine';

describe('Login', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('successful login', async() => {
        await utils.reloadApp();

        // fill the login form
        await utils.fillInputByPlaceholder('Username/Email', 'tester');
        await utils.fillInputByPlaceholder('Password', 'tester');

        // click the submit button
        await utils.click('sk-login');

        const userName = await browser.findElement(by.className('sk-name'));

        // we should see the logged user's name
        expect(userName.getText()).toEqual('Tester');
    });

    it('error login', async() => {
        await utils.reloadApp([
            'login_failed'
        ]);

        // fill the login form
        await utils.fillInputByPlaceholder('Username/Email', 'tester');
        await utils.fillInputByPlaceholder('Password', 'tester');

        // click the submit button
        await utils.click('sk-login');

        expect(await utils.alert()).toEqual('Invalid username or email');
    });
});
