import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';

@Pipe({
    name: 'trustUrl'
})

export class TrustUrlPipe implements PipeTransform {
    /**
     * Constructor
     */
    constructor(private sanitizer: DomSanitizer) {}

    /**
     * Transform
     */
    transform(value: string): SafeUrl {
        return this.sanitizer.bypassSecurityTrustUrl(value);
    }
}
