import { Injectable } from '@angular/core';

@Injectable()
export class DateUtilsService {
    /**
     * Get unix time
     */
    getUnixTime(): number {
        return Math.floor(Date.now() / 1000);
    }
}
