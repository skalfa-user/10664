import { Utils } from '../utils';
import { element, by } from 'protractor';
import {} from 'jasmine';

describe('Photos', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('successful upload photo on the main edit page', async() => {
        await utils.reloadApp([
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // upload photo
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-uploader');

        // should see successful uploaded message
        expect(await utils.toaster()).toEqual('Photo has been uploaded');

        // show the uploaded photo
        await utils.click('sk-photo-wrapper', 1);

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successful upload pending photo on the main edit page', async() => {
        await utils.reloadApp([
            'photo_upload_pending'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // upload a photo
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-uploader');

        // should see successful uploaded message
        expect(await utils.toaster()).toEqual('Photo has been uploaded');

        // the photo must be pending
        expect(await utils.waitForElement('sk-photo-pending')).toBe(true);

        // show the uploaded photo
        await utils.click('sk-photo-pending');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successful upload photo on the all photos page', async() => {
        await utils.reloadApp([
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photos actions button
        await utils.click('sk-photo-actions');

        // click the view all button in the actions menu
        await utils.clickActionMenuItem(0);

        // upload photo
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-uploader');

        // should see the successful uploaded message
        expect(await utils.toaster()).toEqual('Photo has been uploaded');

        // // show the uploaded photo
        await utils.click('sk-extra-photo-wrapper', 1);

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successful upload pending photo on the all photos page', async() => {
        await utils.reloadApp([
            'photo_upload_pending'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photos actions button
        await utils.click('sk-photo-actions');

        // click the view all button in the actions menu
        await utils.clickActionMenuItem(0);

        // upload a photo
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-uploader');

        // should see successful uploaded message
        expect(await utils.toaster()).toEqual('Photo has been uploaded');

        // the photo must be pending
        expect(await utils.waitForElement('sk-photo-pending')).toBe(true);

        // show the uploaded photo
        await utils.click('sk-photo-pending');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);

        // there is must be a pending explanation
        expect(element(by.className('sk-photos-approval')).getText()).toEqual('1 photo(s) are not approved. Please wait');
    });

    it('upload photos should be blocked by the promoted membership action on the main edit page', async() => {
        await utils.reloadApp([
            'user_promoted_upload_photos'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photos actions button
        await utils.click('sk-photo-actions');

        // click the upload a photo button in the actions menu
        await utils.clickActionMenuItem(1);

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');
    });

    it('upload photos should be blocked by the promoted membership action on the all photos page', async() => {
        await utils.reloadApp([
            'user_promoted_upload_photos'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photos actions button
        await utils.click('sk-photo-actions');

        // click the view all button in the actions menu
        await utils.clickActionMenuItem(0);

        // click the extra photos actions button
        await utils.click('sk-photos-extra-actions');

        // click the upload a photo button in the actions menu
        await utils.clickActionMenuItem(1);

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');
    });

    it('upload photos should not be visible due to the membership action restriction on the main edit page', async() => {
        await utils.reloadApp([
            'user_block_upload_photos'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photos actions button
        await utils.click('sk-photo-actions');

        expect(utils.findElementByText('.button-inner', 'Upload photo').count()).toBe(0);
    });

    it('upload photos should not be visible due to the membership action restriction on the all photos page', async() => {
        await utils.reloadApp([
            'user_block_upload_photos'
        ], true);

        // click the edit user button
        await utils.click('sk-user-profile');

        // click the photos actions button
        await utils.click('sk-photo-actions');

        // click the view all button in the actions menu
        await utils.clickActionMenuItem(0);

        // click the extra photos actions button
        await utils.click('sk-photos-extra-actions');

        // click the upload a photo button in the actions menu
        await utils.clickActionMenuItem(1);
    });
});
