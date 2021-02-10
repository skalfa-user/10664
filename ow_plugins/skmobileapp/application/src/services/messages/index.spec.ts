import { TestBed } from '@angular/core/testing';
import { normalize } from 'normalizr';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions, Response, ResponseOptions } from '@angular/http';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { MessagesService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { MatchActionsService } from 'services/match-actions';
import { SiteConfigsService } from 'services/site-configs';
import { UserService } from 'services/user';
import { StringUtilsService } from 'services/string-utils';
import { DateUtilsService } from 'services/date-utils';
import { FileUploaderService, IFileUploadResult } from 'services/file-uploader';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload,
    IMessageDataPayload,
    IMessagesAfterAddPayload
} from 'store/payloads';

// store
import {
    MESSAGES_BEFORE_MARK_READ,
    MESSAGES_AFTER_MARK_READ,
    MESSAGES_ERROR_MARK_READ,
    MESSAGES_DELETE_MESSAGE,
    MESSAGES_RESEND_MESSAGE,
    MESSAGES_ERROR_ADD,
    MESSAGES_AFTER_ADD,
    MESSAGES_BEFORE_ADD,
    MESSAGES_LOAD_HISTORY,
    MESSAGES_LOAD_MESSAGE,
    MESSAGES_UPDATE,
    MESSAGES_LOAD,
    CONVERSATIONS_BEFORE_MARK_READ,
    CONVERSATIONS_AFTER_MARK_READ,
    CONVERSATIONS_ERROR_MARK_READ,
    CONVERSATIONS_BEFORE_MARK_UNREAD,
    CONVERSATIONS_AFTER_MARK_UNREAD,
    CONVERSATIONS_ERROR_MARK_UNREAD,
    CONVERSATIONS_SET,
    CONVERSATIONS_BEFORE_DELETE,
    CONVERSATIONS_AFTER_DELETE,
    CONVERSATIONS_ERROR_DELETE
} from 'store/actions'; 

import { 
    IUser, 
    IAvatarData,
    IMatchAction,
    IConversationData,
    IMessage
} from 'store/states';

import { IConversationListItem } from 'store/reducers';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    createFakeFile,
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake,
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// responses
import { IConversationResponse, IMessageResponse } from './responses';  

// schemas
import { conversationListSchema, messageListSchema } from './schemas';

