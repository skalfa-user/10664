import { DomSanitizer, ɵe as DomSanitizerImpl, SafeStyle } from '@angular/platform-browser';
import { SecurityContext } from '@angular/core';

import { TrustStylePipe } from './';

describe('Trust style pipe', () => {
    let pipe: TrustStylePipe; // testable pipe
    let sanitizer: DomSanitizer = new DomSanitizerImpl(null);

    beforeEach(() => {
        pipe = new TrustStylePipe(sanitizer);
    });

    it('transform should mark any style as a safe', () => {
        const style: string = 'width:100%';
        const safeStyle: SafeStyle = pipe.transform(style);
        const sanitizedValue = sanitizer.sanitize(SecurityContext.STYLE, safeStyle);

        expect(sanitizedValue).toBe(style);
    });
});
