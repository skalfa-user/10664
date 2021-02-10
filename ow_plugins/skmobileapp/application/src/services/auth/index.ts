import { Injectable } from '@angular/core';
import { Subject } from 'rxjs/Subject';

// services
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';

export interface IAuthUser {
    id: number;
    name: string;
    email: string;
}

@Injectable()
export class AuthService {
    public watchSetAuthenticated$: Subject<boolean> = new Subject();
    public watchLogout$: Subject<boolean> = new Subject();

    protected token: any = null;
    protected tokenName: string = 'token';
    protected user: IAuthUser = null;

    /**
     * Constructor
     */
    constructor(private storage: PersistentStorageService, private jwt: JwtService)
    {
        this.token = this.storage.getValue(this.tokenName);

        if (this.token) {
            this.user = this.jwt.decodeToken(this.token);
        }
    }

    /**
     * Get token name
     */
    public getTokenName(): string {
        return this.tokenName;
    }

    /**
     * Get user
     */
    public getUser(): IAuthUser {
        return this.user;
    }

    /**
     * Get user id
     */
    public getUserId(): number {
        return this.user ? this.user.id : null;
    }

    /**
     * Get token
     */
    public getToken(): string {
        return this.token;
    }

    /**
     * Set authenticated
     */
    public setAuthenticated(token: string): void {
        this.storage.setValue(this.tokenName, token);

        this.user = this.jwt.decodeToken(token);
        this.token = token;

        this.watchSetAuthenticated$.next(true);
    }

    /**
     * Logout
     */
    public logout(): void {
        this.storage.removeValue(this.tokenName);

        this.user = null;
        this.token = null;

        this.watchLogout$.next(true);
    }

    /**
     * Is authenticated
     */
    public isAuthenticated(): boolean {
        return this.token && !this.jwt.isTokenExpired(this.tokenName);
    }

    /**
     * Get auth header name
     */
    public getAuthHeaderName(): string {
        return 'jwt';
    }

    /**
     * Get auth header value
     */
    public getAuthHeaderValue(): string {
        return `Bearer ${this.getToken()}`;
    }
}
