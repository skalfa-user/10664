import { DomSanitizer, ɵe as DomSanitizerImpl, SafeHtml } from '@angular/platform-browser';
import { SecurityContext } from '@angular/core';

import { TrustHtmlPipe } from './';

describe('Trust html pipe', () => {
    let pipe: TrustHtmlPipe; // testable pipe
    let sanitizer: DomSanitizer = new DomSanitizerImpl(null);

    beforeEach(() => {
        pipe = new TrustHtmlPipe(sanitizer);
    });

    it('transform should mark any content as safe', () => {
        const htmlString: string = '<script>test</script>';
        const safeHtml: SafeHtml = pipe.transform(htmlString);
        const sanitizedValue = sanitizer.sanitize(SecurityContext.HTML, safeHtml);

        expect(sanitizedValue).toBe(htmlString);
    });
});
