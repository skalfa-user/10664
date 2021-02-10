import { Injectable } from '@angular/core';
import { JwtHelper, tokenNotExpired } from 'angular2-jwt';

@Injectable()
export class JwtService {
    protected jwtHelper: JwtHelper = new JwtHelper();

    /**
     * Decode token
     */
    decodeToken(token: string): any {
        return this.jwtHelper.decodeToken(token);
    }

    /**
     * Is token expired
     */
    isTokenExpired(token: string): boolean {
        return !tokenNotExpired(token);
    }
}

