import { NlbrPipe } from './';

describe('Nlbr pipe', () => {
    let pipe: NlbrPipe; // testable pipe

    beforeEach(() => {
        pipe = new NlbrPipe();
    });

    it('transform should correctly replace line endings in unix style', () => {
        expect(pipe.transform("test\ntest2")).toEqual('test <br /> test2');
    });

    it('transform should correctly replace line endings in windows style', () => {
        expect(pipe.transform("test\rtest2")).toEqual('test <br /> test2');
    });

    it('transform should correctly replace line endings for both unix and windows style', () => {
        expect(pipe.transform("test\r\ntest2")).toEqual('test <br /> test2');
    });
});