describe('Messages service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeSiteConfigs: SiteConfigsService;
    let fakeMatchActions: MatchActionsService;
    let fakeUser: UserService;
    let fakeHttp: SecureHttpService;
    let fakeStringUtils: StringUtilsService;
    let fakeDateUtils: DateUtilsService;
    let fakeUploader: FileUploaderService;

    let messages: MessagesService; // testable service

    beforeEach(() => { 
        TestBed.configureTestingModule({
            providers: [{
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new DeviceFake, new StringUtilsFake, fakePlatform),
                    deps: [PersistentStorageService, Platform]
                }, {
                    provide: PersistentStorageService,
                    useFactory: () => new PersistentStorageService(new PersistentStorageMemoryAdapterFake),
                    deps: []
                }, {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
                }, {
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                }, {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                }, {
                    provide: NgRedux, 
                    useFactory: () => new ReduxFake(), 
                    deps: [] 
                },
                MatchActionsService,
                MessagesService,
                SiteConfigsService,
                UserService,
                StringUtilsService,
                DateUtilsService,
                FileUploaderService
            ]}
        );

        // init service's fakes
        fakeRedux = TestBed.get(NgRedux);
        fakeSiteConfigs = TestBed.get(SiteConfigsService);
        fakeMatchActions = TestBed.get(MatchActionsService);
        fakeUser = TestBed.get(UserService);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeStringUtils = TestBed.get(StringUtilsService);
        fakeDateUtils = TestBed.get(DateUtilsService);
        fakeUploader = TestBed.get(FileUploaderService);

        // init messages service
        messages = TestBed.get(MessagesService);
    });

    it('sendImageMessage should call the processSuccessfulMessageResponse for all successful requests', () => {
        const messageId: number = 1;
        const opponentId: number = 1;
        const file: Blob = createFakeFile('test.jpg', 100);
        const downloadUrl: string = 'test';

        const message: IMessage = {
            id: messageId,
            file: file,
            opponentId: opponentId,
            attachments: [{
                downloadUrl: downloadUrl
            }]
        };

        const response: IMessageResponse = {
            id: messageId
        };

        const fileUploaderResponse: IFileUploadResult = {
            type: FileUploaderService.SUCCESS_RESULT,
            data: response
        };
 
        spyOn(fakeUploader, 'upload').and.returnValue(
            Observable.of(fileUploaderResponse)
        );

        spyOn(messages, 'processSuccessfulMessageResponse');
        spyOn(window.URL, 'revokeObjectURL');

        messages.sendImageMessage(message).subscribe((response: IFileUploadResult) => {
            expect(messages.processSuccessfulMessageResponse).toHaveBeenCalledWith(message, response.data);
            expect(window.URL.revokeObjectURL).toHaveBeenCalledWith(downloadUrl);
        });
    });
 
    it('sendImageMessage should call the processFailMessageResponse for all failed requests', () => {
        const messageId: number = 1;
        const opponentId: number = 1;
        const file: Blob = createFakeFile('test.jpg', 100);

        const message: IMessage = {
            id: messageId,
            opponentId: opponentId,
            file: file
        };

        const fileUploaderResponse: IFileUploadResult = {
            type: FileUploaderService.UPLOAD_ERROR_RESULT,
            data: new Response(new ResponseOptions({}))
        };

        spyOn(fakeUploader, 'upload').and.returnValue(
            Observable.of(fileUploaderResponse)
        );

        spyOn(messages, 'processFailMessageResponse');

        messages.sendImageMessage(message).subscribe((response: IFileUploadResult) => {
            expect(messages.processFailMessageResponse).toHaveBeenCalledWith(message, response.data);
        });
    });

    it('sendTextMessage should call the processSuccessfulMessageResponse for all successful requests', () => {
        const messageId: number = 1;
        const message: IMessage = {
            id: messageId,
            text: 'test'
        };

        const response: IMessageResponse = {
            id: messageId
        };

        spyOn(messages, 'processSuccessfulMessageResponse');

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        messages.sendTextMessage(message).subscribe((response: IMessageResponse) => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/mailbox/messages', message, {}, false);
            expect(messages.processSuccessfulMessageResponse).toHaveBeenCalledWith(message, response);
        });
    });

    it('sendTextMessage should call the processFailMessageResponse for all failed requests', () => {
        const messageId: number = 1;
        const message: IMessage = {
            id: messageId,
            text: 'test'
        };

        const errorResponse: string  = 'Some error';

        spyOn(messages, 'processFailMessageResponse');

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(Observable.throw(errorResponse));

        messages.sendTextMessage(message).subscribe(() => {}, (error) => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/mailbox/messages', message, {}, false);
            expect(messages.processFailMessageResponse).toHaveBeenCalledWith(message, error);
        });
    });

    it('processSuccessfulMessageResponse dispatch MESSAGES_AFTER_ADD action', () => {
        const messageId: number = 1;
        const message: IMessage = {
            id: messageId,
            text: 'test'
        };

        const response: IMessageResponse = {
            id: messageId
        };

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        messages.processSuccessfulMessageResponse(message, response);

        // normalize the response
        const result = normalize([response], messageListSchema);

        const payload: IMessagesAfterAddPayload = {
            id: message.id,
            message: result.entities.messages[message.id]
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MESSAGES_AFTER_ADD,
            payload: payload
        });
    });

    it('processFailMessageResponse dispatch MESSAGES_ERROR_ADD action and broadcast error', () => {
        const messageId: number = 1;
        const message: IMessage = {
            id: messageId,
            text: 'test'
        };

        const error = new Response(new ResponseOptions({}));

        spyOn(fakeHttp, 'broadcastError');
 
        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        messages.processFailMessageResponse(message, error);

        const payload: IMessagesAfterAddPayload = {
            id: messageId,
            message: ''
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MESSAGES_ERROR_ADD,
            payload: payload
        });

        // the error should be broadcasted
        expect(fakeHttp.broadcastError).toHaveBeenCalledWith(error);
    });

    it('processFailMessageResponse dispatch MESSAGES_ERROR_ADD action and and correctly parse the error description', () => {
        const messageId: number = 1;
        const message: IMessage = {
            id: messageId,
            text: 'test'
        };

        const errorDescription: string = 'Error';
        const errorResponse = {
            messagesError: errorDescription
        };

        const error = new Response(new ResponseOptions({ 
            body: JSON.stringify(errorResponse)
        }));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        messages.processFailMessageResponse(message, error);

        const payload: IMessagesAfterAddPayload = {
            id: messageId,
            message: errorDescription
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MESSAGES_ERROR_ADD,
            payload: payload
        });
    });

    it('deleteMessage should dispatch MESSAGES_DELETE_MESSAGE action and revoke file urls', () => {
        const messageId: number = 1;
        const downloadUrl: string = 'test';
        const file: Blob = createFakeFile('test.jpg', 100);
        const message: IMessage = {
            id: messageId,
            file: file,
            attachments: [{
                downloadUrl: downloadUrl
            }]
        };

        const payload: IMessageDataPayload = {
            ...message
        };

        const expectedArgs = {
            type: MESSAGES_DELETE_MESSAGE,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        spyOn(window.URL, 'revokeObjectURL');

        messages.deleteMessage(message);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
        expect(window.URL.revokeObjectURL).toHaveBeenCalledWith(downloadUrl);
    });

    it('resendMessage should dispatch MESSAGES_RESEND_MESSAGE action', () => {
        const messageId: number = 1;
        const message: IMessage = {
            id: messageId,
            text: 'test'
        };

        const payload: IMessageDataPayload = {
            ...message
        };

        const expectedArgs = {
            type: MESSAGES_RESEND_MESSAGE,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');

        messages.resendMessage(message);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('addMessage should dispatch MESSAGES_BEFORE_ADD action', () => {
        const messageId: number = 1;
        const message: IMessage = {
            text: 'test'
        };

        const timeStamp: number = 1;

        const payload: IMessageDataPayload = {
            ...message,
            id: messageId,
            isAuthorized: true,
            isAuthor: true,
            timeStamp: timeStamp
        };

        const expectedArgs = {
            type: MESSAGES_BEFORE_ADD,
            payload: payload
        };

        spyOn(fakeDateUtils, 'getUnixTime').and.returnValue(timeStamp);
        spyOn(fakeRedux, 'dispatch');
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(messageId);

        messages.addMessage(message);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeStringUtils.getRandomString).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('updateMessages should dispatch MESSAGES_UPDATE action', () => { 
        const messageId: number = 1;
        const response: Array<IMessageResponse> = [{
            id: messageId
        }];

        const payload: IEntitiesPayload = normalize(response, messageListSchema);

        const expectedArgs = {
            type: MESSAGES_UPDATE,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        messages.updateMessages(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('loadHistoryMessages should dispatch MESSAGES_LOAD_HISTORY action', () => {
        const limit:  number = 1;
        const userId: number = 1;
        const messageId: number = 1;

        const response: Array<IMessageResponse> = [{
            id: messageId
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        spyOn(fakeRedux, 'dispatch');

        const payload: IEntitiesPayload = normalize(response, messageListSchema);

        const expectedArgs = {
            type: MESSAGES_LOAD_HISTORY,
            payload: payload
        };

        messages.loadHistoryMessages(userId, messageId, limit).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);

            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/mailbox/messages/history/user/' + userId, {
                beforeMessageId: messageId,
                limit: limit
            });
        });
    });
 
    it('loadMessage should dispatch MESSAGES_LOAD_MESSAGE action', () => {
        const messageId: number = 1;

        const response: IMessageResponse = {
            id: messageId
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        spyOn(fakeRedux, 'dispatch');

        const expectedArgs = {
            type: MESSAGES_LOAD_MESSAGE,
            payload: normalize([response], messageListSchema)
        };

        messages.loadMessage(messageId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);

            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/mailbox/messages/' + messageId);
        });
    });

    it('loadMessages should dispatch MESSAGES_LOAD action', () => {
        const userId: number = 1;
        const limit: number = 1;
        const messageId: number = 1;

        const response: Array<IMessageResponse> = [{
            id: messageId
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        spyOn(fakeRedux, 'dispatch');

        const payload: IEntitiesPayload = normalize(response, messageListSchema);

        const expectedArgs = {
            type: MESSAGES_LOAD,
            payload: payload
        };

        messages.loadMessages(userId, limit).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);

            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/mailbox/messages/user/' + userId, {
                limit: limit
            });
        });
    });

    it('markMessagesAsRead should return correct result and dispatch both MESSAGES_BEFORE_MARK_READ and MESSAGES_AFTER_MARK_READ actions', () => {
        const messagesIds: Array<number> = [1, 2, 3];
        const response: string = 'ok';

        const payload: IByIdPayload = {
            id: messagesIds
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        // fake messages
        spyOn(messages, 'getRealMessagesIds').and.returnValue(messagesIds);

        messages.markMessagesAsRead(messagesIds).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MESSAGES_AFTER_MARK_READ,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/mailbox/messages', {
                ids: messagesIds
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MESSAGES_BEFORE_MARK_READ,
            payload: payload
        });
    });

    it('markMessagesAsRead should dispatch MESSAGES_ERROR_MARK_READ action if an error occurred', () => {
        const messagesIds: Array<number> = [1, 2, 3];
        const errorResponse: string  = 'Some error';
 
        const payload: IByIdPayload = {
            id: messagesIds
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        // fake messages
        spyOn(messages, 'getRealMessagesIds').and.returnValue(messagesIds);

        messages.markMessagesAsRead(messagesIds).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MESSAGES_ERROR_MARK_READ,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/mailbox/messages', {
                ids: messagesIds
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MESSAGES_BEFORE_MARK_READ,
            payload: payload
        });
    });

    it('markConversationAsRead should return correct result and dispatch both CONVERSATIONS_BEFORE_MARK_READ and CONVERSATIONS_AFTER_MARK_READ actions', () => {
        const conversationId: string = '1-1';
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: conversationId
        };

        messages.markConversationAsRead(conversationId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: CONVERSATIONS_AFTER_MARK_READ,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/mailbox/conversations/' + conversationId, {
                isRead: true
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: CONVERSATIONS_BEFORE_MARK_READ,
            payload: payload
        });
    });

    it('markConversationAsRead should dispatch CONVERSATIONS_ERROR_MARK_READ action if an error occurred', () => {
        const conversationId: string = '1-1';
        const errorResponse: string  = 'Some error';
 
        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: conversationId
        };
        
        messages.markConversationAsRead(conversationId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: CONVERSATIONS_ERROR_MARK_READ,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/mailbox/conversations/' + conversationId, {
                isRead: true
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: CONVERSATIONS_BEFORE_MARK_READ,
            payload: payload
        });
    });

    it('markConversationAsUnRead should return correct result and dispatch both CONVERSATIONS_BEFORE_MARK_UNREAD and CONVERSATIONS_AFTER_MARK_UNREAD actions', () => {
        const conversationId: string = '1-1';
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: conversationId
        };

        messages.markConversationAsUnRead(conversationId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: CONVERSATIONS_AFTER_MARK_UNREAD,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/mailbox/conversations/' + conversationId, {
                isRead: false
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: CONVERSATIONS_BEFORE_MARK_UNREAD,
            payload: payload
        });
    });

    it('markConversationAsUnRead should dispatch CONVERSATIONS_ERROR_MARK_UNREAD action if an error occurred', () => {
        const conversationId: string = '1-1';
        const errorResponse: string  = 'Some error';
 
        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: conversationId
        };

        messages.markConversationAsUnRead(conversationId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: CONVERSATIONS_ERROR_MARK_UNREAD,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/mailbox/conversations/' + conversationId, {
                isRead: false
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: CONVERSATIONS_BEFORE_MARK_UNREAD,
            payload: payload
        });
    });

    it('deleteConversation should return correct result and dispatch both CONVERSATIONS_BEFORE_DELETE and CONVERSATIONS_AFTER_DELETE actions', () => {
        const conversationId: string = '1-1';
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: conversationId
        };

        messages.deleteConversation(conversationId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: CONVERSATIONS_AFTER_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/mailbox/conversations/' + conversationId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: CONVERSATIONS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('deleteConversation should dispatch CONVERSATIONS_ERROR_DELETE action if an error occurred', () => {
        const conversationId: string = '1-1';
        const errorResponse: string  = 'Some error';
 
        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: conversationId
        };

        messages.deleteConversation(conversationId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: CONVERSATIONS_ERROR_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/mailbox/conversations/' + conversationId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: CONVERSATIONS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('watchNewConversationsCount should return a correct result of not read conversations count', () => {
        const notReadConversationsCount: number = 10;

        // fake the method
        spyOn(messages, 'watchNewConversationsCount').and.returnValue(
            Observable.of(notReadConversationsCount)
        );

        messages.watchNewConversationsCount().subscribe(count => {
            expect(count).toEqual(notReadConversationsCount); 
        });
    });

    it('setConversations should dispatch CONVERSATIONS_SET action', () => {
        const conversationId: string = '1-1';
        const response: Array<IConversationResponse> = [{
            id: conversationId
        }];

        const payload: IEntitiesPayload = normalize(response, conversationListSchema);

        const expectedArgs = {
            type: CONVERSATIONS_SET,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        messages.setConversations(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('isChatAllowed should return a positive boolean value if the tinder search mode is deactivated', () => {
        const recipientId: number = 1;

        // fake site configs
        spyOn(fakeSiteConfigs, 'isTinderSearchMode').and.returnValue(false);

        expect(messages.isChatAllowed(recipientId)).toBeTruthy();
    });

    it('isChatAllowed should return a negative boolean value if the tinder search mode is activated and there are no any user and match actions data', () => {
        const recipientId: number = 1;

        // fake site configs
        spyOn(fakeSiteConfigs, 'isTinderSearchMode').and.returnValue(true);

        // fake match actions
        spyOn(fakeMatchActions, 'getMatch').and.returnValue(undefined);

        // fake user
        spyOn(fakeUser, 'getUser').and.returnValue(undefined);

        expect(messages.isChatAllowed(recipientId)).toBeFalsy();
    });

    it('isChatAllowed should return a positive boolean value if the tinder search mode is activated and there are mutual match data', () => {
        const recipientId: number = 1;
        const matchId: number = 1;

        const matchData: IMatchAction = {
            id: matchId,
            userId: recipientId,
            isMutual: true
        };

        // fake site configs
        spyOn(fakeSiteConfigs, 'isTinderSearchMode').and.returnValue(true);

        // fake match actions
        spyOn(fakeMatchActions, 'getMatch').and.returnValue(matchData);

        expect(messages.isChatAllowed(recipientId)).toBeTruthy();
    });

    it('isChatAllowed should return a positive boolean value if the tinder search mode is activated and there is a conversation', () => {
        const recipientId: number = 1;
        const conversationId: string = '1-1';

        const userData: IUser = {
            id: recipientId,
            conversation: conversationId
        };
 
        const conversationData: IConversationData = {
            id: conversationId
        };

        // fake site configs
        spyOn(fakeSiteConfigs, 'isTinderSearchMode').and.returnValue(true);

        // fake match actions
        spyOn(fakeMatchActions, 'getMatch').and.returnValue(undefined);

        // fake user
        spyOn(fakeUser, 'getUser').and.returnValue(userData);

        // fake messages
        spyOn(messages, 'getConversation').and.returnValue(conversationData);

        expect(messages.isChatAllowed(recipientId)).toBeTruthy();
    });

    it('isChatAllowed should return a negative boolean value if the tinder search mode is activated and there is a lost conversation', () => {
        const recipientId: number = 1;
        const conversationId: string = '1-1';

        const userData: IUser = {
            id: recipientId,
            conversation: conversationId
        };

        // fake site configs
        spyOn(fakeSiteConfigs, 'isTinderSearchMode').and.returnValue(true);

        // fake match actions
        spyOn(fakeMatchActions, 'getMatch').and.returnValue(undefined);

        // fake user
        spyOn(fakeUser, 'getUser').and.returnValue(userData);

        // fake messages
        spyOn(messages, 'getConversation').and.returnValue(undefined);

        expect(messages.isChatAllowed(recipientId)).toBeFalsy();
    });

    it('watchIsConversationsFetched should return a correct boolean value', () => {
        const isFetched: boolean = true;

        // fake the method
        spyOn(messages, 'watchIsConversationsFetched').and.returnValue(
            Observable.of(isFetched)
        );

        messages.watchIsConversationsFetched().subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchIsMessageListFetched should return a correct boolean value', () => {
        const userId: number = 1;
        const isFetched: boolean = true;

        // fake the method
        spyOn(messages, 'watchIsMessageListFetched').and.returnValue(
            Observable.of(isFetched)
        );

        messages.watchIsMessageListFetched(userId).subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchMessagesQueue should return a correct result', () => {
        const messageId: number = 1;

        const message: IMessage = {
            id: messageId
        };

        // fake the method
        spyOn(messages, 'watchMessagesQueue').and.returnValue(
            Observable.of(message)
        );

        messages.watchMessagesQueue().subscribe(response => {
            expect(response).toEqual(message);
        });
    });

    it('watchUnreadMessagesIdList should return a correct result', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;

        // fake the method
        spyOn(messages, 'watchUnreadMessagesIdList').and.returnValue(
            Observable.of([messageId])
        );

        messages.watchUnreadMessagesIdList(conversationId).subscribe(response => {
            expect(response).toEqual([messageId]);
        });
    });

    it('watchUnreadMessagesIdList should return an empty list if message list empty', () => {
        const conversationId: string = '1-1';

        // fake the method
        spyOn(messages, 'watchUnreadMessagesIdList').and.returnValue(
            Observable.of([])
        );

        messages.watchUnreadMessagesIdList(conversationId).subscribe(response => {
            expect(response).toEqual([]); 
        });
    });

    it('watchMessageList should return a correct result', () => {
        const conversationId: string = '1-1';
        const messageId: number = 1;

        const message: IMessage = {
            id: messageId
        };

        // fake the method
        spyOn(messages, 'watchMessageList').and.returnValue(
            Observable.of([message])
        );

        messages.watchMessageList(conversationId).subscribe(response => {
            expect(response).toEqual([message]);
        });
    });

    it('watchMessageList should return an undefined value if message list empty', () => {
        const conversationId: string = '1-1';

        // fake the method
        spyOn(messages, 'watchMessageList').and.returnValue(
            Observable.of(undefined)
        );

        messages.watchMessageList(conversationId).subscribe(response => {
            expect(response).toBeUndefined(); 
        });
    });

    it('watchConversationList should return a correct result', () => {
        const conversationId: string = '1-1';
        const userId: number = 1;
        const avatarId: number = 1;
 
        const conversationData: IConversationData = {
            id: conversationId,
            user: userId
        };

        const userData: IUser = {
            id: userId,
            avatar: avatarId
        };

        const avatarData: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const result: IConversationListItem = {
            conversation: conversationData,
            user: userData, 
            avatar: avatarData
        };

        // fake the method
        spyOn(messages, 'watchConversationList').and.returnValue(
            Observable.of(result)
        );

        messages.watchConversationList().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchConversationList should return an undefined value if conversation list empty or there are no relevant users', () => {
        // fake the method
        spyOn(messages, 'watchConversationList').and.returnValue(
            Observable.of(undefined)
        );

        messages.watchConversationList().subscribe(response => {
            expect(response).toBeUndefined(); 
        });
    });
});
