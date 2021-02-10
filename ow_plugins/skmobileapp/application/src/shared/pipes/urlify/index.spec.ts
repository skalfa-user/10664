import { UrlifyPipe } from './';

describe('Urlify pipe', () => {
    let pipe: UrlifyPipe; // testable pipe

    beforeEach(() => {
        pipe = new UrlifyPipe();
    });

    it('transform should not be case sensitive', () => {
        expect(pipe.transform('FTP://www.test.com')).toEqual('<a href="ftp://www.test.com">ftp://www.test.com</a>');
        expect(pipe.transform('wWw.teSt.com')).toEqual('<a href="http://www.test.com">www.test.com</a>');
    });

    it('transform should add the http protocol for links without http protocols', () => {
        expect(pipe.transform('www.test.com')).toEqual('<a href="http://www.test.com">www.test.com</a>');
        expect(pipe.transform('www.com')).toEqual('<a href="http://www.com">www.com</a>');
    });

    it('transform should affect strings without (http | ftp | www ) prefixes', () => {
        expect(pipe.transform('test.com')).toEqual('test.com');
        expect(pipe.transform('just a string')).toEqual('just a string');
        expect(pipe.transform('www')).toEqual('www');
        expect(pipe.transform('www.')).toEqual('www.');
    });

    it('transform should correctly find and replace strings that contain www, http, https or ftp prefixes', () => {
        expect(pipe.transform('https://www.test.com')).toEqual('<a href="https://www.test.com">https://www.test.com</a>');
        expect(pipe.transform('http://www.test.com')).toEqual('<a href="http://www.test.com">http://www.test.com</a>');
        expect(pipe.transform('ftp://www.test.com')).toEqual('<a href="ftp://www.test.com">ftp://www.test.com</a>');
    });
});
