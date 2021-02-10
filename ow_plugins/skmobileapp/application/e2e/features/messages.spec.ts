import { Utils } from '../utils';
import {} from 'jasmine';

describe('Messages', () => {
    let utils: Utils;

    beforeEach(() => {
        utils = new Utils;
    });

    afterEach(() => {
        utils.cleanBrowser();
    });

    it('search for conversations', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // trying to find non existing users
        await utils.fillInputByPlaceholder('Username', 'Non existing user');

        // conversations should be empty
        expect(utils.findElementByText('.sk-blank-state-cont', 'No results found').count()).toBe(1);
    });

    it('block /unblock conversations', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // tap on the conversation (show the actions menu)
        await utils.longTap('sk-conversation-item');

        // click the block conversation button
        await utils.clickActionMenuItem(0);

        // confirm the conversation blocking 
        await utils.confirmAlert();

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Profile blocked');

        // wait before the toaster message is active
        await utils.waitForElementHidden('toast-message');

        // tap on the conversation (show the actions menu)
        await utils.longTap('sk-conversation-item');

        // click the unblock conversation button
        await utils.clickActionMenuItem(0);

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Profile unblocked');
    });

    it('delete conversations', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // tap on the conversation (show the actions menu)
        await utils.longTap('sk-conversation-item');

        // click the delete conversation button
        await utils.clickActionMenuItem(1);

        // confirm the conversation deletion 
        await utils.confirmAlert();

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Conversation has been deleted');

        // conversations should be empty
        expect(utils.findElementByText('.sk-blank-state-cont p', 'First, let\'s find someone you like!').count()).toBe(1);
    });

    it('mark as read / unread conversations', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // tap on the conversation (show the actions menu)
        await utils.longTap('sk-conversation-item');

        // click the mark as unread conversation button
        await utils.clickActionMenuItem(2);

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Conversation has been marked as unread');

        // the conversations should be marked as new
        expect(await utils.waitForElement('sk-conversation-item-new')).toBe(true);

        // the conversations tab should have a badge about new messages
        expect(await utils.waitForElement('sk-conversation-notification')).toBe(true);

        // wait before the toaster message is active
        await utils.waitForElementHidden('toast-message');

        // tap on the conversation (show the actions menu)
        await utils.longTap('sk-conversation-item');

        // click the mark as read conversation button
        await utils.clickActionMenuItem(2);

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Conversation has been marked as read');

        // the conversations should be marked as read
        expect(await utils.waitForElementHidden('sk-conversation-item-new')).toBe(true);

        // the conversations tab should not have a badge about new messages
        expect(await utils.waitForElementHidden('sk-conversation-notification')).toBe(true);
    });

    it('block /unblock conversation on the chat page', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // show the conversation actions
        await utils.click('sk-conversation-actions');

        // click the block conversation button
        await utils.clickActionMenuItem(0);

        // confirm the conversation blocking 
        await utils.confirmAlert();

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Profile blocked');

        // wait before the toaster message is active
        await utils.waitForElementHidden('toast-message');

        // show the conversation actions
        await utils.click('sk-conversation-actions');

        // click the unblock conversation button
        await utils.clickActionMenuItem(0);

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Profile unblocked');
    });

    it('delete conversation on the chat page', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // show the conversation actions
        await utils.click('sk-conversation-actions');

        // click the delete conversation button
        await utils.clickActionMenuItem(1);

        // confirm the conversation deletion 
        await utils.confirmAlert();

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Conversation has been deleted');

        // conversations should be empty
        expect(utils.findElementByText('.sk-blank-state-cont p', 'First, let\'s find someone you like!').count()).toBe(1);
    });

    it('mark as unread conversations on the chat page', async() => {
        await utils.reloadApp([
            'se_conversations'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // show the conversation actions
        await utils.click('sk-conversation-actions');

        // click the mark as unread conversation button
        await utils.clickActionMenuItem(2);

        // should see the toaster message
        expect(await utils.toaster()).toEqual('Conversation has been marked as unread');

        // the conversations should be marked as new
        expect(await utils.waitForElement('sk-conversation-item-new')).toBe(true);

        // the conversations tab should have a badge about new messages
        expect(await utils.waitForElement('sk-conversation-notification')).toBe(true);
    });

    it('send messages should be blocked by the promoted membership action', async() => {
        await utils.reloadApp([
            'se_conversations',
            'user_promoted_send_messages'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // click the attach a file button
        await utils.click('sk-messages-footer-attach');

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');

        // the entering a message area should be disabled
        expect(await utils.waitForElement('sk-messages-footer-promoted')).toBe(true);
    });

    it('send messages should not be visible due to the membership action restriction', async() => {
        await utils.reloadApp([
            'se_conversations',
            'user_blocked_send_messages'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // the send message button should absent
        expect(utils.findElementByText('.sk-send-message-button', 'Send').count()).toBe(0);
    });

    it('successfully send a text message', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // enter a text message
        await utils.fillInputByCssClass('text-input', 'test2');

        // send the message
        await utils.click('sk-send-message-button');

        // the message should be delivered
        expect(await utils.waitForElement('sk-message-received-icon')).toBe(true);
    });

    it('successfully resend a text message', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message',
            'message_create_failed'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // enter a text message
        await utils.fillInputByCssClass('text-input', 'test2');

        // send the message
        await utils.click('sk-send-message-button');

        // the message should be delivered with an error
        expect(await utils.waitForElement('sk-message-deliver-error')).toBe(true);

        // click on the error message
        await utils.click('sk-message-deliver-error');

        // no we should successfully resend message
        utils.reloadAppFixtures([
            'se_conversations',
            'message'
        ]);

        // click the resend message button
        await utils.clickActionMenuItem(1);

        // the message should be delivered
        expect(await utils.waitForElement('sk-message-received-icon')).toBe(true);
    });

    it('successfully delete a text message', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message',
            'message_create_failed'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // enter a text message
        await utils.fillInputByCssClass('text-input', 'test2');

        // send the message
        await utils.click('sk-send-message-button');

        // the message should be delivered with an error
        expect(await utils.waitForElement('sk-message-deliver-error')).toBe(true);

        // click on the error message
        await utils.click('sk-message-deliver-error');

        // click the delete message button
        await utils.clickActionMenuItem(0);

        // confirm the message deleting 
        await utils.confirmAlert();

        // the message should be deleted
        expect(utils.findElementByText('.sk-message-body', 'test2').count()).toBe(0);
    });

    it('successfully send a photo message', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // upload image
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-message-uploader');

        // the message should be delivered
        expect(await utils.waitForElement('sk-message-received-icon')).toBe(true);

        // show the uploaded image
        await utils.click('sk-attachment-img');

        // photos viewer should be activated
        expect(await utils.waitForElement('sk-photos-viewer')).toBe(true);
    });

    it('successfully resend a photo message', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message',
            'photo_message_create_failed'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // upload image
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-message-uploader');

        // the message should be delivered with an error
        expect(await utils.waitForElement('sk-message-deliver-error')).toBe(true);

        // click on the error message
        await utils.click('sk-message-deliver-error');

        // no we should successfully resend message
        utils.reloadAppFixtures([
            'se_conversations',
            'message'
        ]);

        // click the resend message button
        await utils.clickActionMenuItem(1);

        // the message should be delivered
        expect(await utils.waitForElement('sk-message-received-icon')).toBe(true);
    });

    it('successfully delete a photo message', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message',
            'photo_message_create_failed'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // upload image
        await utils.uploadFileFromStatic('avatar.jpg', 'sk-photo-message-uploader');

        // the message should be delivered with an error
        expect(await utils.waitForElement('sk-message-deliver-error')).toBe(true);

        // click on the error message
        await utils.click('sk-message-deliver-error');

        // click the delete message button
        await utils.clickActionMenuItem(0);

        // confirm the message deleting 
        await utils.confirmAlert();

        // the message should be deleted
        expect(await utils.waitForElementHidden('sk-attachment-img')).toBe(true);
    });

    it('successfully reading a text message protected by credits', async() => {
        await utils.reloadApp([
            'se_conversations',
            'user_read_messages_by_credits',
            'message_protected'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // click the read a message button
        await utils.click('sk-read-message-button');

        // the read message button should be deleted
        expect(await utils.waitForElementHidden('sk-read-message-button')).toBe(true);

        // the message should be updated
        expect(utils.findElementByText('.sk-message-body', 'test').count()).toBe(1);
    });

    it('successfully reading a photo message protected by credits', async() => {
        await utils.reloadApp([
            'se_conversations',
            'user_read_messages_by_credits',
            'message_photo_protected'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // click the read a message button
        await utils.click('sk-read-message-button');

        // the read message button should be deleted
        expect(await utils.waitForElementHidden('sk-read-message-button')).toBe(true);

        // the message with a photo should be updated
        expect(await utils.waitForElement('sk-attachments-wrap')).toBe(true);
    });

    it('reading messages should be blocked by the promoted membership action', async() => {
        await utils.reloadApp([
            'se_conversations',
            'user_promoted_read_messages',
            'message_photo_protected'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // click the read a message button
        await utils.click('sk-read-message-button');

        // we should see the permission error message
        expect(await utils.alertTitle()).toEqual('You don\'t have permissions');
    });

    it('reading messages should be blocked due to the membership action restriction', async() => {
        await utils.reloadApp([
            'se_conversations',
            'user_blocked_read_messages',
            'message_photo_protected'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // reading the message button should absent
        expect(await utils.waitForElementHidden('sk-read-message-button')).toBe(true);

        // there is should be a permission denied text
        expect(utils.findElementByText('.sk-message-permisson', 'You do not have enough permissions to read this message').count()).toBe(1);
    });

    it('loading the chat history', async() => {
        await utils.reloadApp([
            'se_conversations',
            'message_list',
            'message_history'
        ], true);

        // navigate to the conversations
        await utils.click('sk-tabs-conversations');

        // navigate to the chat page
        await utils.click('sk-conversation-item');

        // scroll messages to the top (load messages from the messages history)
        await utils.scrollArea('sk-messages', 'top');

        // there is should be a loaded history message
        expect(await utils.waitForElement('sk-message-received')).toBe(true);

        // there is should be new messages counter
        expect(await utils.waitForElement('sk-messages-down-counter')).toBe(true);

        // the scroll down arrow should be activated 
        expect(await utils.waitForElement('sk-messages-down')).toBe(true);

        // scroll all messages down
        await utils.click('sk-messages-down');

        // the scroll down arrow should be hidden 
        expect(await utils.waitForElementHidden('sk-messages-down')).toBe(true);

        // the new messages counter should be hidden 
        expect(await utils.waitForElementHidden('sk-messages-down-counter')).toBe(true);
    });
});
