import { Utils } from '../utils';
import {} from 'jasmine';

describe('User search', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('the top navigation search button should not be visible if it deactivated in settings', async() => {
        await utils.reloadApp([
            'configs_search_disabled'
        ], true);

        expect(await utils.waitForElementHidden('sk-tab-toggle-search')).toBe(true);
    });

    it('user search blocked due to the membership action restriction', async() => {
        await utils.reloadApp([
            'user_search_blocked_search'
        ], true);

        // navigate to the the user search
        await utils.click('sk-tab-toggle-search');

        // click the upgrade button
        await utils.click('sk-search-upgrade');

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');
    });

    it('user search by nickname should be hidden due to settings', async() => {
        await utils.reloadApp([
            'configs_search_by_username_disabled'
        ], true);

        // navigate to the the user search
        await utils.click('sk-tab-toggle-search');

        // the search area should be hidden
        expect(await utils.waitForElement('sk-filter-only')).toBe(true);
    });

    it('user search by nickname should return an empty result', async() => {
        await utils.reloadApp([
        ], true);

        // navigate to the user search
        await utils.click('sk-tab-toggle-search');

        // trying to find non existing users
        await utils.fillInputByPlaceholder('Username', 'test');

        await utils.pressEnterKey();

        // user list should be empty
        expect(utils.findElementByText('.sk-blank-state-cont', 'No people found').count()).toBe(1);
    });

    it('user search by filters should return a user list', async() => {
        await utils.reloadApp([
        ], true);

        // navigate to the the user search
        await utils.click('sk-tab-toggle-search');

        // navigate to the filters
        await utils.click('sk-search-filter');

        // select online users
        await utils.click('toggle', 0);

        // select users with photos
        await utils.click('toggle', 1);

        // select the relationship
        await utils.fillSelectBoxById('relationship', [
            0,
            1
        ]);

        // we need a user list after the filters filled 
        utils.reloadAppFixtures([
            'user_search_list'
        ]);

        // search users
        await utils.click('sk-search-button');

        // user list should be loaded
        expect(await utils.waitForElementHidden('sk-nothing-found')).toBe(true);
    });

    it('user search profile preview', async() => {
        await utils.reloadApp([
            'user_search_second_user'
        ], true);

        // navigate to the the user search
        await utils.click('sk-tab-toggle-search');

        // we need a different user to view after user card clicked 
        utils.reloadAppFixtures([
            'user_second'
        ]);

        // show a detailed profile data
        await utils.click('sk-card');

        // the profile view page should be activated
        expect(await utils.waitForElement('sk-profile-view-page')).toBe(true);
    });
});
