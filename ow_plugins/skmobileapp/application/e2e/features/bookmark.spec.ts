import { Utils } from '../utils';
import {} from 'jasmine';

describe('Bookmark', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('bookmark plugin is not installed', async() => {
        await utils.reloadApp([
          'configs_bookmarks_disable'
        ], true);

        // the bookmark section should not be show
        expect(utils.findElementByText('.sk-user-links', 'Bookmarks').count()).toBe(0);
    });

    it('bookmark not found', async() => {
        await utils.reloadApp([
        ], true);

        // click bookmark button
        await utils.click('sk-bookmark-button');

        // the bookmark list should be empty
        expect(utils.findElementByText('.sk-blank-state-cont', 'No people found').count()).toBe(1);
    });

    it('bookmark like user with a confirmation', async() => {
        await utils.reloadApp([
          'bookmark_user'
        ], true);

        // click bookmark button
        await utils.click('sk-bookmark-button');

        // the bookmark list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the bookmark actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the like button should be visible
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(1);

        // click like button
        await utils.click('button-md-green');

        await utils.confirmAlert();

        // recheck the like button inside the bookmark actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the like button should absent (because we already clicked it later)
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(0);
    });

    it('bookmark like user without confirmation', async() => {
        await utils.reloadApp([
          'bookmark_user'
        ], true);

        // click bookmark button
        await utils.click('sk-bookmark-button');

        // the bookmark list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the bookmark actions
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

        // recheck the like button inside the bookmark actions
        await utils.moveElement('item', {
            x: 0,
            y: -1000
        });

        // the like button should absent (because we already clicked it later)
        expect(utils.findElementByText('.button-inner', 'Like').count()).toBe(0);
      });

    it('bookmark send message', async() => {
        await utils.reloadApp([
          'bookmark_user'
        ], true);

        // click bookmark button
        await utils.click('sk-bookmark-button');

        // the bookmark list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the bookmark actions
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

    it('remove from bookmarks', async() => {
        await utils.reloadApp([
          'bookmark_user'
        ], true);

        // click bookmark button
        await utils.click('sk-bookmark-button');

        // the bookmark list should not be empty
        expect(utils.waitForElement('sk-userlist')).toBe(true);

        // show the bookmark actions
        await utils.moveElement('item', {
            x: -1000,
            y: 0
        });

        // the unmark button should be presented
        expect(utils.findElementByText('.button-inner', 'Unmark').count()).toBe(1);

        // click unmark button
        await utils.click('button-md-secondary');

        // unmark confirmation
        await utils.confirmAlert();

        // should see the success message
        expect(await utils.toaster()).toEqual('Profile removed from bookmarks');
      });
});
