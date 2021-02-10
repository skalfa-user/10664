import { JwtService } from 'services/jwt';

export class JwtFake extends JwtService {
    /**
     * Decode token
     */
    decodeToken(token: string): any {
        return {};
    }

    /**
     * Is token expired
     */
    isTokenExpired(token: string): boolean {
        return false;
    }
}
