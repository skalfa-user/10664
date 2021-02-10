import { Utils } from '../utils';
import {} from 'jasmine';

describe('Tinder search', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('the top navigation tinder search button should not be visible if it deactivated in settings', async() => {
        await utils.reloadApp([
            'configs_tinder_disabled'
        ], true);

        expect(await utils.waitForElementHidden('sk-tab-toggle-tinder')).toBe(true);
    });

    it('tinder search should not work without defined location', async() => {
        await utils.reloadApp([
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        // search for the location error message
        expect(utils.findElementByText('.sk-blank-state-cont', 'Location issue!').count()).toBe(1);
    });

    it('successful moving tinder cards to the left (dislike) and to the right (like) with a confirmation', async() => {
        await utils.reloadApp([
            'tinder_user_list'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // now we don't need any users to be loaded again
        utils.reloadAppFixtures([
            'tinder_empty'
        ]);

        // dislike the tinder card
        await utils.moveElement('sk-item-card', {
            x: -1000,
            y: 0
        });

        await utils.confirmAlert();

        // like the tinder card
        await utils.moveElement('sk-item-card', {
            x: 1000,
            y: 0
        });

        await utils.confirmAlert();

        // all the tinder cards should be absent
        expect(utils.findElementByText('.sk-tinder-no-matches', 'You\'ve run out of matches!').count()).toBe(1);
    });

    it('successful moving tinder cards to the left (dislike) and to the right (like) without a confirmation', async() => {
        await utils.reloadApp([
            'tinder_user_list'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // now we don't need any users to be loaded again
        utils.reloadAppFixtures([
            'tinder_empty'
        ]);

        // we don't need to show a confirmation window
        utils.setValueToLocalStore('user_dislike_pressed', true);
        utils.setValueToLocalStore('user_like_pressed', true);

        // dislike the tinder card
        await utils.moveElement('sk-item-card', {
            x: -1000,
            y: 0
        });

        // like the tinder card
        await utils.moveElement('sk-item-card', {
            x: 1000,
            y: 0
        });

        // all the tinder cards should be absent
        expect(utils.findElementByText('.sk-tinder-no-matches', 'You\'ve run out of matches!').count()).toBe(1);
    });

    it('tinder search blocked due to the membership action restriction', async() => {
        await utils.reloadApp([
            'tinder_blocked_search'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // click the upgrade button
        await utils.click('sk-tinder-upgrade');

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');
    });

    it('moving tinder cards to the top and to the bottom should not be counted as like or dislike', async() => {
        await utils.reloadApp([
            'tinder_user'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // we don't need to show a confirmation window
        utils.setValueToLocalStore('user_dislike_pressed', true);
        utils.setValueToLocalStore('user_like_pressed', true);

        // now we don't need any users to be loaded again
        utils.reloadAppFixtures([
            'tinder_empty'
        ]);

        // move the tinder card to the top position
        await utils.moveElement('sk-item-card', {
            x: 0,
            y: -1000
        });

        // tinder card should be returned back
        expect(await utils.waitForElement('sk-item-card')).toBe(true);

        // move the tinder card to the bottom position
        await utils.moveElement('sk-item-card', {
            x: 0,
            y: 1000
        });

        // tinder card should be returned back
        expect(await utils.waitForElement('sk-item-card')).toBe(true);
    });

    it('successful click the like and dislike buttons on the tinder cards with a confirmation', async() => {
        await utils.reloadApp([
            'tinder_user_list'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // now we don't need any users to be loaded again
        utils.reloadAppFixtures([
            'tinder_empty'
        ]);


        // click the like button
        await utils.click('sk-tinder-like-btn');

        await utils.confirmAlert();

        // click the dislike button
        await utils.click('sk-tinder-dislike-btn');

        await utils.confirmAlert();

        // all the tinder cards should be absent
        expect(utils.findElementByText('.sk-tinder-no-matches', 'You\'ve run out of matches!').count()).toBe(1);
    });

    it('successful click the like and dislike buttons on the tinder cards without a confirmation', async() => {
        await utils.reloadApp([
            'tinder_user_list'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // now we don't need any users to be loaded again
        utils.reloadAppFixtures([
            'tinder_empty'
        ]);

        // we don't need to show a confirmation window
        utils.setValueToLocalStore('user_dislike_pressed', true);
        utils.setValueToLocalStore('user_like_pressed', true);

        // click the like button
        await utils.click('sk-tinder-like-btn');

        // click the dislike button
        await utils.click('sk-tinder-dislike-btn');

        // all the tinder cards should be absent
        expect(utils.findElementByText('.sk-tinder-no-matches', 'You\'ve run out of matches!').count()).toBe(1);
    });

    it('show / hide extra tinder cards info', async() => {
        await utils.reloadApp([
            'tinder_user'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // now we don't need any users to be loaded again
        utils.reloadAppFixtures([
            'tinder_empty'
        ]);

        // show extended user info
        await utils.click('sk-tinder-info-btn');

        // the tinder card extended info should be available
        expect(await utils.waitForElement('sk-tinder-cards-profile-info')).toBe(true);

        // close extended user info
        await utils.click('sk-tinder-info-btn');

        // the tinder card extended info should be hidden
        expect(await utils.waitForElementHidden('sk-tinder-cards-profile-info')).toBe(true);
    });

    it('tinder cards profile preview', async() => {
        await utils.reloadApp([
            'tinder_user'
        ], true);

        // navigate to the the tinder search
        await utils.click('sk-tab-toggle-tinder');

        utils.initAppGeoLocation();

        // check location
        await utils.click('sk-check-location-button');

        // we need to be loaded a different user
        utils.reloadAppFixtures([
            'user_second'
        ]);

        // show a detailed profile data
        await utils.click('sk-item-card');

        // the profile view page should be activated
        expect(await utils.waitForElement('sk-profile-view-page')).toBe(true);

        // return back to the tinder cards
        await utils.click('sk-slider-back');

        // show a detailed profile data using the extra preview profile button
        await utils.click('sk-tinder-profile-btn');

        // the profile view page should be activated
        expect(await utils.waitForElement('sk-profile-view-page')).toBe(true);
    });
});
