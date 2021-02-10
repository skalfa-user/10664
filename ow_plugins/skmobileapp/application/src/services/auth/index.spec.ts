// services
import { PersistentStorageService } from 'services/persistent-storage';
import { AuthService, IAuthUser } from './';

// fakes
import { JwtFake, PersistentStorageMemoryAdapterFake } from 'test/fake';
 
describe('Auth service', () => {

    // register service's fakes
    let fakePersistentStorage: PersistentStorageService;
    let fakeJwt: JwtFake;
    let user: IAuthUser;

    // testable service
    let auth: AuthService;

    beforeEach(() => {
        // init service's fakes
        fakePersistentStorage = new PersistentStorageService( new PersistentStorageMemoryAdapterFake);
        fakeJwt = new JwtFake();

        user = {
            id: 1,
            name: 'test',
            email: 'test@gmail.com'
        };

        // init auth service
        auth = new AuthService(fakePersistentStorage, fakeJwt);
    });

    it('watchSetAuthenticated$ should return a positive boolean value', () => {
        spyOn(auth, 'setAuthenticated').and.callFake(() => {
            auth.watchSetAuthenticated$.next(true);
        });

        auth.watchSetAuthenticated$.subscribe(isAuthenticated => {
            expect(isAuthenticated).toBeTruthy(); 
        });

        auth.setAuthenticated('test_token');
    });

    it('watchLogout$ should return a positive boolean value', () => {
        spyOn(auth, 'logout').and.callFake(() => {
            auth.watchLogout$.next(true);
        });

        auth.watchLogout$.subscribe(isLoggedOut => {
            expect(isLoggedOut).toBeTruthy(); 
        });

        auth.logout();
    });
 
    it('setAuthenticated should authenticate users by a token', () => {
        const token: string = 'some_token';

        spyOn(fakePersistentStorage, 'setValue');
        spyOn(fakeJwt, 'decodeToken').and.returnValue(user);

        auth.setAuthenticated(token);

        expect(fakePersistentStorage.setValue).toHaveBeenCalled();
        expect(fakePersistentStorage.setValue).toHaveBeenCalledWith(auth.getTokenName(), token);

        expect(fakeJwt.decodeToken).toHaveBeenCalled();
        expect(fakeJwt.decodeToken).toHaveBeenCalledWith(token);

        expect(auth.getToken()).toEqual(token);
        expect(auth.getUser()).toEqual(user);
    });

    it('logout should clear logged data', () => {
        const token: string = 'some_token';

        spyOn(fakePersistentStorage, 'removeValue');

        auth.setAuthenticated(token);
        auth.logout();

        expect(fakePersistentStorage.removeValue).toHaveBeenCalled();
        expect(fakePersistentStorage.removeValue).toHaveBeenCalledWith(auth.getTokenName());
        expect(auth.getToken()).toBeNull();
    });

    it('isAuthenticated should return false if a token is empty', () => {
        expect(auth.isAuthenticated()).toBeFalsy();
        expect(auth.getUser()).toBeNull();
    });

    it('isAuthenticated should return true if a token is active and not empty', () => {
        const token: string = 'some_token';

        spyOn(fakeJwt, 'isTokenExpired').and.returnValue(false);
        spyOn(fakeJwt, 'decodeToken').and.returnValue(user);

        auth.setAuthenticated(token);

        expect(auth.getToken()).toEqual(token);
        expect(auth.isAuthenticated()).toBeTruthy();
        expect(auth.getUser()).toEqual(user);
    });

    it('isAuthenticated should return false if a token is expired or empty', () => {
        const token: string = 'some_token';

        spyOn(fakeJwt, 'decodeToken').and.returnValue(null);
        spyOn(fakeJwt, 'isTokenExpired').and.returnValue(true);

        auth.setAuthenticated(token);

        expect(auth.getToken()).toEqual(token);
        expect(auth.isAuthenticated()).toBeFalsy();
        expect(auth.getUser()).toBeNull();
    });

});
