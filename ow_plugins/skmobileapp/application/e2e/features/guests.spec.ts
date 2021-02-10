import { element, by } from 'protractor';
import { Utils } from '../utils';
import {} from 'jasmine';


describe('Guests', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('ocsguests plugin is not installed', async() => {
        await utils.reloadApp([
          'configs_ocsguests_disable'
        ], true);

        // the guests section should not be show
        expect(utils.findElementByText('.sk-user-links', 'My guests').count()).toBe(0);
    });

    it('guests not found', async() => {
        await utils.reloadApp([
        ], true);

        // click my guests button
        await utils.click('sk-guests-button');

        // the guests list should be empty
        expect(utils.findElementByText('.sk-blank-state-cont', 'No people found').count()).toBe(1);
    });

    it('guests check user in list', async() => {
        await utils.reloadApp([
          'se_guests'
        ], true);

        // click my guests button
        await utils.click('sk-guests-button');

        // the guests list should not be empty
        expect(utils.findElementByText('.sk-blank-state-cont', 'No people found').count()).toBe(0);

        // click the guests user button
        await utils.click('item-inner',0);

        // user profile should be show
        expect(utils.waitForElement('sk-profile-actions')).toBe(true);

        // click the back button
        await utils.click('sk-slider-back');

        // user profile should not be highlighted
        await utils.waitForElementAttribute('item-block', 'data-status', 'read');
    });

    it('guests like user with a confirmation', async() => {
        await utils.reloadApp([
          'se_guests'
        ], true);

        // the guests user
        await utils.click('sk-guests-button');

        // the guests list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the guests actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the like button should be visible
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(1);

        // click like button
        await utils.click('button-md-green');

        await utils.confirmAlert();

        // recheck the like button inside the guests actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the like button should absent (because we already clicked it later)
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(0);
    });

    it('guests like user without confirmation', async() => {
        await utils.reloadApp([
          'se_guests'
        ], true);

        // the guests user
        await utils.click('sk-guests-button');

        // the guests list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the guests actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the like button should be visible
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(1);

        // we don't need to show a confirmation window
        utils.setValueToLocalStore('user_like_pressed', true);

        // click like button
        await utils.click('button-md-green');

        // recheck the like button inside the guests actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the like button should absent (because we already clicked it later)
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(0);
    });

    it('guests send message', async() => {
        await utils.reloadApp([
          'se_guests'
        ], true);

        // click guests button
        await utils.click('sk-guests-button');

        // the guests list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the guests actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the send messasge button should be presented
        expect(utils.findElementByText('.button-inner', 'Send message').count()).toBe(1);

        // click send message button
        await utils.click('button-md-primary');

        // the chat window should be activated
        expect(utils.waitForElement('sk-messages-page')).toBe(true);
      });

      it('remove from list', async() => {
          await utils.reloadApp([
            'se_guests'
          ], true);

          // click guests button
          await utils.click('sk-guests-button');

          // the guests list should not be empty
          expect(utils.waitForElement('sk-userlist')).toBe(true);

          // show the guests actions
          await utils.moveElement('item', {
              x: -1000,
              y: 0
            });

          // click remove button
          await utils.click('button-md-secondary');

          // remove confirmation
          await utils.confirmAlert();

          // should see the success message
          expect(await utils.toaster()).toEqual('Profile removed from guests');
        });

});
