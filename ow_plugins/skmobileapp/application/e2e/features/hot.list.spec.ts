import { Utils } from '../utils';
import {} from 'jasmine';

describe('Hot list', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('successful join in the hot list', async() => {
        await utils.reloadApp([
        ], true);

        // navigate to the the hot list
        await utils.click('sk-tab-toggle-hot-list');

        // click the join button
        await utils.click('sk-hot-list-button');

        // check the user inside one
        expect(await utils.waitForElement('sk-card-list')).toBe(true);
    });

    it('successful delete from the hot list', async() => {
        await utils.reloadApp([
            'se_hot_list'
        ], true);

        // navigate to the the hot list
        await utils.click('sk-tab-toggle-hot-list');

        // click the delete from hot list button
        await utils.click('sk-hot-list-button');

        // confirm the user deletion 
        await utils.confirmAlert();

        // hot list should be empty
        expect(await utils.waitForElement('sk-nothing-found')).toBe(true);
    });

    it('joining to the hot lists should be blocked by the promoted membership action', async() => {
        await utils.reloadApp([
            'user_promoted_hot_list'
        ], true);

        // navigate to the the hot list
        await utils.click('sk-tab-toggle-hot-list');

        // click the join to hot list button
        await utils.click('sk-hot-list-button');

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');
    });

    it('joining button should not be visible due to the membership action restriction', async() => {
        await utils.reloadApp([
            'user_blocked_hot_list'
        ], true);

        // navigate to the the hot list
        await utils.click('sk-tab-toggle-hot-list');

        expect(utils.findElementByText('.sk-hot-list-button', 'Are you hot too?').count()).toBe(0);
    });

    it('the top navigation hotlist button should not be visible if the plugin is not installed', async() => {
        await utils.reloadApp([
            'configs_hotlist_disable'
        ], true);

        expect(await utils.waitForElementHidden('sk-tab-toggle-hot-list')).toBe(true);
    });
});
