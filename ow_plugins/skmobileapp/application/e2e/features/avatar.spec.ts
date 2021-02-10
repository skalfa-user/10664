import { Utils } from '../utils';
import { element, by } from 'protractor';
import {} from 'jasmine';

describe('Avatar', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('successful delete avatar on the main edit page', async() => {
        await utils.reloadApp([
            'user_with_avatar'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // tap on the avatar (show the actions menu)
        await utils.longTap('sk-avatar-mask');

        // click the delete avatar button
        await utils.clickActionMenuItem(1);

        // confirm the avatar deletion 
        await utils.confirmAlert();

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Avatar has been deleted');
    });

    it('successful upload pending avatar on the main edit page', async() => {
        await utils.reloadApp([
            'avatar_upload_pending'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // upload avatar
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-avatar-uploader');

        // should see successful uploaded message
        expect(await utils.toaster()).toEqual('Avatar has been uploaded');

        // avatar must be pending
        expect(await utils.waitForElement('sk-photo-pending')).toBe(true);

        // show the uploaded avatar
        await utils.click('sk-photo-pending');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successful upload avatar on the main edit page', async() => {
        await utils.reloadApp([
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // upload avatar
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-avatar-uploader');

        // should see successful uploaded message
        expect(await utils.toaster()).toEqual('Avatar has been uploaded');

        // show the uploaded avatar
        await utils.click('sk-avatar-mask');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successful delete avatar on the all photos page', async() => {
        await utils.reloadApp([
            'user_with_avatar'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photo actions button
        await utils.click('sk-photo-actions');

        // navigate to the all photos page from the actions menu
        await utils.clickActionMenuItem(0);

        // tap on the avatar (show the actions menu)
        await utils.longTap('sk-extra-avatar-mask');

        // click the delete avatar button
        await utils.clickActionMenuItem(1);

        // confirm the avatar deletion 
        await utils.confirmAlert();

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Avatar has been deleted');
    });
 
    it('successful upload avatar on the all photos page', async() => {
        await utils.reloadApp([
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photo actions button
        await utils.click('sk-photo-actions');

        // navigate to the all photos page from the actions menu
        await utils.clickActionMenuItem(0);

        // upload an avatar again
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-extra-avatar-uploader');

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Avatar has been uploaded');

        // show the uploaded avatar
        await utils.click('sk-extra-avatar-mask');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successful upload pending avatar on the all photos page', async() => {
        await utils.reloadApp([
            'avatar_upload_pending'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photo actions button
        await utils.click('sk-photo-actions');

        // navigate to the all photos page from the actions menu
        await utils.clickActionMenuItem(0);

        // upload an avatar again
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-extra-avatar-uploader');

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Avatar has been uploaded');

        // avatar must be pending
        expect(await utils.waitForElement('sk-photo-pending')).toBe(true);

        // there is must be a pending explanation
        expect(element(by.className('sk-photos-approval')).getText()).toEqual('Avatar is not approved. Please wait');

        // show the uploaded avatar
        await utils.click('sk-photo-pending');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });
});
