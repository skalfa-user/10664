import { DomSanitizer, ɵe as DomSanitizerImpl, SafeUrl } from '@angular/platform-browser';
import { SecurityContext } from '@angular/core';

import { TrustUrlPipe } from './';

describe('Trust url pipe', () => {
    let pipe: TrustUrlPipe; // testable pipe
    let sanitizer: DomSanitizer = new DomSanitizerImpl(null);

    beforeEach(() => {
        pipe = new TrustUrlPipe(sanitizer);
    });

    it('transform should mark any url as a safe', () => {
        const url: string = 'test1';
        const safeUrl: SafeUrl = pipe.transform(url);
        const sanitizedValue = sanitizer.sanitize(SecurityContext.URL, safeUrl);

        expect(sanitizedValue).toBe(url);
    });

    it('transform should mark url with blobs as a safe', () => {
        const url: string = 'blob:http://localhost:8100/f3802780-297f-428a-8ad3-162b07ba4bac';
        const safeUrl: SafeUrl = pipe.transform(url);
        const sanitizedValue = sanitizer.sanitize(SecurityContext.URL, safeUrl);

        expect(sanitizedValue).toBe(url); 
    });
});
