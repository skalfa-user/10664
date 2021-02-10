// services
import { AuthService, IAuthUser } from 'services/auth';

export class AuthServiceFake extends AuthService {
    /**
     * Get token name
     */
    public getTokenName(): string {
        return '';
    }

    /**
     * Get user
     */
    public getUser(): IAuthUser {
        return null;
    }

    /**
     * Get user id
     */
    public getUserId(): number {
        return null;
    }

    /**
     * Get token
     */
    public getToken(): string {
        return '';
    }

    /**
     * Set authenticated
     */
    public setAuthenticated(token: string): void {}

    /**
     * Logout
     */
    public logout(): void {}

    /**
     * Is authenticated
     */
    public isAuthenticated(): boolean {
        return false
    }
}

