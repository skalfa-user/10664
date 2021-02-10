import { Injectable } from '@angular/core';
import cuid from 'cuid';

@Injectable()
export class StringUtilsService {
    /**
     * Get random string
     */
    getRandomString(): string {
        return cuid();
    }
}
