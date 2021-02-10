import { StringUtilsService } from 'services/string-utils';

export class StringUtilsFake extends StringUtilsService {
    /**
     * Get random string
     */
    getRandomString(): string {
        return '';
    }
}
